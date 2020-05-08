<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Casts\Json;
use Illuminate\Support\Arr;

class Config extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'july_configs';

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
        'is_preset',
        'value_type',
        'config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'config' => Json::class,
    ];

    public static function get($key, $default = null)
    {
        $item = static::find($key);
        if ($item) {
            return $item->getValue() ?? $default;
        }
        return $default;
    }

    public function mixConfig($clang = null)
    {
        $item = $this->getAttributes();
        unset($item['config']);

        $interface_values = $this->config['interface_values'] ?? [];

        $item['label'] = $interface_values['description'] ?? '';
        $item['description'] = $interface_values['description'] ?? '';
        $item['value'] = $this->getValue($clang);

        return $item;
    }

    public function getValue($clang = null)
    {
        if ($value = $this->config['value'] ?? null) {
            $lang = null;
            if (is_null($this->value_type)) {
                $lang = 0;
            } elseif ($this->value_type == 'interface_value') {
                $lang = config('request_langcode') ?: langcode('interface_value');
            } elseif ($this->value_type == 'content') {
                $lang = $clang ?: (config('content_value_langcode') ?: langcode('content_value'));
            }

            return $value[$lang] ?? null;
        }
        return null;
    }
}
