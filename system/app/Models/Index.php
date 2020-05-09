<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Index extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'indexes';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'node_id',
        'node_field',
        'field_value',
        'langcode',
    ];

    protected $tokens = [];

    public static function rebuild()
    {
        $langcodes = array_keys(langcode('all'));
        $records = [];
        foreach (Node::fetchAll() as $node) {
            $fields = $node->searchableFields();
            $record = [
                'node_id' => $node->id,
            ];
            foreach ($langcodes as $langcode) {
                $values = $node->retrieveValues($langcode);
                foreach ($values as $key => $value) {
                    if (($meta = $fields[$key] ?? null) && !empty($value)) {
                        $records[] = array_merge($record, [
                            'node_field' => $key,
                            'field_value' => static::prepareValue($value, $meta['field_type']),
                            'langcode' => $langcode,
                            'weight' => $meta['weight'],
                        ]);
                    }
                }
            }
        }

        DB::delete('DELETE FROM indexes;');
        DB::transaction(function() use ($records) {
            DB::table('indexes')->insert($records);
        });

        return true;
    }

    protected static function prepareValue($content, $type)
    {
        $content = preg_replace('/\s+/', ' ', $content);

        if ($type === 'html') {
            $blocks = [
                'div','p','h1','h2','h3','h4','h5','h6',
                'li','dt','dd','caption','th','td',
                'section','nav','header','article','aside','footer','menuitem','address',
                'br','hr',
            ];

            $content = preg_replace('/<('.implode('|', $blocks).')(?=\\s|>)/i', '; <$1', $content);
            $content = strip_tags($content);
            $content = preg_replace('/\s+/', ' ', $content);
            $content = preg_replace('/[\s;]+;/', ';', $content);
            $content = preg_replace('/([.,;?!]);\s/', '$1 ', $content);
        }

        return trim($content, ' ;');
    }

    public static function search($keywords)
    {
        if (empty($keywords)) {
            return [
                'keywords' => $keywords,
                'results' => [],
            ];
        }

        $keywords = static::getKeywords($keywords);

        $results = [];
        foreach (static::searchIndex($keywords) as $result) {
            $node_id = $result->node_id;
            $node_field = $result->node_field;

            $result = $result->getSearchResult($keywords);
            if (! isset($results[$node_id])) {
                $results[$node_id] = [
                    'node_id' => $node_id,
                    'weight' => 0,
                ];
            }
            $results[$node_id][$node_field] =  $result['content'];
            $results[$node_id]['weight'] +=  $result['weight'];
        }

        // 对结果排序
        array_multisort(
            array_column($results, 'weight'),
            SORT_DESC,
            array_column($results, 'node_id'),
            SORT_NUMERIC,
            $results
        );

        return [
            'keywords' => key($keywords),
            'results' => $results,
        ];
    }

    protected static function searchIndex(array $keywords, $langcode = null)
    {
        $langcode = $langcode ?: langcode('site_page');

        $likes = [];
        foreach ($keywords as $keyword => $weight) {
            $likes[] = ['field_value', 'like', '%'.$keyword.'%', 'or'];
        }

        return static::where('langcode', $langcode)->where($likes)->get();
    }

    /**
     * 提取有效的关键词
     *
     * @param string $input
     * @return array
     */
    protected static function getKeywords($input)
    {
        if (empty($input)) {
            return [];
        }

        if (strlen($input) > 100) {
            $input = substr($input, 0, 100);
        }

        $keywords = array_filter(preg_split('/\s+/', $input));
        $keywords = array_slice($keywords, 0, 5);
        $keywords = static::weightKeywords($keywords);
        arsort($keywords);

        return $keywords;
    }

    /**
     * 为关键词标记权重
     *
     * @param array $keywords
     * @param int $offset 偏移
     * @return array
     */
    protected static function weightKeywords(array $keywords, $offset = 0)
    {
        $weights = [];
        $words = [];
        foreach ($keywords as $index => $word) {
            $words[] = $word;
            $key = implode(' ', $words);
            $weights[$key] = pow(2, $index + 1) - $offset/10;
        }

        $keywords = array_slice($keywords, 1);
        if ($keywords) {
            $weights = array_merge($weights, static::weightKeywords($keywords, $offset + 1));
        }

        return $weights;
    }

    public function getSearchResult(array $keywords)
    {
        $this->tokenize($keywords);

        $similar = $this->similar($this->attributes['field_value'], key($keywords));

        $weight = 0;
        foreach ($this->tokens as $token) {
            $weight += $token['weight'];
        }
        $weight *= ($this->attributes['weight'] ?? 1) * pow(10, pow($similar, 3));

        return [
            'content' => $this->summary(),
            'weight' => $weight,
        ];
    }

    /**
     * 将内容切分为令牌，令牌共四种：
     *  1. 普通单词
     *  2. 关键字
     *  3. 空格
     *  4. 标点
     */
    protected function tokenize(array $keywords)
    {
        $this->tokens = [];
        $content = trim($this->attributes['field_value']);

        $order = 1;

        while(true) {
            // 按权重大小依次检查每个关键字
            foreach ($keywords as $keyword => $weight) {
                $pos = stripos($content, $keyword);
                if ($pos !== false) {
                    $order = $this->tokenizeContext(substr($content, 0, $pos), $order, $order > 1 ? 'middle' : 'lead');
                    $this->tokens['order_' . $order++] = [
                        'type' => 'KEYWORD',
                        'source' => substr($content, $pos, strlen($keyword)),
                        'weight' => $weight,
                        'order' => $order-1,
                    ];
                    $content = substr($content, $pos + strlen($keyword));
                    continue 2;
                }
            }
            $this->tokenizeContext($content, $order, 'tail');
            break;
        }
    }

    /**
     * 切分关键词上下文（不包含关键词），令牌共三种：
     *  1. 普通单词
     *  2. 空格
     *  3. 标点
     */
    protected function tokenizeContext($content, $order, $type)
    {
        if ($count = preg_match_all('/[.,;?!]\s/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            switch($type) {
                case 'lead':
                    $content = substr($content, $matches[0][$count-1][1] + 2);
                break;
                case 'tail':
                    $content = substr($content, 0, $matches[0][0][1] + 2);
                break;
                default:
                    $content = substr($content, 0, $matches[0][0][1] + 2) . substr($content, $matches[0][$count-1][1] + 2);
                break;
            }
        }

        foreach (explode(' ', $content) as $index => $word) {
            if ($index > 0) {
                $this->tokens['order_' . $order++] = [
                    'type' => 'SPACE',
                    'source' => ' ',
                    'weight' => 0,
                    'order' => $order-1,
                ];
            }
            $mark = false;
            if (preg_match('/[.,;?!:]$/', $word)) {
                $mark = substr($word, -1);
                $word = substr($word, 0, strlen($word)-1);
            }
            if (strlen($word)) {
                $this->tokens['order_' . $order++] = [
                    'type' => 'WORD',
                    'source' => $word,
                    'weight' => 0,
                    'order' => $order-1,
                ];
            }
            if ($mark) {
                $this->tokens['order_' . $order++] = [
                    'type' => 'MARK',
                    'source' => $mark,
                    'weight' => 0,
                    'order' => $order-1,
                ];
            }
        }

        return $order;
    }

    public function summary()
    {
        $limit = 200;

        //
        if (strlen($this->attributes['field_value']) <= $limit) {
            return $this->join($this->tokens);
        }

        $tokens = $this->tokens;
        array_multisort(
            array_column($tokens, 'weight'),
            SORT_DESC,
            array_column($tokens, 'order'),
            SORT_NUMERIC,
            $tokens
        );

        //
        $summary = [];
        $len = 0;
        foreach ($tokens as $token) {
            if ($token['weight'] <= 0) break;

            $siblings = $this->siblings($token);
            $moreLen = $this->length($siblings);
            if ($len + $moreLen >= $limit) continue;

            $summary = array_merge($summary, $siblings);
            $len += $moreLen;
        }
        if (empty($summary)) {
            return '';
        }

        array_multisort(
            array_column($summary, 'order'),
            SORT_NUMERIC,
            $summary
        );

        // 删除连续的标点以及标点后的空格，并将所有标点改为 『...』
        $prev = null;
        foreach ($summary as $key => $token) {
            switch($token['type']) {
                case 'SPACE':
                    if ($prev && $prev['type'] === 'MARK') {
                        $summary[$key] = null;
                    } else {
                        $prev = $token;
                    }
                break;

                case 'MARK':
                    if ($prev && $prev['type'] === 'MARK') {
                        $summary[$key] = null;
                    } else {
                        $summary[$key]['source'] = ' ... ';
                        $prev = $token;
                    }
                break;

                default:
                    $prev = $token;
                break;
            }
        }
        $summary = array_filter($summary);

        return $this->join($summary);
    }

    /**
     * 组合词组为字符串
     *
     * @param array $tokens 词组
     * @return string
     */
    protected function join(array $tokens)
    {
        $content = '';
        foreach ($tokens as $token) {
            if ($token['type'] === 'KEYWORD') {
                $content .= '<span class="keyword">' . $token['source'] . '</span>';
            } else {
                $content .= $token['source'];
            }
        }
        return trim($content);
    }

    /**
     * 返回词组组合成字符串后的长度
     *
     * @param array $tokens 词组
     * @return int
     */
    protected function length(array $tokens)
    {
        $len = 0;
        foreach ($tokens as $token) {
            $len += strlen($token['source']);
        }
        return $len;
    }

    /**
     * 获取一个令牌的相邻令牌，以标点为界
     *
     * @param array $token 令牌
     * @return array
     */
    protected function siblings(array $token)
    {
        $order = $token['order'];

        $siblings = [
            'order_' . $order => $token
        ];

        $offset = $order + 1;
        while(true) {
            $next = $this->tokens['order_' . $offset] ?? null;
            if (is_null($next)) break;
            $siblings['order_' . $offset] = $next;
            if ($next['type'] === 'MARK') break;
            $offset++;
        }

        $offset = $order - 1;
        while(true) {
            $prev = $this->tokens['order_' . $offset] ?? null;
            if (is_null($prev)) break;
            $siblings['order_' . $offset] = $prev;
            if ($prev['type'] === 'MARK') break;
            $offset--;
        }

        return $siblings;
    }

    /**
     * 计算两个字符串的相似度（百分比）
     *
     * @param string $str1
     * @param string $str2
     * @return float
     */
    protected function similar($str1, $str2)
    {
        if ($str1 === $str2) {
			return 1;
		}

		$len1 = strlen($str1);
		$len2 = strlen($str2);
		if ($len1 === 0 || $len2 === 0) {
			return 0;
		}

		$maxlen = max($len1, $len2);
		if (strpos($str1, $str2) !== false || strpos($str2, $str1) !== false) {
			return abs($len1 - $len2) / $maxlen;
        }

        // 长度相差 3 倍以上
        if ($len1/$len2 > 3 || $len1/$len2 < 1/3) {
            return 0;
        }

		$levenshtein = levenshtein($str1, $str2);
		$levenshtein -= ($levenshtein - levenshtein(strtolower($str1), strtolower($str2))) / 2;

		return ($maxlen - $levenshtein) / $maxlen;
    }
}
