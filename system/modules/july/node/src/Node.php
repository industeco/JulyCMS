<?php

namespace July\Node;

use App\Entity\TranslatableEntityBase;
use App\Support\JustInTwig;
use Illuminate\Support\Facades\Log;
use July\Node\CatalogSet;

class Node extends TranslatableEntityBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'nodes';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'mold_id',
        'title',
        'view',
        'is_red',
        'is_green',
        'is_blue',
        'langcode',
    ];

    /**
     * 不可更新字段
     *
     * @var array
     */
    protected $immutable = [
        'mold_id',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_red' => 'bool',
        'is_green' => 'bool',
        'is_blue' => 'bool',
    ];

    /**
     * 附加属性
     *
     * @var array
     */
    protected $appends = [
        'is_black',
        'is_white',
    ];

    /**
     * 获取实体类型类
     *
     * @return string
     */
    public static function getMoldClass()
    {
        return NodeType::class;
    }

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getFieldClass()
    {
        return NodeField::class;
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotClass()
    {
        return NodeFieldNodeType::class;
    }

    /**
     * 获取对应的模型集类
     *
     * @return string|null
     */
    public static function getModelSetClass()
    {
        return NodeSet::class;
    }

    /**
     * 组合属性：is_black
     *
     * @return bool
     */
    public function getIsBlackAttribute()
    {
        return !$this->is_red && !$this->is_green && !$this->is_blue;
    }

    /**
     * 组合属性：is_white
     *
     * @return bool
     */
    public function getIsWhiteAttribute()
    {
        return $this->is_red && $this->is_green && $this->is_blue;
    }

    /**
     * 获取所有实体，并附加指定的字段值
     *
     * @param  array $fields
     * @return \Illuminate\Support\Collection|array[]
     */
    public static function indexWith(...$fields)
    {
        // 允许以数组形式指定参数
        $fields = real_args($fields);
        if (! in_array('url', $fields)) {
            $fields[] = 'url';
        }

        // 字段值
        $fieldValues = static::getFieldValues($fields);

        // 获取所有消息数据，附带指定的字段值
        return static::all()->map(function(Node $node) use($fieldValues) {
                $attributes = $node->attributesToArray();

                $key = $attributes['id'].'/'.$node->getLangcode();
                foreach ($fieldValues as $field => $values) {
                    $attributes[$field] = $values[$key] ?? null;
                }
                $attributes['suggested_templates'] = $node->getSuggestedTemplates($attributes['url'] ?? null);
                return $attributes;
            })->keyBy('id');
    }

    /**
     * 获取建议模板
     *
     * @param  string|null 节点网址
     * @return array
     */
    public function getSuggestedTemplates(?string $url = null)
    {
        $localized = [];
        $templates = [];

        if ($this->view) {
            $localized[] = $this->view;
        }

        // 根据 id
        $templates[] = 'node--'.$this->id.'.twig';

        // 根据 url
        if (func_num_args() === 0) {
            $url = $this->url;
        }
        if ($url) {
            $url = str_replace('/', '_', trim($url, '\\/'));
            $localized[] = 'url--'.$url.'.twig';
        }

        // 根据类型
        $templates[] = 'mold--'.$this->mold_id.'.twig';

        return array_merge($localized, $templates);
    }

    /**
     * @return array
     */
    public function positions()
    {
        return CatalogNode::where('node_id', $this->id)->get()->groupBy('catalog_id')->toArray();
    }

    /**
     * Get content as a string of HTML.
     *
     * @param  \Twig\Environment|null $twig
     * @param  string|null $renderingLangcode 渲染语言
     * @return string
     */
    public function render($twig = null, $renderingLangcode = null)
    {
        if (! $twig) {
            /** @var \Twig\Environment */
            $twig = app('twig');
        }

        /** @var \App\Support\JustInTwig */
        $jit = $twig->getGlobals()['_jit'] ?? new JustInTwig;

        $view = $this->getPreferredTemplate();
        if (! $view) {
            return '';
        }

        $multiple = config('lang.multiple');

        $data = $this->gather();

        $url = $data['url'] ?? '/'.$this->getEntityPath();

        $globals = [
            '_node' => $this,
            '_langcode' => $renderingLangcode,
            '_path' => $this->get_path(),
            '_canonical' => $this->getCanonical($url),
            '_languages' => $this->getLanguageOptions($url),
        ];

        $jit->mergeGlobals($globals);

        foreach ($globals as $key => $value) {
            $twig->addGlobal($key, $value);
        }
        $twig->addGlobal('_jit', $jit);

        if ($multiple) {
            if ($renderingLangcode) {
                $data['url'] = '/'.strtolower($renderingLangcode).$url;
                config()->set('lang.output', $renderingLangcode);
            }
            config()->set('lang.rendering', $renderingLangcode ?? $this->getLangcode());
        }

        // 生成 html
        $html = $twig->render($view, $data);

        config()->set('lang.rendering', null);
        config()->set('lang.output', null);

        $html = html_compress($html);

        $this->cacheHtml($html, $data['url']);

        return $html;
    }

    /**
     * 获取可能的模板
     *
     * @return string|null
     */
    public function getPreferredTemplate()
    {
        foreach ($this->getSuggestedTemplates() as $view) {
            if (is_file(frontend_path('template/'.$view))) {
                return $view;
            }
        }

        return null;
    }

    /**
     * 计算权威页面
     *
     * @param  string|null $url 指定 url
     * @return string
     */
    public function getCanonical(string $url = null)
    {
        $url = $url ?? $this->getPathAlias() ?? '/'.$this->getEntityPath();

        // 如果不是前台默认语言，则权威页面加上语言代码
        if ($this->getLangcode() !== langcode('frontend')) {
            $url = '/'.$this->getLangcode().$url;
        }

        return rtrim(config('app.url'), '/').$url;
    }

    /**
     * 在指定的目录中，获取当前节点的直接子节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_children($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_children($this->id);
    }

    public function get_under($catalog = null)
    {
        return $this->get_children($catalog);
    }

    /**
     * 在指定的目录中，获取当前节点的所有子节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_descendants($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_descendants($this->id);
    }

    public function get_below($catalog = null)
    {
        return $this->get_descendants($catalog);
    }

    /**
     * 在指定的目录中，获取当前节点的直接父节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_parent($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_parent($this->id);
    }

    public function get_over($catalog = null)
    {
        return $this->get_parent($catalog);
    }

    /**
     * 在指定的目录中，获取当前节点的所有上级节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_ancestors($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_ancestors($this->id);
    }

    public function get_above($catalog = null)
    {
        return $this->get_ancestors($catalog);
    }

    /**
     * 在指定的目录中，获取当前节点的相邻节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_siblings($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_siblings($this->id);
    }

    public function get_around($catalog = null)
    {
        return $this->get_siblings($catalog);
    }

    /**
     * 在指定的目录中，获取当前节点的前一个节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_prev($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_prev($this->id);
    }

    /**
     * 在指定的目录中，获取当前节点的后一个节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_next($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_next($this->id);
    }

    /**
     * 获取当前节点在指定目录中的路径
     *
     * @param mixed $catalog
     * @return \Illuminate\Support\Collection
     */
    public function get_path($catalog = null)
    {
        return CatalogSet::fetch($catalog)->get_path($this->id);
    }

    /**
     * 获取内容标签
     *
     * @return \July\Node\NodeType
     */
    public function get_type()
    {
        return $this->mold;
    }

    /**
     * 获取内容标签
     *
     * @return \July\Node\CatalogSet
     */
    public function get_catalogs()
    {
        $catalogs = $this->catalogs()->get()->keyBy('id');

        return CatalogSet::make($catalogs);
    }

    public function get_url()
    {
        return rtrim(config('app.url'), '/').$this->url;
    }

    // /**
    //  * 查找坏掉的链接
    //  *
    //  * @return array
    //  */
    // public function findInvalidLinks()
    // {
    //     $langcode = $this->getLangcode();

    //     $pocket = new Pocket($this, 'html');
    //     $html = $pocket->get() ?? $this->render();
    //     if (! $html) {
    //         return [];
    //     }
    //     $html = new Html($html);

    //     $links = [];
    //     $nodeInfo = [
    //         'node_id' => $this->id,
    //         'node_title' => $this->title,
    //         'url' => $this->url,
    //         'langcode' => $langcode,
    //     ];

    //     $disk = Storage::disk('public');

    //     // images
    //     foreach ($html->extractImageLinks() as $link) {
    //         if (! $disk->exists($link)) {
    //             $links[] = array_merge($nodeInfo, ['link' => $link]);
    //         }
    //     }

    //     // PDFs
    //     foreach ($html->extractPdfLinks() as $link) {
    //         if (! $disk->exists($link)) {
    //             $links[] = array_merge($nodeInfo, ['link' => $link]);
    //         }
    //     }

    //     // hrefs
    //     $disk = Storage::disk('storage');
    //     foreach ($html->extractPageLinks() as $link) {
    //         $url = $link;
    //         if (substr($url, -5) !== '.html') {
    //             $url = rtrim($url, '/').'/index.html';
    //         }
    //         if (!$disk->exists('pages'.$url) && !$disk->exists('pages/'.$langcode.$url)) {
    //             $links[] = array_merge($nodeInfo, ['link' => $link]);
    //         }
    //     }

    //     return $links;
    // }
}
