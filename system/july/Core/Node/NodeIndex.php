<?php

namespace July\Core\Node;

use App\Model;
use Illuminate\Support\Facades\DB;

class NodeIndex extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_index';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'node_id',
        'node_field_id',
        'field_value',
        'langcode',
    ];

    protected $tokens = [];

    public static function rebuild()
    {
        $langcodes = lang()->getAvailableLangcodes();
        $records = [];
        foreach (Node::all() as $node) {
            $fields = $node->searchableFields();
            $record = [
                'node_id' => $node->id,
            ];
            foreach ($langcodes as $langcode) {
                $values = $node->cacheGetValues($langcode);
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
            foreach ($records as $record) {
                DB::table('indexes')->insert($record);
            }
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
            $content_id = $result->content_id;
            $content_field = $result->content_field;

            $result = $result->getSearchResult($keywords);
            if (! isset($results[$content_id])) {
                $results[$content_id] = [
                    'content_id' => $content_id,
                    'weight' => 0,
                ];
            }
            $results[$content_id][$content_field] =  $result['content'];
            $results[$content_id]['weight'] +=  $result['weight'];
        }

        // 对结果排序
        array_multisort(
            array_column($results, 'weight'),
            SORT_DESC,
            array_column($results, 'content_id'),
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
        $langcode = $langcode ?: langcode('frontend');

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
        $keywords = array_slice($keywords, 0, 10);
        $keywords = static::reorganizeKeywords($keywords);
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
    public static function reorganizeKeywords(array $keywords, $offset = 0)
    {
        // 计算每个单词的权重，从左到右依次降低
        $weightSequence = [];
        foreach ($keywords as $index => $word) {
            $weightSequence[] = exp(-0.5*pow($index/3.82, 2));
        }

        // 将单词按顺序组合成查询短句，并计算每个短句的权重
        $queryWeight = [];
        $offset = 0;
        while ($keywords) {

            $words = [];
            $weight = 0;
            foreach ($keywords as $index => $word) {
                $words[] = $word;
                $query = implode(' ', $words);
                $weight += $weightSequence[$offset + $index];
                $queryWeight[$query] = $weight;
            }

            $keywords = array_slice($keywords, 1);
            $offset++;
        }

        return $queryWeight;

        // $weights = [];
        // $words = [];
        // foreach ($keywords as $index => $word) {
        //     $words[] = $word;
        //     $key = implode(' ', $words);
        //     $weights[$key] = pow(2, $index + 1) - $offset/10;
        // }

        // $keywords = array_slice($keywords, 1);
        // if ($keywords) {
        //     $weights = array_merge($weights, static::weightKeywords($keywords, $offset + 1));
        // }

        // return $weights;
    }

    public function getSearchResult(array $keywords)
    {
        // $this->tokenize($keywords);
        $tokens = $this->splitByKeywords($keywords);

        $similar = $this->similar($this->attributes['field_value'], key($keywords));
        $weight = $this->weight*($this->attributes['weight'] ?? 1)*pow(10, pow($similar, 3));

        return [
            'content' => $this->joinTokens($tokens),
            'weight' => $weight,
        ];
    }

    protected function splitByKeywords(array $keywords)
    {
        $this->weight = 0;
        $content = trim($this->attributes['field_value']);
        $tokens = [];
        foreach ($keywords as $keyword => $weight) {
            $pos = stripos($content, $keyword);
            while ($pos !== false) {
                $tokens[] = substr($content, 0, $pos);

                $word = substr($content, $pos, strlen($keyword));
                if ($word !== $keyword) {
                    $weight *= 1 - str_diff($word, $keyword)*.5/strlen($keyword);
                }
                $this->weight += $weight;
                $tokens[] = '<span class="keyword">' . $word . '</span>';

                $content = substr($content, $pos + strlen($keyword));
                $pos = stripos($content, $keyword);
            }
        }
        if (!empty($content)) {
            $tokens[] = $content;
        }

        return $tokens;
    }

    protected function joinTokens(array $tokens)
    {
        $content = trim($this->attributes['field_value']);
        if (strlen($content) <= 200) {
            return implode('', $tokens);
        }

        $pieces = [];
        $length = 0;
        foreach ($tokens as $index => $token) {
            if ($index%2) {
                $left = $tokens[$index-1];
                if ($left) {
                    $left = explode(' ', $tokens[$index-1]);
                    $left = array_slice($left, -1*min(intval(count($left)/2), 5));
                    $left = implode(' ', $left);
                }

                $right = explode(' ', $tokens[$index+1]);
                $right = array_slice($right, 0, min(intval(count($right)/2), 5));
                $right = implode(' ', $right);

                $piece = $left.$token.$right;
                $pieces[] = $piece;
                $length += strlen($piece) - 29;

                if ($length >= 200) {
                    break;
                }
            }
        }

        return '... '.implode(' ... ', $pieces).' ...';
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
