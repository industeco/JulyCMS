<?php

namespace July\Core\Node;

use App\Utils\Arr;
use App\Utils\Html;
use App\Utils\Pocket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use July\Core\Entity\EntityBase;
use July\Core\EntityField\EntityFieldBase;
use July\Core\EntityField\FieldType;
use July\Core\Node\CatalogSet;
use July\Core\Taxonomy\Term;
use July\Core\Taxonomy\TermSet;
use Twig\Environment as Twig;

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
        'node_type_id',
        'langcode',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'templates',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nodeType()
    {
        return $this->belongsTo(NodeType::class);
    }

    /**
     * 获取所有实体字段
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        return $this->belongsToMany(NodeField::class, NodeTypeNodeField::class, 'node_type_id', 'node_field_id', 'node_type_id')
                    ->orderBy('node_fields.preset_type', 'desc')
                    ->orderBy('node_field_node_type.delta')
                    ->withPivot([
                        'delta',
                        'weight',
                        'label',
                        'description',
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
     * {@inheritdoc}
     */
    public function getEntityFields()
    {
        return NodeField::getGlobalFields()->merge($this->fields)
            ->map(function(EntityFieldBase $field) {
                return $field->bindEntity($this);
            })
            ->keyBy(function(EntityFieldBase $field) {
                return $field->getKey();
            });
    }

    /**
     * 获取字段拼图（与字段相关的一组信息，用于组成表单）
     *
     * @param  string|null $langcode
     * @return array
     */
    public function takeEntityFieldMaterials()
    {
        $langcode = $this->getLangcode();

        $pocket = new Pocket($this);
        $key = 'entity_fields_material/'.$langcode;

        if ($materials = $pocket->get($key)) {
            $materials = $materials->value();
        }

        $modified = last_modified(backend_path('template/components/'));
        if (!$materials || $materials['created_at'] < $modified) {
            $materials = [];
            foreach ($this->getEntityFields() as $field) {
                $materials[$field->getKey()] = FieldType::findOrFail($field)->setLangcode($langcode)->getMaterials();
            }

            $materials = [
                'created_at' => time(),
                'materials' => $materials,
            ];

            $pocket->put($key, $materials);
        }

        return $materials['materials'];
    }


    public function searchableFields()
    {
        $fields = [];
        foreach ($this->contentType->cacheGetFields() as $field) {
            if ($field['is_searchable']) {
                $fields[$field['id']] = [
                    'field_type' => $field['field_type'],
                    'weight' => $field['weight'] ?? 1,
                ];
            }
        }

        return $fields;
    }

    public function cacheGetValues($langcode = null)
    {
        $langcode = $langcode ?: $this->langcode;
        $cachekey = $this->cacheKey([
            'key' => 'values',
            'langcode' => $langcode,
        ]);

        if ($values = $this->cacheGet($cachekey)) {
            $values = $values['value'];
        } else {
            $values = [];
            foreach ($this->fields() as $field) {
                $values[$field->getKey()] = $field->getValue($this->getKey(), $langcode);
            }
            $this->cachePut($cachekey, $values);
        }

        return $values;
    }

    /**
     * 获取内容标签
     *
     * @param string|null $langcode
     * @return array
     */
    public function cacheGetTags($langcode = null)
    {
        $langcode = $langcode ?: $this->langcode;
        $cachekey = $this->cacheKey([
            'key' => 'tags',
            'langcode' => $langcode,
        ]);

        if ($tags = $this->cacheGet($cachekey, $langcode)) {
            $tags = $tags['value'];
        } else {
            $tags = $this->tags($langcode)->get()->pluck('tag')->toArray();
            if (empty($tags)) {
                $tags = [];
                foreach ($this->tags($this->getAttribute('langcode'))->get() as $tag) {
                    $tags[] = $tag->getRightTag($langcode);
                }
            }
            $this->cachePut($cachekey, $tags);
        }

        return $tags;
    }

    /**
     * 保存属性值
     */
    public function saveValues(array $values)
    {
        Pocket::create($this)->clear('values/'.$this->getLangcode());
        // $this->cacheClear(['key'=>'values', 'langcode'=>langcode('content')]);
        // Log::info('CacheKey: '.static::cacheKey($this->id.'/values', langcode('content')));

        $values = Arr::only($values, $values['changed_values']);

        foreach ($this->getEntityFields() as $field) {
            if (is_null($value = $values[$field->id] ?? null)) {
                $field->deleteValue();
            } else {
                $field->saveValue($value);
            }
        }

        // Log::info($this->cacheGetValues());
    }

    public function saveTags(array $tags, $langcode = null)
    {
        $langcode = $langcode ?: $this->langcode;
        $this->cacheClear(['key'=>'tags', 'langcode'=>$langcode]);

        Term::createIfNotExist($tags, $langcode);

        $tags = array_fill_keys($tags, ['langcode' => $langcode]);
        $this->tags($langcode)->sync($tags);
    }

    /**
     * 保存当前内容在各目录中的位置
     *
     * @param  array $positions 待保存的位置信息
     * @param  bool $deleteNull 是否删除 null 值
     * @return void
     */
    public function savePositions(array $positions, $deleteNull = false)
    {
        $node_id = $this->getKey();
        foreach ($positions as $position) {
            $catalog = Catalog::findOrFail($position['catalog_id']);
            $position['node_id'] = $node_id;
            if (! is_null($position)) {
                $catalog->insertPosition($position);
            } elseif ($deleteNull) {
                $catalog->removePosition($position);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::deleted(function(Node $node) {
            foreach ($node->getEntityFields() as $field) {
                $field->deleteValue();
            }
        });
    }

    /**
     * 生成页面
     *
     * @param \Twig\Environment $twig
     * @return string|null
     */
    public function render(Twig $twig = null)
    {
        $twig = $twig ?? $twig = twig();
        $langcode = $this->getLangcode();

        config()->set('render_langcode', $langcode);

        // 获取节点值
        $data = $this->gather($langcode);

        if ($tpl = $this->template($langcode)) {

            $twig->addGlobal('_node', $this);
            $twig->addGlobal('_path', $this->get_path());

            $canonical = '/'.ltrim($data['url'], '/');
            if ($langcode === langcode('page')) {
                $canonical = rtrim(config('app.url'), '/').$canonical;
            } else {
                $canonical = rtrim(config('app.url'), '/').'/'.$langcode.$canonical;
            }
            $twig->addGlobal('_canonical', $canonical);

            // 生成 html
            $html = $twig->render($tpl, $data);

            //
            $html = preg_replace('/\n\s+/', "\n", $html);

            // 写入文件
            if ($data['url']) {
                $file = 'pages/'.$langcode.'/'.ltrim($data['url'], '/');
                Storage::disk('storage')->put($file, $html);
            }

            return $html;
        }

        return null;
    }

    /**
     * 获取 templates
     *
     * @return array
     */
    public function getTemplatesAttribute()
    {
        return $this->suggestedTemplates();
    }

    /**
     * 获取可能的模板
     *
     * @param  string $langcode
     * @return string|null
     */
    public function template(string $langcode = null)
    {
        foreach ($this->suggestedTemplates($langcode) as $tpl) {
            if (false !== strpos($tpl, '{langcode}')) {
                if ($langcode) {
                    $tpl = str_replace('{langcode}', $langcode, $tpl);
                } else {
                    continue;
                }
            }

            if (is_file(frontend_path('template/'.$tpl))) {
                return $tpl;
            }
        }

        return null;
    }

    /**
     * 获取建议模板
     *
     * @param  string $langcode
     * @return array
     */
    public function suggestedTemplates(string $langcode = null)
    {
        $node = $this->gather($langcode);
        if (empty($node['url'] ?? null)) return [];

        $templates = [];
        if ($node['template'] ?? null) {
            $templates[] = ltrim($node['template'], '/');
        }

        // 按 url
        $url = str_replace('/', '--', trim($node['url'], '\\/'));
        $templates[] = 'url_'.$url.'.{langcode}.twig';
        $templates[] = 'url_'.$url.'.twig';

        // 按 id
        $templates[] = 'node_'.$node['id'].'.{langcode}.twig';
        $templates[] = 'node_'.$node['id'].'.twig';

        if ($parent = Catalog::default()->tree()->parent($this->id)) {
            $templates[] = 'under_' . $parent[0] . '.{langcode}.twig';
            $templates[] = 'under_' . $parent[0] . '.twig';
        }

        // 针对该节点类型的模板
        $templates[] = 'type_'.$node['node_type_id'].'.{langcode}.twig';
        $templates[] = 'type_'.$node['node_type_id'].'.twig';

        return $templates;
    }

    public function findInvalidLinks($langcode)
    {
        $html = $this->getHtml($langcode);
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
     * @return \July\Core\Node\NodeSet
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
     * @return \July\Core\Node\NodeSet
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
     * @return \July\Core\Node\NodeSet
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
     * @return \July\Core\Node\NodeSet
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
     * @return \July\Core\Node\NodeSet
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
     * @return \July\Core\Node\NodeSet
     */
    public function get_prev($catalog = null)
    {
        return CatalogSet::find($catalog)->get_prev($this->id);
    }

    /**
     * 在指定的目录中，获取当前节点的后一个节点
     *
     * @param mixed $catalog
     * @return \July\Core\Node\NodeSet
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
     * @return \July\Core\Node\NodeType
     */
    public function get_type()
    {
        return $this->nodeType;
    }

    /**
     * 获取内容标签
     *
     * @return \July\Core\Node\CatalogSet
     */
    public function get_catalogs()
    {
        $catalogs = $this->catalogs()->get()->keyBy('id');
        return CatalogSet::make($catalogs);
    }

    /**
     * 获取内容标签
     *
     * @return \July\Core\Taxonomy\TermSet
     */
    public function get_tags()
    {
        $langcode = config('render_langcode') ?? langcode('frontend');
        $tags = $this->tags($langcode)->get()->keyBy('tag');
        return TermSet::make($tags);
    }

    public function get_url()
    {
        return rtrim(config('app.url'), '/').'/'.ltrim($this->url, '/');
    }


    // public static function findByUrl($url, $langcode = null)
    // {
    //     $langcode = $langcode ?: langcode('node_value.default');
    //     $url = '/'.ltrim($url, '/');

    //     $record = DB::table('node__url')->where([
    //         ['url_value', $url],
    //         ['langcode', $langcode],
    //     ])->first();

    //     if ($record) {
    //         return static::find($record->node_id);
    //     }

    //     return null;
    // }

    // public function getHtml($langcode = null)
    // {
    //     $langcode = $langcode ?: $this->langcode;

    //     $values = $this->cacheGetValues($langcode);
    //     if ($url = $values['url'] ?? null) {
    //         $file = 'pages/'.$langcode.$url;
    //         $disk = Storage::disk('storage');
    //         if ($disk->exists($file)) {
    //             return $disk->get($file);;
    //         }

    //         return $this->render(null, $langcode);
    //     }

    //     return null;
    // }
}
