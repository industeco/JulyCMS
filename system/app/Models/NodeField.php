<?php

namespace App\Models;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Casts\Json;
use App\FieldTypes\FieldType;

class NodeField extends JulyModel
{
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
     * 不可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'field_type',
        'is_preset',
        'is_searchable',
        // 'langcode',
        'config',
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

    public static function fieldsAside()
    {
        return [
            'url','template','meta_title','meta_keywords','meta_description',
        ];
    }

    public static function retrieveFieldJigsawsAside($langcode = null)
    {
        $langcode = $langcode ?? langcode('admin_page');

        $lastModified = last_modified(view_path('components/'));

        $cacheKey = md5('fieldJigsawsAside/'.$langcode);
        $jigsawsAside = Cache::get($cacheKey);

        if (!$jigsawsAside || $jigsawsAside['created_at'] < $lastModified) {
            $jigsawsAside = [];
            foreach (NodeField::findMany(NodeField::fieldsAside()) as $field) {
                $jigsawsAside[$field->truename] = FieldType::getJigsaws($field->toArray());
            }
            $jigsawsAside = [
                'created_at' => time(),
                'jigsaws' => $jigsawsAside,
            ];
            Cache::put($cacheKey, $jigsawsAside);
        }

        return $jigsawsAside['jigsaws'];
    }

    public function tableName()
    {
        return 'node__' . $this->truename;
    }

    public function columns()
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
            $columns = $this->columns();
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
     * 保存前对请求数据进行预处理
     *
     * @param \Illuminate\Http\Request $request
     * @return Array
     */
    public static function prepareRequest(Request $request, NodeField $nodeField = null)
    {
        $config = FieldType::getConfig($request->all());
        if ($nodeField) {
            unset($config['langcode']);
            return [
                'config' => array_replace_recursive($this->config, $config),
            ];
        } else {
            return [
                'truename' => $request->input('truename'),
                'field_type' => $request->input('field_type'),
                'config' => $config,
            ];
        }
    }

    // public static function cacheKey($truename, $node_id, $langcode = null)
    // {
    //     $langcode = $langcode ?: langcode('content');
    //     return 'node_fields/'.$truename.'/'.$node_id.'/'.$langcode;
    // }

    /**
     * 删除字段值
     */
    public function deleteValue($node_id, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');

        // 清除字段值缓存
        $this->cacheClear($this->truename.'/'.$node_id, $langcode);

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
        $this->cacheClear($this->truename.'/'.$node_id, $langcode);

        $records = FieldType::getRecords($this->field_type, $value, $this->columns());
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
        $langcode = $langcode ?: langcode('content');

        $cacheid = $this->truename.'/'.$node->id;
        if ($value = $this->cacheGet($cacheid, $langcode)) {
            $value = $value['value'];
        } else {
            $value = null;
            $table = $this->tableName();
            $records = DB::select("SELECT * FROM `$table` WHERE `node_id`=? ORDER BY `delta`", [$node->id]);
            if ($records) {
                $records = collect($records)->groupBy('langcode');
                $records = $records->get($langcode) ?: $records->get($node->langcode);
                if ($records) {
                    $records = $records->map(function($record) {
                        return (array) $record;
                    })->toArray();

                    // 借助字段类型格式化数据库记录
                    $config = $this->config;
                    if ($this->pivot) {
                        $config = array_replace_recursive($config, $this->pivot->config);
                    }
                    $value = FieldType::getValue($this->field_type, $records, $this->columns(), $config);
                }
            }

            // 缓存字段值
            $this->cachePut($cacheid, $value, $langcode);
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
}
