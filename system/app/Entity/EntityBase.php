<?php

namespace App\Entity;

use App\EntityValue\EntityPathAlias;
use App\EntityField\FieldBase;
use App\Models\ModelBase;
use App\Support\Arr;
use App\Support\Lang;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class EntityBase extends ModelBase
{
    /**
     * 实体字段值缓存
     *
     * @var array
     */
    protected static $fieldValuesCache = [];

    /**
     * 实体字段名缓存
     *
     * @var array
     */
    protected static $fieldKeysCache = [];

    /**
     * 实体标题列
     *
     * @var string
     */
    protected $titleColumn = 'title';

    /**
     * 获取实体类型类
     *
     * @return string
     */
    public static function getMoldClass()
    {
        return static::class.'Type';
    }

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getFieldClass()
    {
        return static::class.'Field';
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotClass()
    {
        $classname = basename(static::class);
        return __NAMESPACE__.'\\'.$classname.'Field'.$classname.'Type';
    }

    /**
     * 获取实体类型类
     *
     * @return string
     */
    public static function getMoldKeyName()
    {
        return 'mold_id';
    }

    /**
     * 获取实体名
     *
     * @return string
     */
    public static function getEntityName()
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * 获取实体标题列名称
     *
     * @return string
     */
    public function getTitleColumn()
    {
        return $this->titleColumn;
    }

    /**
     * 获取实体 id
     *
     * @return int|string
     */
    public function getEntityId()
    {
        return $this->getKey();
    }

    /**
     * 获取实体路径
     *
     * @return string
     */
    public function getEntityPath()
    {
        return static::getEntityName().'/'.$this->getEntityId();
    }

    public function getLangcode()
    {
        return $this->attributes['langcode'] ?? langcode('content');
    }

    /**
     * 获取实体标题
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->{$this->getTitleColumn()};
    }

    public function getUrlAttribute($url)
    {
        if ($langcode = langcode('output')) {
            return '/'.strtolower($langcode).$url;
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public static function template(EntityMoldBase $mold = null)
    {
        $template = parent::template();
        if ($mold) {
            $template = array_merge(
                $template,
                ['mold_id' => $mold->getKey()],
                $mold->getFieldValues()
            );
        }

        return $template;
    }

    /**
     * 按实体类型
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string $mold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfMold($query, $mold)
    {
        return $query->where(static::getMoldKeyName(), $mold);
    }

    /**
     * 缓存实体字段值
     *
     * @return void
     */
    public static function cacheFieldValues()
    {
        static::getFieldValues();
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

        // 字段值
        $fieldValues = static::getFieldValues($fields);

        // 获取所有消息数据，附带指定的字段值
        return static::all()->map(function (EntityBase $entity) use ($fieldValues) {
                $attributes = $entity->attributesToArray();
                $id = $entity->getKey();
                foreach ($fieldValues as $field => $values) {
                    $attributes[$field] = $values[$id] ?? null;
                }
                return $attributes;
            })->keyBy('id');
    }

    /**
     * 获取指定字段的值
     *
     * @param  array $fields 指定的字段列表
     * @return array
     */
    public static function getFieldValues(array $fields = ['*'])
    {
        // 获取具体的字段列表
        if (empty($fields) || $fields == ['*']) {
            $fields = static::getFieldKeys();
        }
        // 去除重复值
        $fields = array_unique($fields);

        // 获取当前缓存
        $cache = self::$fieldValuesCache[static::getEntityName()] ?? [];

        // 排除已缓存的字段
        if ($uncachedFields = array_diff($fields, array_keys($cache))) {
            // 将新获取的字段值列表添加到缓存
            foreach (static::getFieldClass()::findMany($uncachedFields) as $field) {
                $cache[$field->getKey()] = $field->getValueModel()->values();
            }
        }

        // 缓存结果
        self::$fieldValuesCache[static::getEntityName()] = $cache;

        return Arr::only($cache, $fields);
    }

    /**
     * 获取实体路径别名（网址）
     *
     * @return string|null
     */
    public function getPathAlias()
    {
        return EntityPathAlias::make()->getValue($this);
    }

    /**
     * 实体所属类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mold()
    {
        return $this->belongsTo(static::getMoldClass(), static::getMoldKeyName());
    }

    /**
     * 关联字段
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        $pivot = static::getPivotClass();
        $pivotTable = $pivot::getModelTable();
        return $this->belongsToMany(
                static::getFieldClass(),
                static::getPivotClass(),
                $pivot::getMoldKeyName(),
                $pivot::getFieldKeyName(),
                static::getMoldKeyName()
            )->withPivot([
                'label',
                'description',
                'field_meta',
                'delta',
            ])->orderBy($pivotTable.'.delta');
    }

    /**
     * 获取实体类型
     *
     * @return \App\Entity\EntityMoldBase|null
     */
    public function getMold()
    {
        if ($this->exists) {
            return $this->mold->translateTo($this->getLangcode());
        } elseif ($mold_id = $this->attributes[static::getMoldKeyName()] ?? null) {
            $mold = static::getMoldClass();
            return $mold::findOrFail($mold_id)->translateTo($this->getLangcode());
        }
        return null;
    }

    /**
     * 获取属性集，可指定属性名
     *
     * @param  array $keys 属性名列表
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        if ($attributes = $this->pocket()->get('attributes')) {
            $attributes = $attributes->value();
        } else {
            $attributes = array_merge(
                $this->attributesToArray(), $this->fieldsToArray()
            );
        }

        if ($keys && !in_array('*', $keys)) {
            $attributes = Arr::only($attributes, $keys);
        }
        return $attributes;
    }

    /**
     * 获取所有字段的 id
     *
     * @return array
     */
    public static function getFieldKeys()
    {
        if (static::$fieldKeysCache) {
            return static::$fieldKeysCache;
        }
        return static::$fieldKeysCache = static::getFieldClass()::query()->pluck('id')->all();
    }

    /**
     * 判断是否拥有指定字段
     *
     * @param  string|int $key
     * @return bool
     */
    public function isField($key)
    {
        return in_array($key, $this->getFieldKeys());
    }

    /**
     * 获取实体字段值
     *
     * @param  string|int $key 字段 id
     * @return mixed
     */
    public function getFieldValue($key)
    {
        $values = $this->getFieldValues([$key])[$key] ?? [];
        $valueKey = $this->getEntityId().'/'.$this->getLangcode();

        return $this->transformAttributeValue($key, $values[$valueKey] ?? null);
    }

    /**
     * 获取所有实体字段值
     *
     * @return array
     */
    public function fieldsToArray()
    {
        $fieldValues = static::getFieldValues();

        $key = $this->getEntityId().'/'.$this->getLangcode();
        $attributes = [];
        foreach ($fieldValues as $field => $values) {
            $attributes[$field] = $values[$key] ?? null;
        }

        return $this->transformAttributesArray($attributes);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if ($this->isField($key)) {
            return $this->getFieldValue($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * 更新实体字段
     *
     * @return $this
     */
    public function updateFields()
    {
        if ($this->raw) {
            DB::beginTransaction();

            $this->fields->each(function(FieldBase $field) {
                if (array_key_exists($id = $field->getKey(), $this->raw)) {
                    $field->bindEntity($this)->setValue($this->raw[$id]);
                }
            });

            DB::commit();
        }

        return $this;
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * 获取实体渲染结果
     *
     * @return string
     */
    public function fetchHtml()
    {
        if ($html = $this->pocket()->get('html')) {
            return $html->value();
        }

        if ($html = $this->render()) {
            return $this->pocket('html')->put($html);
        }

        return null;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array(static::class, $morphMap)) {
            return array_search(static::class, $morphMap, true);
        }

        return static::getEntityName();
    }

    /**
     * Retrieve the actual class name for a given morph class.
     *
     * @param  string  $class
     * @return string
     */
    public static function getActualClassNameForMorph($class)
    {
        if ($actualClass = Arr::get(Relation::morphMap() ?: [], $class, null)) {
            return $actualClass;
        }

        return EntityManager::resolve($class) ?? $class;
    }

    /**
     * 移除渲染结果
     *
     * @return void
     */
    public function clearRenderingResults()
    {
        $url = $this->url;
        if ($url && preg_match('/\.html?$/i', $url)) {
            Storage::disk('public')->delete($url);

            // 清除网址中可能存在的语言
            $path = explode('/', ltrim($url, '/'));
            if (lang($path[0])->isAvailable()) {
                array_shift($path);
            }
            $path = implode('/', $path);
            if ($path !== $url) {
                Storage::disk('public')->delete($path);
            }

            // 删除各语言版本的 html
            foreach (Lang::getLangcodes() as $langcode) {
                Storage::disk('public')->delete(strtolower($langcode).'/'.$path);
            }
        }
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function(EntityBase $entity) {
            $entity->clearRenderingResults();
            $entity->pocket()->clear('html', 'gather');
            $entity->fields->each(function (FieldBase $field) use($entity) {
                $field->bindEntity($entity)->deleteValue();
            });
        });

        static::saved(function(EntityBase $entity) {
            $entity->clearRenderingResults();
            $entity->pocket()->clear('html', 'gather');
            if ($entity->getRaw()) {
                $entity->updateFields();
                $entity->clearRaw();
            }
        });
    }

    /**
     * 获取实体字段
     *
     * @return \Illuminate\Support\Collection|\App\EntityField\FieldBase[]
     */
    public function getFields()
    {
        // 获取关联字段集合
        if ($this->exists) {
            $fields = $this->fields;
        } elseif ($mold = $this->getMold()) {
            $fields = $mold->fields;
        } else {
            $fields = static::getFieldClass()::isPreseted()->get();
        }

        // 字段表主键名
        $keyName = static::getFieldClass()::getModelKeyName();

        // 为字段绑定当前实体对象，排序等
        return $fields->map(function(FieldBase $field) {
                return $field->bindEntity($this);
            })->keyBy($keyName)->sortBy('delta');
    }

    public function getUrl($forceLangcode = false)
    {
        $url = '/'.ltrim($this->url, '/');

        if ($forceLangcode) {
            return '/'.strtolower(langcode('rendering') ?? $this->getLangcode()).$url;
        }

        if ($langcode = langcode('rendering')) {
            return '/'.strtolower($langcode).$url;
        }

        return $url;
    }
}
