<?php

namespace Specs;

use App\Model;
use Illuminate\Database\Schema\Blueprint;
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
     * 获取模板数据
     *
     * @return array
     */
    public function getTemplate()
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
     * 获取规格数据
     *
     * @return array
     */
    public function getRecords()
    {
        return DB::table($this->getDataTable())
            ->orderByDesc('created_at')
            ->get()
            ->map(function($record) {
                return (array) $record;
            })
            ->all();
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
            return;
        }

        // 数据表列参数
        $columns = [];
        foreach (request('fields') ?: $this->fields as $field) {
            if ($field instanceof SpecField) {
                $field = $field->attributesToArray();
            }
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
     * 移除规格存储表
     *
     * @return void
     */
    public function tableDown()
    {
        Schema::dropIfExists($this->getDataTable());
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
            if (!($field['id'] ?? null)) {
                $columns[] = array_merge(
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
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function(Spec $spec) {
            $spec->fields()->delete();
            $spec->fields()->createMany(request('fields'));
            $spec->tableUpdate();
        });

        static::deleted(function(Spec $spec) {
            $spec->fields()->delete();
            $spec->tableDown();
        });
    }
}
