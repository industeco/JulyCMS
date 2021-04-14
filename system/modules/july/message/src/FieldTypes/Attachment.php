<?php

namespace July\Message\FieldTypes;

use App\EntityField\FieldTypes\FieldTypeBase;
use App\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class Attachment extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'attachment';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '附件';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '上传附件';

    /**
     * 字段值类型转换器
     *
     * @var string|\Closure
     */
    protected $caster = 'json';

    /**
     * 判断类型在指定范围是否可用
     *
     * @param  string $scope 使用范围
     * @return bool
     */
    public static function available(string $scope)
    {
        return $scope === 'message';
    }

    /**
     * 格式化值用于数据库保存
     *
     * @param  mixed $value
     * @return mixed
     */
    public function formatRecordValue($value)
    {
        if ($value instanceof UploadedFile) {
            return json_encode([
                'name' => $value->getClientOriginalName(),
                'type' => $value->getClientMimeType(),
                'size' => $value->getSize(),
            ]);
        }

        return (string) $value;
    }
}
