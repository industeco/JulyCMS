<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\ModelCollections\CatalogCollection;
use App\ModelCollections\TagCollection;
use Illuminate\Support\Facades\DB;
use Twig\Environment as Twig;

class Content extends BaseContent
{
    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'is_preset',
        'content_type',
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

    public static function getEntityId()
    {
        return 'content';
    }

    public static function getParentEntityId()
    {
        return null;
    }

    public function contentType()
    {
        return $this->belongsTo(ContentType::class, 'content_type');
    }

    public function fields()
    {
        return $this->contentType->fields->merge(ContentField::globalFields());
    }

    public function catalogs()
    {
        return $this->belongsToMany(Catalog::class, 'catalog_content', 'content_id', 'catalog')
                ->withPivot(
                    'parent_id',
                    'prev_id',
                    'langcode'
                );
    }

    public function tags($langcode = null)
    {
        if ($langcode) {
            return $this->belongsToMany(Tag::class, 'content_tag', 'content_id', 'tag')
                ->wherePivot('langcode', $langcode);
        }
        return $this->belongsToMany(Tag::class, 'content_tag', 'content_id', 'tag')
            ->withPivot('langcode');
    }

    public function positions()
    {
        return CatalogContent::where('content_id', $this->id)->get()->groupBy('catalog')->toArray();
    }

    public static function countByContentType()
    {
        $contents = [];
        $records = DB::select('SELECT `content_type`, count(`content_type`) as `total` FROM `contents` GROUP BY `content_type`');
        foreach ($records as $record) {
            $contents[$record->content_type] = $record->total;
        }

        return $contents;
    }

    public static function allNodes($langcode = null)
    {
        $contents = [];
        foreach (Content::all() as $content) {
            $contents[$content->id] = $content->gather($langcode);
        }
        return $contents;
    }

    public function gather($langcode = null)
    {
        return array_merge(
            $this->attributesToArray(),
            $this->cacheGetValues($langcode),
            ['tags' => $this->cacheGetTags($langcode)]
        );
    }

    public function searchableFields()
    {
        $fields = [];
        foreach ($this->contentType->cacheGetFields() as $field) {
            if ($field['is_searchable']) {
                $fields[$field['truename']] = [
                    'field_type' => $field['field_type'],
                    'weight' => $field['weight'] ?? 1,
                ];
            }
        }
        return $fields;
    }

    public function cacheGetValues($langcode = null)
    {
        $langcode = $langcode ?: $this->getAttributeValue('langcode');
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
        $langcode = $langcode ?: langcode('content');
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
    public function saveValues(array $values, $deleteNull = false)
    {
        $this->cacheClear(['key'=>'values', 'langcode'=>langcode('content')]);
        // Log::info('CacheKey: '.static::cacheKey($this->id.'/values', langcode('content')));

        $changed = $values['changed_values'];

        foreach ($this->fields() as $field) {
            if (! in_array($field->truename, $changed)) {
                // Log::info("'{$field->truename}' is not changed.");
                continue;
            }
            $value = $values[$field->truename] ?? null;
            // Log::info("'{$field->truename}' is changed. The new value is '{$value}'");
            if (! is_null($value)) {
                // Log::info("Prepare to update field '{$field->truename}'");
                $field->setValue($value, $this->getKey());
            } elseif ($deleteNull) {
                $field->deleteValue($this->getKey());
            }
        }

        // Log::info($this->cacheGetValues());
    }

    public function saveTags(array $tags, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $this->cacheClear(['key'=>'tags', 'langcode'=>$langcode]);

        Tag::createIfNotExist($tags, $langcode);

        $tags = array_fill_keys($tags, ['langcode' => $langcode]);
        $this->tags($langcode)->sync($tags);
    }

    /**
     * 保存当前内容在各目录中的位置
     */
    public function savePositions(array $positions, $deleteNull = false)
    {
        foreach ($positions as $position) {
            $catalog = Catalog::findOrFail($position['catalog']);
            $position['content_id'] = $this->id;
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

        static::deleted(function(Content $content) {
            foreach ($content->fields() as $field) {
                $field->deleteValue($content->id);
            }
        });
    }

    /**
     * 生成页面
     *
     * @param \Twig\Environment $twig
     * @return string|null
     */
    public function render(Twig $twig = null, $langcode = null)
    {
        $twig = $twig ?? $twig = twig('template', true);
        $langcode = $langcode ?: $this->langcode;

        config()->set('current_render_langcode', $langcode);

        // 获取节点值
        $content = $this->gather($langcode);

        if ($tpl = $this->template($langcode)) {

            $twig->addGlobal('_content', $this);
            $twig->addGlobal('_path', $this->get_path());

            $canonical = '/'.ltrim($content['url'], '/');
            if ($langcode === langcode('site_page')) {
                $canonical = rtrim(config('jc.url'), '/').$canonical;
            } else {
                $canonical = rtrim(config('jc.url'), '/').'/'.$langcode.$canonical;
            }
            $twig->addGlobal('_canonical', $canonical);

            // 生成 html
            $html = $twig->render($tpl, $content);

            // 写入文件
            if ($content['url']) {
                $file = 'pages/'.$langcode.'/'.ltrim($content['url'], '/');
                Storage::disk('storage')->put($file, $html);
            }

            return $html;
        }

        return null;
    }

    /**
     * 获取可能的模板
     */
    public function template($langcode = null)
    {
        foreach ($this->suggestedTemplates($langcode) as $tpl) {
            if (is_file(foreground_path('template/'.$tpl))) {
                return $tpl;
            }
        }
        return null;
    }

    public function suggestedTemplates($langcode = null)
    {
        $content = $this->gather($langcode);
        if (!$content['url']) return [];

        $templates = [];
        if ($content['template']) {
            $templates[] = ltrim($content['template'], '/');
        }

        // 按 url
        $url = str_replace('/', '--', trim($content['url'], '\\/'));
        $templates[] = 'url--'.($langcode ? $langcode.'-' : '').$url.'.twig';

        // 按 id
        if ($langcode) {
            $templates[] = 'content--'.$langcode.'-'.$content['id'].'.twig';
        }
        $templates[] = 'content--'.$content['id'].'.twig';

        if ($parent = Catalog::default()->tree()->parent($this->id)) {
            $templates[] = 'under--' . $parent[0] . '.twig';
        }

        // 针对该节点类型的模板
        $templates[] = 'type--' . $content['content_type'] . '.twig';

        return $templates;
    }

    public static function findByUrl($url, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content_value.default');
        $url = '/'.ltrim($url, '/');

        $record = DB::table('content__url')->where([
            ['url_value', $url],
            ['langcode', $langcode],
        ])->first();

        if ($record) {
            return static::find($record->content_id);
        }

        return null;
    }

    public function getHtml($langcode = null)
    {
        $langcode = $langcode ?: $this->langcode;

        $values = $this->cacheGetValues($langcode);
        if ($url = $values['url'] ?? null) {
            $file = 'pages/'.$langcode.$url;
            $disk = Storage::disk('storage');
            if ($disk->exists($file)) {
                return $disk->get($file);;
            }

            return $this->render(null, $langcode);
        }

        return null;
    }

    public function findInvalidLinks($langcode)
    {
        $html = $this->getHtml($langcode);
        if (! $html) {
            return [];
        }
        $html = html($html);

        $links = [];
        $contentInfo = [
            'content_id' => $this->id,
            'content_title' => $this->title,
            'content_url' => $this->url,
            'langcode' => $langcode,
        ];

        $disk = Storage::disk('public');

        // images
        foreach ($html->extractImageLinks() as $link) {
            if (! $disk->exists($link)) {
                $links[] = array_merge($contentInfo, ['link' => $link]);
            }
        }

        // PDFs
        foreach ($html->extractPdfLinks() as $link) {
            if (! $disk->exists($link)) {
                $links[] = array_merge($contentInfo, ['link' => $link]);
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
                $links[] = array_merge($contentInfo, ['link' => $link]);
            }
        }

        return $links;
    }

    public function __isset($key)
    {
        return ! is_null($this->{$key});
    }

    /**
     * Dynamically retrieve attributes on Content.
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

        $values = $this->cacheGetValues();
        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return null;
    }

    /**
     * 在指定的目录中，获取当前节点集的直接子节点
     *
     * @param mixed $catalog
     * @return ContentCollection
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
     * 在指定的目录中，获取当前节点的所有子节点
     *
     * @param mixed $catalog
     * @return ContentCollection
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
     * 在指定的目录中，获取当前节点的直接父节点
     *
     * @param mixed $catalog
     * @return ContentCollection
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
     * 在指定的目录中，获取当前节点的所有上级节点
     *
     * @param mixed $catalog
     * @return ContentCollection
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
     * 在指定的目录中，获取当前节点的相邻节点
     *
     * @param mixed $catalog
     * @return ContentCollection
     */
    public function get_siblings($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_siblings($this->id);
    }

    public function get_around($catalog = null)
    {
        return $this->get_siblings($catalog);
    }

    /**
     * 在指定的目录中，获取当前节点的前一个节点
     *
     * @param mixed $catalog
     * @return ContentCollection
     */
    public function get_prev($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_prev($this->id);
    }

    /**
     * 在指定的目录中，获取当前节点的后一个节点
     *
     * @param mixed $catalog
     * @return ContentCollection
     */
    public function get_next($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_next($this->id);
    }

    /**
     * 获取当前节点在指定目录中的路径
     *
     * @param mixed $catalog
     * @return \Illuminate\Support\Collection
     */
    public function get_path($catalog = null)
    {
        return CatalogCollection::find($catalog)->get_path($this->id);
    }

    /**
     * 获取内容标签
     *
     * @return \App\Models\ContentType
     */
    public function get_type()
    {
        return $this->contentType;
    }

    /**
     * 获取内容标签
     *
     * @return \App\ModelCollections\CatalogCollection
     */
    public function get_catalogs()
    {
        $catalogs = $this->catalogs()->get()->keyBy('truename');
        return CatalogCollection::make($catalogs);
    }

    /**
     * 获取内容标签
     *
     * @return \App\ModelCollections\TagCollection
     */
    public function get_tags()
    {
        $langcode = config('current_render_langcode') ?? langcode('site_page');
        $tags = $this->tags($langcode)->get()->keyBy('tag');
        return TagCollection::make($tags);
    }

    public function get_url()
    {
        return rtrim(config('jc.url'), '/').'/'.ltrim($this->url, '/');
    }
}
