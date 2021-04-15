<?php

namespace July\Message;

use App\Entity\EntityMoldBase;
use Illuminate\Support\Facades\Log;
use July\Message\FieldTypes\MultipleAttachment;

class MessageForm extends EntityMoldBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'message_forms';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'label',
        'description',
        'langcode',
        'subject',
        'is_reserved',
    ];

    /**
     * 获取实体类
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return Message::class;
    }

    /**
     * 获取默认表单
     *
     * @return \July\Message\MessageForm|static
     */
    public static function default()
    {
        if (app()->has('message_form.default')) {
            return app('message_form.default');
        }
        app()->instance('message_form.default', $form = static::findOrFail('new_message'));
        return $form;
    }

    /**
     * 生成表单
     *
     * @param  array $values
     * @return string
     */
    public function render($values = [])
    {
        /** @var \Twig\Environment */
        $twig = app('twig');

        // 默认模板
        $view = 'message/form/'.$this->getKey().'.twig';

        // 数据
        $data = $this->gather();
        $data['action'] = short_url('messages.send', $this->getKey());

        $data['fields'] = gather($this->fields)->keyBy('id')->all();
        foreach ($data['fields'] as $key => &$field) {
            $field['value'] = $values[$key] ?? null;
        }

        return $twig->render($view, $data);
    }

    /**
     * 解析字段验证规则和定制的错误信息
     *
     * @return array
     */
    public function resolveFieldRules()
    {
        $rules = [];
        $messages = [];
        $fields = [];

        foreach ($this->fields as $field) {
            $id = $field->getKey();
            [$fieldRules, $fieldMessages] = array_values($field->resolveRules());
            $rules = array_merge($rules, $fieldRules);
            $messages = array_merge($messages, $fieldMessages);
            $fields[$id] = $field->label;
        }

        return [$rules, $messages, $fields];
    }
}
