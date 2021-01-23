<?php

namespace App\Concerns;

use App\Utils\Pocket;
use Illuminate\Support\Facades\Schema;

trait HasColumnsTrait
{
    /**
     * （数据库表的）列名登记处
     *
     * @var array
     */
    protected static $columns = [];

    /**
     * 获取所有列名
     *
     * @return array
     */
    public static function getColumns()
    {
        // 检查内存 $columns
        if (isset(self::$columns[static::class])) {
            return self::$columns[static::class];
        }

        // 检查缓存
        $pocket = new Pocket(static::class, 'columns');
        if ($columns = $pocket->get()) {
            return self::$columns[static::class] = $columns->value();
        }

        // 生成
        $columns = Schema::getColumnListing((new static)->getTable());

        // 保存到内存 $columns
        self::$columns[static::class] = $columns;

        // 缓存
        $pocket->put($columns);

        return $columns;
    }

    /**
     * 判断列是否存在
     *
     * @param  string $column
     * @return bool
     */
    public static function hasColumn(string $column)
    {
        return in_array($column, static::getColumns());
    }

    /**
     * 获取列值
     *
     * @param  string $column
     * @return mixed
     */
    public function getColumnValue(string $column)
    {
        return $this->transformModelValue($column, $this->attributes[$column] ?? null);
    }

    /**
     * 获取所有列值
     *
     * @return array
     */
    public function columnsToArray()
    {
        $attributes = [];
        foreach (static::getColumns() as $column) {
            $attributes[$column] = $this->attributes[$column] ?? null;
        }

        return $this->transformAttributesArray($attributes);
    }
}
