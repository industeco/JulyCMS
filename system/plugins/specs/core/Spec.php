<?php

namespace Specs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        return $this->hasMany(SpecField::class)->orderBy('delta');
    }

    /**
     * 获取规格存储表表名
     *
     * @return string
     */
    public function getDataTable()
    {
        return 'spec_'.$this->attributes['id'].'__data';
    }

    /**
     * 获取数据表列参数
     *
     * @return array
     */
    public function getDataTableColumns()
    {
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

        return $columns;
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
        $columns = $this->getDataTableColumns();

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
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::created(function(Spec $spec) {
            $spec->tableUp();
        });

        static::deleted(function(Spec $spec) {
            $spec->tableDown();
        });
    }
}
