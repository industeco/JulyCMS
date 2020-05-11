<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\ModelCollections\CatalogCollection;
use Twig\Environment as Twig;

class Node extends JulyModel
{
    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'is_preset',
        'node_type',
        'langcode',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
    ];

    public function nodeType()
    {
        return $this->belongsTo(NodeType::class, 'node_type');
    }

    public function fields()
    {
        $globalFields = NodeField::findMany(NodeField::globalFields());
        return $this->nodeType->fields->merge($globalFields);
    }

    public function catalogs()
    {
        return $this->belongsToMany(Node::class, 'catalog_node', 'node_id', 'catalog')
                ->withPivot(
                    'parent_id',
                    'prev_id',
                    'langcode'
                );
    }

    public function positions()
    {
        return CatalogNode::where('node_id', $this->id)->get()->groupBy('catalog')->toArray();
    }

    public static function make(array $attributes = [])
    {
        $nodeType = NodeType::findOrFail($attributes['node_type'] ?? null);
        return new static([
            'node_type' => $nodeType->truename,
            'langcode' => langcode('content_value'),
        ]);
    }

    public static function urls($langcode = null)
    {
        $langcode = $langcode ?: config('translate.default_langcode.site_page');
        $urls = [];
        $nodes = Catalog::default()->get_nodes();
        foreach ($nodes as $node) {
            $node = $node->retrieveValues($langcode);
            if ($url = $node['url'] ?? null) {
                $urls[$url] = true;
            }
        }
        return array_keys($urls);
    }

    public static function allNodes($langcode = null)
    {
        $nodes = [];
        foreach (Node::all() as $node) {
            $nodes[$node->id] = $node->getData($langcode);
        }
        return $nodes;
    }

    public function getData($langcode = null)
    {
        return array_merge(
            $this->getAttributes(),
            $this->retrieveValues($langcode),
            $this->retrieveTags($langcode)
        );
    }

    public function searchableFields()
    {
        $fields = [];
        foreach ($this->nodeType->retrieveFields() as $field) {
            if ($field['is_searchable']) {
                $fields[$field['truename']] = [
                    'field_type' => $field['field_type'],
                    'weight' => $field['weight'] ?? $field['index_weight'] ?? 1,
                ];
            }
        }
        return $fields;
    }

    public function retrieveTags($langcode = null)
    {
        return [
            'tags' => [],
        ];
    }

    public function retrieveValues($langcode = null)
    {
        $langcode = $langcode ?: langcode('content_value');

        $cacheid = $this->attributes['id'].'/values';
        if ($values = $this->cacheGet($cacheid, $langcode)) {
            $values = $values['value'];
        } else {
            $values = [];
            foreach ($this->fields() as $field) {
                $values[$field->truename] = $field->getValue($this, $langcode);
            }
            $this->cachePut($cacheid, $values, $langcode);
        }

        return $values;
    }

    // public static function retrieveFieldJigsaws(NodeType $nodeType, array $values = [])
    // {
    //     $langcode = langcode('admin_page');

    //     // 表单左侧字段碎片
    //     $jigsaws = $nodeType->retrieveFieldJigsaws($langcode);
    //     foreach ($jigsaws as $fieldName => &$jigsaw) {
    //         $jigsaw['value'] = $values[$fieldName] ?? null;
    //     }
    //     unset($jigsaw);

    //     // 表单右侧字段碎片
    //     $jigsawsAside = NodeField::retrieveGlobalFieldJigsaws($langcode);
    //     foreach ($jigsawsAside as $fieldName => &$jigsaw) {
    //         $jigsaw['value'] = $values[$fieldName] ?? null;
    //     }
    //     unset($jigsaw);

    //     return [
    //         'jigsaws' => $jigsaws,
    //         'jigsawsAside' => $jigsawsAside,
    //     ];
    // }

    // public static function prepareRequest(Request $request)
    // {
    //     $nodeType = NodeType::findOrFail($request->input('node_type'));
    //     return [
    //         'node_type' => $nodeType->truename,
    //         'langcode' => langcode('content_value'),
    //     ];
    // }

    /**
     * 保存属性值
     */
    public function saveValues(array $values, $deleteNull = false)
    {
        $this->cacheClear($this->id.'/values', langcode('content_value'));
        Log::info('CacheKey: '.$this->cacheKey($this->id.'/values', langcode('content_value')));

        $changed = $values['changed_values'];
        // Log::info('Saving Values. Values Changed:');
        // Log::info($changed);
        foreach ($this->fields() as $field) {
            if (! in_array($field->truename, $changed)) {
                // Log::info("'{$field->truename}' is not changed.");
                continue;
            }
            $value = $values[$field->truename] ?? null;
            // Log::info("'{$field->truename}' is changed. The new value is '{$value}'");
            if (! is_null($value)) {
                // Log::info("Prepare to update field '{$field->truename}'");
                $field->setValue($value, $this->id);
            } elseif ($deleteNull) {
                $field->deleteValue($this->id);
            }
        }

        Log::info($this->retrieveValues());
    }

    /**
     * 保存当前内容在各目录中的位置
     */
    public function savePositions(array $positions, $deleteNull = false)
    {
        foreach ($positions as $position) {
            $catalog = Catalog::findOrFail($position['catalog']);
            $position['node_id'] = $this->id;
            if (! is_null($position)) {
                $catalog->insertPosition($position);
            } elseif ($deleteNull) {
                $catalog->removePosition($position);
            }
        }
    }

    public static function boot()
    {
        parent::boot();

        static::deleted(function(Node $node) {
            foreach ($node->fields() as $field) {
                $field->deleteValue($node->id);
            }
        });
    }

    /**
     * 生成页面
     *
     * @param \Twig\Environment $twig
     * @return bool
     */
    public function render(Twig $twig, $langcode = null)
    {
        $langcode = $langcode ?? langcode('content_value');

        // 获取节点值
        $node = $this->getData($langcode);

        if ($tpl = $this->template()) {
            // 更新文件名
            $file = 'pages/'.$langcode.'/'.ltrim($node['url'], '/');

            $twig->addGlobal('_node', $this);

            // 生成 html 并写入文件
            return Storage::disk('public')->put($file, $twig->render($tpl, $node));
        }

        return false;
    }

    /**
     * 获取可能的模板
     */
    public function template()
    {
        foreach ($this->suggestedTemplates() as $tpl) {
            if (is_file(twig_path($tpl))) {
                return $tpl;
            }
        }
        return null;
    }

    public function suggestedTemplates()
    {
        $node = $this->getData();
        if (!$node['url']) return [];

        $templates = [];
        try {
            if ($node['template']) {
                $templates[] = ltrim($node['template'], '/');
            }

            // 针对该节点的模板
            $templates[] = 'node--' . str_replace('/', '--', ltrim($node['url'], '/')) . '.twig';

            // 针对该节点类型的模板
            $templates[] = 'type--' . $node['node_type'] . '.twig';
        } catch (\Throwable $th) {
            dd($node);
        }

        return $templates;
    }

    public function getHtml($langcode = null)
    {
        $url = $this->url;
        if (! $url) {
            return '';
        }

        $langcode = $langcode ?: $this->langcode;
        $html = 'pages/'.$langcode.$url;

        $disk = Storage::disk('public');
        if (! $disk->exists($html)) {
            return '';
        }

        return $disk->get($html);
    }

    public function findInvalidLinks($langcode = null)
    {
        $langcode = $langcode ?: $this->langcode;
        $html = $this->getHtml($langcode);
        if (! $html) {
            return [];
        }

        $disk = Storage::disk('public');
        $links = [];
        $nodeInfo = [
            'node_id' => $this->id,
            'node_title' => $this->title,
            'node_url' => $this->url,
            'langcode' => $langcode,
        ];

        // images
        foreach (extract_image_links($html) as $link) {
            if (! $disk->exists($link)) {
                $links[] = array_merge($nodeInfo, ['link' => $link]);
            }
        }

        // PDFs
        foreach (extract_pdf_links($html) as $link) {
            if (! $disk->exists($link)) {
                $links[] = array_merge($nodeInfo, ['link' => $link]);
            }
        }

        // hrefs
        foreach (extract_page_links($html) as $link) {
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

    public function __isset($key)
    {
        return ! is_null($this->{$key});
    }

    /**
     * Dynamically retrieve attributes on Node.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (! $key) {
            return;
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributes) || in_array($key, $this->fillable) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(HasAttributes::class, $key)) {
            return;
        }

        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        $values = $this->retrieveValues();
        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return null;
    }

    /**
     * 在指定的树中，获取当前节点集的直接子节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_children($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_children($this->id);
    }

    public function get_under($catalog = null)
    {
        return $this->get_children($catalog);
    }

    /**
     * 在指定的树中，获取当前节点的所有子节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_descendants($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_descendants($this->id);
    }

    public function get_below($catalog = null)
    {
        return $this->get_descendants($catalog);
    }

    /**
     * 在指定的树中，获取当前节点的直接父节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_parent($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_parent($this->id);
    }

    public function get_over($catalog = null)
    {
        return $this->get_parent($catalog);
    }

    /**
     * 在指定的树中，获取当前节点的所有上级节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_ancestors($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_ancestors($this->id);
    }

    public function get_above($catalog = null)
    {
        return $this->get_ancestors($catalog);
    }

    /**
     * 在指定的树中，获取当前节点的相邻节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_siblings($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_siblings($this->id);
    }

    public function get_around($catalog = null)
    {
        return $this->get_siblings($catalog);
    }
}
