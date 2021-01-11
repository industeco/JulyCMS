<?php

namespace Specs;

use App\Model;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Spec extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'specs';

    /**
     * 主键名
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 主键类型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 主键是否自增
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
        'id',
        'label',
        'description',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['fields'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields()
    {
        return $this->hasMany(SpecField::class)->orderBy('delta');
    }

    /**
     * 属性及默认值
     *
     * @return array
     */
    public static function defaultAttributes()
    {
        return [
            'id' => null,
            'label' => null,
            'description' => null,
        ];
    }

    /**
     * 批量更新/插入记录
     *
     * @param  array $records
     * @return array
     */
    public function upsertRecords(array $records)
    {
        $now = Carbon::now();
        $table = $this->getDataTable();
        $fields = $this->fields()->get('field_id')->pluck('field_id')->all();

        DB::beginTransaction();
        foreach ($records as $record) {
            $id = $record['id'] ?? null;
            $record = Arr::only($record, $fields);
            if ($id) {
                $record['updated_at'] = $now;
                DB::table($table)->updateOrInsert(['id' => $id], $record);
            } else {
                $record['created_at'] = $now;
                $record['updated_at'] = $now;
                DB::table($table)->insert($record);
            }
        }
        DB::commit();

        return DB::table($table)->where('updated_at', $now)->get()->map(function($record) {
            return (array) $record;
        })->all();
    }

    /**
     * 获取模板数据
     *
     * @return array
     */
    public function getRecordTemplate()
    {
        $template = [];
        foreach ($this->fields as $field) {
            $template = array_merge(
                $template,
                $field->getFieldType()->toRecords(null)
            );
        }

        return $template;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getFields()
    {
        return $this->fields->map(function(SpecField $field) {
            return $field->attributesToArray();
        })->keyBy('field_id');
    }

    /**
     * 获取规格数据
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRecords()
    {
        return DB::table($this->getDataTable())->get()
            ->map(function($record) {
                return (array) $record;
            });
    }

    /**
     * 查找指定 id 的规格记录
     *
     * @param  string|int $id 规格记录的 id
     * @return array|null
     */
    public function getRecord($id)
    {
        if ($record = DB::table($this->getDataTable())->where('id', $id)->first()) {
            return (array) $record;
        }
        return null;
    }

    /**
     * 搜索规格数据
     *
     * @param  string $keywords
     * @return array $result
     *
     * $result 结构：
     *  [
     *      'groups' => array
     *      'records' => array
     *  ]
     */
    public function search(string $keywords)
    {
        $fields = $this->getFields();

        $conditions = [];
        foreach ($fields as $field_id => $field) {
            if ($field['is_searchable'] ?? false) {
                $conditions[] = [
                    $field_id, 'like', '%'.$keywords.'%', 'or'
                ];
            }
        }

        $records = DB::table($this->getDataTable())
            ->where($conditions)
            ->get()
            ->map(function($record) {
                return (array) $record;
            });

        $groups = [];
        if (! $records->isEmpty()) {
            foreach ($fields as $field_id => $field) {
                if ($field['is_groupable']) {
                    $groups[$field_id] = $records->countBy($field_id)->all();
                }
            }
        }

        return [
            'groups' => $groups,
            'records' => $records->all(),
        ];
    }

    /**
     * 获取规格存储表表名
     *
     * @return string
     */
    public function getDataTable()
    {
        return 'spec_'.$this->getkey().'__data';
    }

    /**
     * 建立规格存储表
     *
     * @return void
     */
    public function tableUp()
    {
        // 获取表名，判断是否存在
        $tableName = $this->getDataTable();
        if (Schema::hasTable($tableName)) {
            $this->tableUpdate();
            return;
        }

        // 数据表列参数
        $columns = [];
        foreach (request('fields') ?: $this->getFields() as $field) {
            $columns = array_merge(
                $columns,
                FieldType::findOrFail($field['field_type_id'])->bind($field)->getColumns()
            );
        }

        // 创建数据表
        Schema::create($tableName, function (Blueprint $table) use ($columns) {
            $table->id();

            foreach($columns as $column) {
                $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
            }

            $table->timestamps();
        });
    }

    /**
     * 修改规格存储表
     *
     * @return void
     */
    public function tableUpdate()
    {
        // 获取表名，判断是否存在
        $tableName = $this->getDataTable();
        if (! Schema::hasTable($tableName)) {
            $this->tableUp();
            return;
        }

        $columns = [];
        foreach (request('fields') as $field) {
            if (!isset($field['id'])) {
                $columns = array_merge(
                    $columns,
                    FieldType::findOrFail($field['field_type_id'])->bind($field)->getColumns()
                );
            }
        }

        Schema::table($this->getDataTable(), function(Blueprint $table) use($columns) {
            foreach($columns as $column) {
                $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
            }
        });
    }

    /**
     * 移除规格存储表
     *
     * @return void
     */
    public function tableDown()
    {
        Schema::dropIfExists($this->getDataTable());
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function(Spec $spec) {
            $spec->fields()->delete();
            $spec->fields()->createMany(request('fields'));
            $spec->tableUp();
        });

        static::deleted(function(Spec $spec) {
            $spec->fields()->delete();
            $spec->tableDown();
        });
    }
}
