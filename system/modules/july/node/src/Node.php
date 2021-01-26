<?php

namespace July\Node;

use App\Entity\EntityBase;
use App\Utils\Html;
use App\Utils\Pocket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\EntityField\FieldBase;
use App\EntityField\FieldTypes\FieldTypeManager;
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
     * （数据库表的）列名登记处
     *
     * @var array
     */
    protected static $columns = [
        'id',
        'mold_id',
        'langcode',
        'is_red',
        'is_green',
        'is_blue',
        'created_at',
        'updated_at',
    ];

    /**
     * 获取实体类型类
     *
     * @return string
     */
    public static function getMoldModel()
    {
        return NodeType::class;
    }

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getFieldModel()
    {
        return NodeField::class;
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotModel()
    {
        return NodeFieldNodeType::class;
    }

    /**
     * 获取所有列名
     *
     * @return array
     */
    public static function getColumns()
    {
        return static::$columns;
    }

    /**
     * {@inheritdoc}
     */
    public function collectFields()
    {
        $fields = NodeField::globalFields()->get();
        if ($this->exists) {
            $fields = $fields->merge($this->fields()->get());
        } elseif ($mold = $this->getMold()) {
            $fields = $fields->merge($mold->fields);
        } else {
            $fields = $fields->merge(NodeField::presetFields()->get());
        }

        return $fields->map(function(FieldBase $field) {
                return $field->bindEntity($this);
            })->keyBy(function(FieldBase $field) {
                return $field->getKey();
            });
    }

    /**
     * 获取所有实体字段
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        return $this->belongsToMany(NodeField::class, NodeFieldNodeType::class, 'mold_id', 'field_id', 'mold_id')
                    ->orderBy('node_field_node_type.delta')
                    ->withPivot([
                        'delta',
                        'label',
                        'description',
                        'helpertext',
                        'is_required',
                    ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function catalogs()
    {
        return $this->belongsToMany(Catalog::class, 'catalog_node', 'node_id', 'catalog_id')
                    ->withPivot([
                        'parent_id',
                        'prev_id',
                        'langcode',
                    ]);
    }

    /**
     * 获取关联的标签
     *
     * @param  string $langcode
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(string $langcode = null)
    {
        if ($langcode) {
            return $this->belongsToMany(Tag::class, 'node_tag', 'node_id', 'tag')
                ->wherePivot('langcode', $langcode);
        }

        return $this->belongsToMany(Tag::class, 'node_tag', 'node_id', 'tag')
            ->withPivot(['langcode']);
    }

    /**
     * @return array
     */
    public function positions()
    {
        return CatalogNode::where('node_id', $this->id)->get()->groupBy('catalog_id')->toArray();
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
     * 获取用于渲染页面表单的原料
     *
     * @return array
     */
    public function retrieveFormMaterials()
    {
        return array_merge(
            $this->retrieveFieldMaterials(),
            $this->retrieveLinkMaterials()
        );
    }

    /**
     * 获取字段渲染原料（与字段相关的一组信息，用于组成表单）
     *
     * @return array
     */
    public function retrieveFieldMaterials()
    {
        $materials = [];
        foreach ($this->collectFields() as $field) {
            $materials[$field->getKey()] = array_merge(
                FieldTypeManager::findOrFail($field['field_type_id'])->bindField($field)->getMaterials(),
                ['preset_type' => $field->preset_type]
            );
        }

        return $materials;

        // $pocket = new Pocket(static::class);
        // $key = join('/', [
        //     $this->attributes['node_type_id'] ?? '{node_type_id}',
        //     $this->getLangcode() ?: '{langcode}',
        //     'field_materials',
        // ]);

        // if ($materials = $pocket->get($key)) {
        //     $materials = $materials->value();
        // }

        // $modified = last_modified(backend_path('template/components/'));
        // if (!$materials || $materials['created_at'] < $modified) {
        //     $materials = [];
        //     foreach ($this->collectFields() as $field) {
        //         $materials[$field->getKey()] = array_merge(
        //             FieldType::findOrFail($field)->getMaterials(),
        //             ['preset_type' => $field->preset_type]
        //         );
        //     }

        //     $materials = [
        //         'created_at' => time(),
        //         'materials' => $materials,
        //     ];

        //     $pocket->put($key, $materials);
        // }

        // return $materials['materials'];
    }

    public function retrieveLinkMaterials()
    {
        return [];
    }


    // public function searchableFields()
    // {
    //     $fields = [];
    //     foreach ($this->nodeType->cacheGetFields() as $field) {
    //         if ($field['is_searchable']) {
    //             $fields[$field['id']] = [
    //                 'field_type' => $field['field_type'],
    //                 'weight' => $field['weight'] ?? 1,
    //             ];
    //         }
    //     }

    //     return $fields;
    // }

    // public function cacheGetValues($langcode = null)
    // {
    //     $langcode = $langcode ?: $this->langcode;
    //     $cachekey = $this->cacheKey([
    //         'key' => 'values',
    //         'langcode' => $langcode,
    //     ]);

    //     if ($values = $this->cacheGet($cachekey)) {
    //         $values = $values['value'];
    //     } else {
    //         $values = [];
    //         foreach ($this->fields() as $field) {
    //             $values[$field->getKey()] = $field->getValue($this->getKey(), $langcode);
    //         }
    //         $this->cachePut($cachekey, $values);
    //     }

    //     return $values;
    // }

    // /**
    //  * 获取内容标签
    //  *
    //  * @param string|null $langcode
    //  * @return array
    //  */
    // public function cacheGetTags($langcode = null)
    // {
    //     $langcode = $langcode ?: $this->langcode;
    //     $cachekey = $this->cacheKey([
    //         'key' => 'tags',
    //         'langcode' => $langcode,
    //     ]);

    //     if ($tags = $this->cacheGet($cachekey, $langcode)) {
    //         $tags = $tags['value'];
    //     } else {
    //         $tags = $this->tags($langcode)->get()->pluck('tag')->toArray();
    //         if (empty($tags)) {
    //             $tags = [];
    //             foreach ($this->tags($this->getAttribute('langcode'))->get() as $tag) {
    //                 $tags[] = $tag->getRightTag($langcode);
    //             }
    //         }
    //         $this->cachePut($cachekey, $tags);
    //     }

    //     return $tags;
    // }

    // /**
    //  * 保存属性值
    //  */
    // public function saveValues(array $values)
    // {
    //     Pocket::make($this)->clear('values/'.$this->getLangcode());
    //     // $this->cacheClear(['key'=>'values', 'langcode'=>langcode('content')]);
    //     // Log::info('CacheKey: '.static::cacheKey($this->id.'/values', langcode('content')));

    //     $values = Arr::only($values, $values['changed_values']);

    //     foreach ($this->collectEntityFields() as $field) {
    //         if (is_null($value = $values[$field->id] ?? null)) {
    //             $field->deleteValue();
    //         } else {
    //             $field->saveValue($value);
    //         }
    //     }

    //     // Log::info($this->cacheGetValues());
    // }

    // public function saveTags(array $tags, $langcode = null)
    // {
    //     $langcode = $langcode ?: $this->langcode;
    //     $this->cacheClear(['key'=>'tags', 'langcode'=>$langcode]);

    //     Term::createIfNotExist($tags, $langcode);

    //     $tags = array_fill_keys($tags, ['langcode' => $langcode]);
    //     $this->tags($langcode)->sync($tags);
    // }

    // /**
    //  * 保存当前内容在各目录中的位置
    //  *
    //  * @param  array $positions 待保存的位置信息
    //  * @param  bool $deleteNull 是否删除 null 值
    //  * @return void
    //  */
    // public function savePositions(array $positions, $deleteNull = false)
    // {
    //     $node_id = $this->getKey();
    //     foreach ($positions as $position) {
    //         $catalog = Catalog::findOrFail($position['catalog_id']);
    //         $position['node_id'] = $node_id;
    //         $catalog->insertPosition($position);
    //         // if (! is_null($position)) {
    //         // } elseif ($deleteNull) {
    //         //     $catalog->removePosition($position);
    //         // }
    //     }
    // }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function(Node $node) {
            foreach ($node->collectFields() as $field) {
                $field->deleteValue();
            }
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
        $tpl = $this->template();
        if (! $tpl) {
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
        $html = $twig->render($tpl, $data);

        config()->set('render_langcode', null);

        $html = preg_replace('/\n\s+/', "\n", $html);
        Pocket::make($this)->put('html', $html);

        return $html;
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
        if ($this->getLangcode() !== langcode('page')) {
            $url = '/'.$this->getLangcode().$url;
        }

        return rtrim(config('app.url'), '/').$url;
    }

    /**
     * 获取 templates
     *
     * @return array
     */
    public function getTemplatesAttribute()
    {
        return $this->getSuggestedTemplates();
    }

    // /**
    //  * 获取可能的模板
    //  *
    //  * @return string|null
    //  */
    // public function template()
    // {
    //     $langcode = $this->getLangcode();

    //     foreach ($this->getSuggestedTemplates() as $tpl) {
    //         $tpl = str_replace('{langcode}', $langcode, $tpl);
    //         if (is_file(frontend_path('template/'.$tpl))) {
    //             return $tpl;
    //         }
    //     }

    //     return null;
    // }

    /**
     * 获取建议模板
     *
     * @return array
     */
    public function getSuggestedTemplates()
    {
        $templates = [];

        if ($template = $this->getPartialView()) {
            $templates[] = $template;
        }

        // 按 id
        $templates[] = 'node_'.$this->id.'.{langcode}.twig';
        $templates[] = 'node_'.$this->id.'.twig';

        // 按 url
        if ($url = $this->getPathAlias()) {
            # code...
            $url = str_replace('/', '_', trim($url, '\\/'));
            $templates[] = 'url_'.$url.'.{langcode}.twig';
            $templates[] = 'url_'.$url.'.twig';
        }

        if ($parent = Catalog::default()->tree()->parent($this->id)) {
            $templates[] = 'under_' . $parent[0] . '.{langcode}.twig';
            $templates[] = 'under_' . $parent[0] . '.twig';
        }

        // 针对该节点类型的模板
        $templates[] = 'type_'.$this->node_type_id.'.{langcode}.twig';
        $templates[] = 'type_'.$this->node_type_id.'.twig';

        return $templates;
    }

    public function findInvalidLinks($langcode)
    {
        $html = $this->retrieveHtml($langcode);
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
        return $this->nodeType;
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
