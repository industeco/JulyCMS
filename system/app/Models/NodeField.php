<?php

namespace App\Models;

use App\FieldTypes\FieldType;
use App\Support\Arr;
use App\Traits\TruenameAsPrimaryKey;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NodeField extends JulyModel
{
    use TruenameAsPrimaryKey;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_fields';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'field_type',
        'is_preset',
        'is_global',
        'is_searchable',
        'weight',
        'group',
        'label',
        'description',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'is_global' => 'boolean',
        'is_searchable' => 'boolean',
        'weight' => 'float',
    ];

    public function types()
    {
        return $this->belongsToMany(NodeType::class, 'node_field_node_type', 'node_field', 'node_type')
                    ->orderBy('node_field_node_type.delta')
                    ->withPivot([
                        'delta',
                        'weight',
                        'label',
                        'description',
                    ]);
    }

    /**
     * 获取字段类型对象
     *
     * @param string|null $langcode
     * @return \App\FieldTypes\FieldTypeInterface
     */
    public function fieldType($langcode = null)
    {
        return FieldType::make($this->getAttribute('field_type'), $this, $langcode);
    }

    public static function usedByNodeTypes()
    {
        $types = [];
        $records = DB::select('SELECT `node_field`, count(`node_field`) as `total` FROM `node_field_node_type` GROUP BY `node_field`');
        foreach ($records as $record) {
            $types[$record->node_field] = $record->total;
        }

        return $types;
    }

    /**
     * 获取所有全局字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function globalFields()
    {
        return static::where('is_global', true)->get();
    }

    /**
     * 从缓存获取所有全局字段
     *
     * @return array
     */
    public static function cacheGetGlobalFields($langcode = null)
    {
        $langcode = $langcode ?? langcode('content');

        $model = new static;
        $cacheKey = $model->cacheKey('globalFields', compact('langcode'));

        if ($fields = $model->cacheGet($cacheKey)) {
            return $fields['value'];
        }

        $fields = static::globalFields()->map(function($field) use($langcode) {
            return $field->gather($langcode);
        })->keyBy('truename')->all();

        $model->cachePut($cacheKey, $fields);

        return $fields;
    }

    /**
     * 获取所有非全局字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function optionalFields()
    {
        return static::where('is_global', false)->get();
    }

    /**
     * 获取所有非全局预设字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function optionalPresetFields()
    {
        return static::where([
            'is_preset' => true,
            'is_global' => false,
        ])->get();
    }

    /**
     * 获取字段参数
     *
     * @param string|null $langcode
     * @return array
     */
    public function parameters($langcode = null)
    {
        $original_lang = $this->getAttribute('langcode');
        $langcode = $langcode ?? $original_lang;

        $keyname = implode('.', ['node_field', $this->getKey()]);
        $records = FieldParameters::where('keyname', 'like', $keyname.'.%')->get()->pluck('data', 'keyname');

        $parameters = $records[$keyname.'.'.$langcode] ?? $records[$keyname.'.'.$original_lang];
        if ($pivot = $this->pivot) {
            $keyname = implode('.', [$keyname, 'node_type', $pivot->node_type]);
            $parameters = array_merge(
                    $parameters,
                    $records[$keyname.'.'.$langcode] ?? $records[$keyname.'.'.$original_lang] ?? []
                );
        }

        return $parameters;
    }

    public function tableName()
    {
        return 'node__' . $this->truename;
    }

    public function tableColumns()
    {
        return $this->fieldType()->getColumns();
    }

    /**
     * 建立字段对应的数据表，用于存储字段值
     *
     * @return void
     */
    public function tableUp()
    {
        // 数据表表名
        $tableName = $this->tableName();

        if (! Schema::hasTable($tableName)) {

            // Log::info('TableUp: ' . $tableName);

            // 获取用于创建数据表列的参数
            $columns = $this->tableColumns();
            // Log::info($columns);

            // 创建数据表
            Schema::create($tableName, function (Blueprint $table) use ($columns) {
                $table->id();
                $table->unsignedBigInteger('node_id');

                foreach($columns as $column) {
                    $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
                }

                $table->unsignedTinyInteger('delta')->default(0);
                $table->string('langcode', 12);
                $table->timestamps();

                $table->unique(['node_id', 'langcode', 'delta']);
            });
        }
    }

    /**
     * 删除字段对应数据表
     *
     * @return void
     */
    public function tableDown()
    {
        Schema::dropIfExists($this->tableName());
    }

    /**
     * 删除字段值
     */
    public function deleteValue($node_id, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');

        // 清除字段值缓存
        $this->cacheClear($this->cacheKey('values', compact('node_id', 'langcode')));

        $table = $this->tableName();
        DB::delete("DELETE FROM `$table` WHERE `node_id`=? AND `langcode`=?", [$node_id, $langcode]);

        return true;
    }

    /**
     * 设置字段值
     *
     * @param int $node_id
     * @param mixed $value
     * @return void
     */
    public function setValue($value, $node_id, $langcode = null)
    {
        // Log::info("Updating field '{$this->truename}'");

        $langcode = $langcode ?: langcode('content');
        // Log::info("langcode: '{$langcode}'");

        // 清除字段值缓存
        $this->cacheClear($this->cacheKey('values', compact('node_id', 'langcode')));

        $records = $this->fieldType()->toRecords($value);
        if (is_null($records)) {
            $this->deleteValue($node_id, $langcode);
        } else {
            foreach ($records as $index => &$record) {
                $record['node_id'] = $node_id;
                $record['langcode'] = $langcode;
                $record['delta'] = $index;
            }
            unset($record);

            $table = $this->tableName();
            // Log::info("table: '{$table}'");

            DB::beginTransaction();
            DB::delete("DELETE FROM `$table` WHERE `node_id`=? AND `langcode`=?", [$node_id, $langcode]);
            foreach ($records as $record) {
                DB::table($table)->insert($record);
            }
            DB::commit();
        }
    }

    /**
     * 获取字段值
     *
     * @param \App\Models\Node $node
     * @param string $langcode 语言代码
     * @return mixed
     */
    public function getValue(Node $node, $langcode = null)
    {
        $langcode = $langcode ?: $node->getAttribute('langcode');

        $cacheKey = $this->cacheKey('values', [
            'node_id' => $node->getKey(),
            'langcode' => $langcode,
        ]);

        if ($value = $this->cacheGet($cacheKey)) {
            return $value['value'];
        }

        $value = null;
        $records = DB::table($this->tableName())->where([
            ['node_id', $node->getKey()],
            ['langcode', $langcode],
        ])->orderBy('delta')->get();

        if ($records->count()) {
            $records = $records->map(function($record) {
                return (array) $record;
            })->all();

            // 借助字段类型格式化数据库记录
            $value = $this->fieldType()->toValue($records);
        }

        // 缓存字段值
        $this->cachePut($cacheKey, $value);

        return $value;
    }

    public static function cacheGetGlobalFieldJigsaws($langcode = null)
    {
        $langcode = $langcode ?? langcode('content');

        $model = new static;
        $cacheKey = $model->cacheKey('globalFieldJigsaws', compact('langcode'));

        if ($jigsaws = $model->cacheGet($cacheKey)) {
            $jigsaws = $jigsaws['value'];
        }

        $lastModified = last_modified(background_path('template/components/'));
        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach (static::cacheGetGlobalFields($langcode) as $field) {
                $jigsaws[$field['truename']] = FieldType::make($field['field_type'])->getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            $model->cachePut($cacheKey, $jigsaws);
        }

        return $jigsaws['jigsaws'];
    }

    public static function boot()
    {
        parent::boot();

        static::created(function(NodeField $field) {
            $field->tableUp();
        });

        static::deleted(function(NodeField $field) {
            $field->tableDown();
        });
    }

    public function search($keywords, $langcode = null)
    {
        $keywords = '%'.$keywords.'%';
        $table = $this->tableName();

        $conditions = [];
        foreach ($this->tableColumns() as $column) {
            $conditions[] = [$column['name'], 'like', $keywords, 'or'];
        }

        if ($langcode) {
            $records = DB::table($table)->where('langcode', $langcode)->where($conditions)->get();
        } else {
            $records = DB::table($table)->where($conditions)->get();
        }

        $fieldName = $this->truename;
        $fieldType = $this->field_type;
        $fieldLabel = $this->label();
        $results = [];
        foreach ($records as $record) {
            $id = $record->node_id;
            $lang = $record->langcode;
            $key = $id.'/'.$fieldName.'/'.$lang;
            if (! isset($results[$key])) {
                $results[$key] = [
                    'node_id' => $id,
                    'node_field' => $fieldName,
                    'field_type' => $fieldType,
                    'field_label' => $fieldLabel,
                    'langcode' => $lang,
                ];
            }
        }

        return array_values($results);
    }

    public function records($langcode = null)
    {
        $table = $this->tableName();
        if ($langcode) {
            return DB::table($table)->where('langcode', $langcode)->get();
        } else {
            return DB::table($table)->get();
        }
    }

    /**
     * 收集字段所有相关信息并组成数组
     *
     * @param string|null $langcode
     * @return array
     */
    public function gather($langcode = null)
    {
        $data = $this->attributesToArray();
        if ($pivot = $this->pivot) {
            $data['label'] = $pivot->label ?? $data['label'];
            $data['description'] = $pivot->description ?? $data['description'];
            $data['delta'] = $pivot->delta;
        }
        $data['parameters'] = $this->parameters($langcode);

        return $data;
    }

    public function label()
    {
        return $this->attributes['label'];
    }
}
