<?php

use App\BaseMigrations\CreateFieldMoldPivotTableBase;

class CreateMessageFieldMessageFormTable extends CreateFieldMoldPivotTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Message\MessageFieldMessageForm::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Message\Seeds\MessageFieldMessageFormSeeder::class;
}
