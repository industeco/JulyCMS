<?php

namespace App\EntityField\FieldTypes;

use App\EntityField\EntityView;

class View extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'reserved.view';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '视图（模板）';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '视图（模板）文件名';

    /**
     * 字段值模型类
     *
     * @var string
     */
    protected $valueModel = \App\EntityField\EntityView::class;

    /**
     * 获取表单组件（element-ui component）
     *
     * @param  mixed $value 字段值
     * @return string
     */
    public function render($value = null)
    {
        $data = $this->field->gather();
        $data['value'] = $value;
        $data['helpertext'] = $data['helpertext'] ?: $data['description'];
        $data['rules'] = $this->getRules();

        $views = EntityView::query()->where('langcode', $this->field->getLangcode())->pluck('view');
        $data['views'] = array_values($views->sort()->unique()->all());

        return view('field_type.'.$this->id, $data)->render();
    }
}
