<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Casts\Json;
use App\Contracts\HasModelConfig;
use App\FieldTypes\FieldType;
use App\Traits\CastModelConfig;
use Illuminate\Support\Arr;

class NodeField extends JulyModel implements HasModelConfig
{
    use CastModelConfig;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_fields';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'truename';

    /**
     * 主键“类型”。
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 指示模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'field_type',
        'is_preset',
        'is_searchable',
        'is_global',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'is_searchable' => 'boolean',
        'config' => Json::class,
    ];

    public static function primaryKeyName()
    {
        return 'truename';
    }

    public function types()
    {
        return $this->belongsToMany(NodeType::class, 'node_field_node_type', 'node_field', 'node_type')
                    ->using(NodeTypeNodeField::class)
                    ->orderBy('node_field_node_type.delta')
                    ->withPivot(
                        'delta',
                        'config'
                    );
    }

    public function configStructure(): array
    {
        return FieldType::getConfigStructrue($this->field_type);
    }

    public static function globalFields()
    {
        return [
            'template','url','meta_title','meta_keywords','meta_description','meta_canonical',
        ];
    }

    public static function retrieveGlobalFields($langcode = null)
    {
        $langcode = $langcode ?? langcode('admin_page');
        if (is_array($langcode)) {
            $langcode = $langcode['content_value'] ?? langcode('admin_page');
        }

        $cacheKey = md5('globalFields/'.$langcode);
        $fields = Cache::get($cacheKey);

        if (! $fields) {
            $fields = [];
            foreach (NodeField::findMany(NodeField::globalFields()) as $field) {
                $fields[$field->truename] = $field->mixConfig();
            }
            Cache::put($cacheKey, $fields);
        }

        return $fields;
    }

    public static function retrieveGlobalFieldJigsaws($langcode = null, array $values = null)
    {
        $langcode = $langcode ?? langcode('admin_page');
        if (is_array($langcode)) {
            $langcode = $langcode['content_value'] ?? langcode('admin_page');
        }

        $lastModified = last_modified(view_path('components/'));

        $cacheKey = md5('globalFieldJigsaws/'.$langcode);
        $jigsaws = Cache::get($cacheKey);

        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach (static::retrieveGlobalFields($langcode) as $field) {
                $jigsaws[$field['truename']] = FieldType::getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            Cache::put($cacheKey, $jigsaws);
        }

        $jigsaws = $jigsaws['jigsaws'];
        if ($values) {
            foreach ($jigsaws as $fieldName => &$jigsaw) {
                $jigsaw['value'] = $values[$fieldName] ?? null;
            }
        }

        return $jigsaws;
    }

    public function tableName()
    {
        return 'node__' . $this->truename;
    }

    public function tableColumns()
    {
        $columns = FieldType::getColumns($this->field_type, $this->config);
        if (count($columns) === 1) {
            $columns[0]['name'] = $columns[0]['name'] ?? $this->truename.'_value';
        }
        return $columns;
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
                $table->string('langcode', 12);
                $table->unsignedTinyInteger('delta')->default(0);

                foreach($columns as $column) {
                    $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
                }

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
        $langcode = $langcode ?: langcode('content_value');

        // 清除字段值缓存
        static::cacheClear($this->truename.'/'.$node_id, $langcode);

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

        $langcode = $langcode ?: langcode('content_value');
        // Log::info("langcode: '{$langcode}'");

        // 清除字段值缓存
        static::cacheClear($this->truename.'/'.$node_id, $langcode);

        $records = FieldType::getRecords($this->field_type, $value, $this->tableColumns());
        if (is_null($records)) {
            $this->deleteValue($node_id, $langcode);
        } else {
            foreach ($records as $index => &$record) {
                $record['node_id'] = $node_id;
                $record['langcode'] = $langcode;
                $record['delta'] = $index;
            }
            unset($record);

            // Log::info("Records:");
            // Log::info($records);

            $table = $this->tableName();
            // Log::info("table: '{$table}'");

            DB::delete("DELETE FROM `$table` WHERE `node_id`=? AND `langcode`=?", [$node_id, $langcode]);
            DB::table($table)->insert($records);

            // Log::info("Field '{$this->truename}' updated.");
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
        $langcode = $langcode ?: $node->langcode;

        $cacheid = $this->truename.'/'.$node->id;
        if ($value = static::cacheGet($cacheid, $langcode)) {
            $value = $value['value'];
        } else {
            $value = null;
            $records = DB::table($this->tableName())->where([
                ['node_id', $node->id],
                ['langcode', $langcode],
            ])->orderBy('delta')->get();

            if ($records->count()) {
                $records = $records->map(function($record) {
                    return (array) $record;
                })->all();

                // 借助字段类型格式化数据库记录
                $config = $this->config;
                if ($this->pivot) {
                    $config = array_replace_recursive($config, $this->pivot->config);
                }
                $value = FieldType::getValue($this->field_type, $records, $this->tableColumns(), $config);
            }

            // 缓存字段值
            static::cachePut($cacheid, $value, $langcode);
        }

        return $value;
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

    public function label($langcode = null)
    {
        $label = $this->config['label'] ?? [];

        $langcode = $langcode ?: langcode('admin_page');
        $original_lang = $this->config['langcode']['interface_value'];

        return $label[$langcode] ?? $label[$original_lang] ?? null;
    }
}
