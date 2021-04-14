<?php

namespace July\Message\FieldTypes;

use App\EntityField\FieldTypes\FieldTypeBase;
use App\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class MultipleAttachment extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'multiple_attachment';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '多附件';

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
     * 获取验证规则（用于前端 js 验证）
     *
     * @param  array|null $meta 字段元数据
     * @return \App\Support\Validation\RuleGroup
     */
    public function getRules(?array $meta = null)
    {
        $rules = parent::getRules($meta);

        return $rules->setKey($this->field->getKey().'.*');
    }

    /**
     * 格式化值用于数据库保存
     *
     * @param  mixed $value
     * @return mixed
     */
    public function formatRecordValue($value)
    {
        $attachments = [];
        foreach (Arr::wrap($value) as $file) {
            if ($file instanceof UploadedFile) {
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        return json_encode($attachments);
    }
}
