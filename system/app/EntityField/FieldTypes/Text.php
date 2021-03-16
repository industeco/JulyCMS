<?php

namespace App\EntityField\FieldTypes;

use Illuminate\Support\Facades\Log;

class Text extends FieldTypeBase
{
    /**
     * 类型标志，由小写字符+数字+下划线组成
     *
     * @var string
     */
    protected $handle = 'text';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '多行文字';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '适用于多行无格式内容';

    /**
     * 指定创建或修改字段时可见的参数项
     *
     * @return array
     */
    public function getMetaKeys()
    {
        return ['maxlength','rules'];
    }
}
