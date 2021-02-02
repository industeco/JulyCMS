<?php

namespace July\Node;

use App\Entity\EntityBase;
use App\Utils\Html;
use App\Utils\Pocket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\EntityField\FieldBase;
use July\Node\CatalogSet;
use July\Taxonomy\Term;
use July\Taxonomy\TermSet;

class Node extends EntityBase
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
        'langcode',
        'is_red',
        'is_green',
        'is_blue',
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
     * 实体所属类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mold()
    {
        return $this->belongsTo(NodeType::class, 'mold_id');
    }

    // /**
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    //  */
    // public function catalogs()
    // {
    //     return $this->belongsToMany(Catalog::class, 'catalog_node', 'node_id', 'catalog_id')
    //                 ->withPivot([
    //                     'parent_id',
    //                     'prev_id',
    //                     'path',
    //                 ]);
    // }

    // /**
    //  * 关联标签
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    //  */
    // public function tags()
    // {
    //     return $this->belongsToMany(Tag::class, 'node_tag', 'node_id', 'tag')
    //         ->wherePivot('langcode', $this->getLangcode());
    // }

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
     * 附加属性 suggested_views 的 Get Mutator
     *
     * @return array
     */
    public function getSuggestedViews()
    {
        $localized = [];
        $views = [];

        if ($view = $this->getView()) {
            $localized[] = $view;
        }

        // 根据 id
        $localized[] = 'node--'.$this->id.'.{langcode}.twig';
        $views[] = 'node--'.$this->id.'.twig';

        // 根据 url
        if ($url = $this->getPathAlias()) {
            $url = str_replace('/', '_', trim($url, '\\/'));
            // $views[] = 'url--'.$url.'.{langcode}.twig';
            $localized[] = 'url--'.$url.'.twig';
        }

        // // 根据目录位置
        // if ($parent = Catalog::default()->tree()->parent($this->id)) {
        //     $localized[] = 'under--' . $parent[0] . '.{langcode}.twig';
        //     $views[] = 'under--' . $parent[0] . '.twig';
        // }

        // 根据类型
        $localized[] = 'mold--'.$this->mold_id.'.{langcode}.twig';
        $views[] = 'mold--'.$this->mold_id.'.twig';

        return array_merge($localized, $views);
    }

    /**
     * {@inheritdoc}
     */
    public function collectFields()
    {
        $fields = NodeField::groupbyPresetType()->get('preseted');
        if ($this->exists) {
            $fields = $fields->merge($this->fields->keyBy('id'));
        } elseif ($mold = $this->getMold()) {
            $fields = $fields->merge($mold->fields->keyBy('id'));
        }

        return $fields->map(function(FieldBase $field) {
                return $field->bindEntity($this);
            })->sortBy('delta');
    }

    /**
     * @return array
     */
    public function positions()
    {
        return CatalogNode::where('node_id', $this->id)->get()->groupBy('catalog_id')->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function(Node $node) {
            Pocket::make($node)->clear('html');
        });

        static::updated(function(Node $node) {
            Pocket::make($node)->clear('html');
        });
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function render()
    {
        $view = $this->getBestView();
        if (! $view) {
            return '';
        }

        $data = $this->gather();

        /** @var \Twig\Environment */
        $twig = app('twig');

        $twig->addGlobal('_node', $this);
        $twig->addGlobal('_path', $this->get_path());
        $twig->addGlobal('_canonical', $this->getCanonical($data['url'] ?? '/'.$this->getEntityPath()));

        config()->set('render_langcode', $this->getLangcode());

        // 生成 html
        $html = $twig->render($view, $data);

        config()->set('render_langcode', null);

        return preg_replace('/\n\s+/', "\n", $html);
    }

    /**
     * 获取可能的模板
     *
     * @return string|null
     */
    public function getBestView()
    {
        $langcode = $this->getLangcode();
        foreach ($this->getSuggestedViews() as $view) {
            $view = str_replace('{langcode}', $langcode, $view);
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
     * 查找坏掉的链接
     *
     * @return array
     */
    public function findInvalidLinks()
    {
        $langcode = $this->getLangcode();

        $pocket = new Pocket($this, 'html');
        $html = $pocket->get() ?? $this->render();
        if (! $html) {
            return [];
        }
        $html = new Html($html);

        $links = [];
        $nodeInfo = [
            'node_id' => $this->id,
            'node_title' => $this->title,
            'url' => $this->url,
            'langcode' => $langcode,
        ];

        $disk = Storage::disk('public');

        // images
        foreach ($html->extractImageLinks() as $link) {
            if (! $disk->exists($link)) {
                $links[] = array_merge($nodeInfo, ['link' => $link]);
            }
        }

        // PDFs
        foreach ($html->extractPdfLinks() as $link) {
            if (! $disk->exists($link)) {
                $links[] = array_merge($nodeInfo, ['link' => $link]);
            }
        }

        // hrefs
        $disk = Storage::disk('storage');
        foreach ($html->extractPageLinks() as $link) {
            $url = $link;
            if (substr($url, -5) !== '.html') {
                $url = rtrim($url, '/').'/index.html';
            }
            if (!$disk->exists('pages'.$url) && !$disk->exists('pages/'.$langcode.$url)) {
                $links[] = array_merge($nodeInfo, ['link' => $link]);
            }
        }

        return $links;
    }

    /**
     * 在指定的目录中，获取当前节点集的直接子节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_children($catalog = null)
    {
        return CatalogSet::find($catalog)->get_children($this->id);
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
        return CatalogSet::find($catalog)->get_descendants($this->id);
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
        return CatalogSet::find($catalog)->get_parent($this->id);
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
        return CatalogSet::find($catalog)->get_ancestors($this->id);
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
        return CatalogSet::find($catalog)->get_siblings($this->id);
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
        return CatalogSet::find($catalog)->get_prev($this->id);
    }

    /**
     * 在指定的目录中，获取当前节点的后一个节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_next($catalog = null)
    {
        return CatalogSet::find($catalog)->get_next($this->id);
    }

    /**
     * 获取当前节点在指定目录中的路径
     *
     * @param mixed $catalog
     * @return \Illuminate\Support\Collection
     */
    public function get_path($catalog = null)
    {
        return CatalogSet::find($catalog)->get_path($this->id);
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

    // /**
    //  * 获取内容标签
    //  *
    //  * @return \July\Taxonomy\TermSet
    //  */
    // public function get_tags()
    // {
    //     $langcode = config('render_langcode') ?? langcode('frontend');
    //     $tags = $this->tags($langcode)->get()->keyBy('tag');
    //     return TermSet::make($tags);
    // }

    public function get_url()
    {
        return rtrim(config('app.url'), '/').$this->url;
    }
}
