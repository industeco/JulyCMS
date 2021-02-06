<?php

namespace App\EntityField\FieldTypes;

class Html extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'html';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = 'HTML';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '适用于 HTML 内容';

    /**
     * 转为适合索引的内容
     *
     * @param  string $value 字段内容
     * @return string
     */
    public function toIndex($value)
    {
        $value = preg_replace('/\s+/', ' ', $value);

        $blocks = [
            'div','p','h1','h2','h3','h4','h5','h6',
            'li','dt','dd','caption','th','td',
            'section','nav','header','article','aside','footer','menuitem','address',
            'br','hr',
        ];

        $value = preg_replace('/<('.implode('|', $blocks).')(?=\\s|>)/i', '; <$1', $value);
        $value = strip_tags($value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = preg_replace('/[\s;]+;/', ';', $value);
        $value = preg_replace('/([.,;?!]);\s/', '$1 ', $value);

        return trim($value, ' ;');
    }
}
