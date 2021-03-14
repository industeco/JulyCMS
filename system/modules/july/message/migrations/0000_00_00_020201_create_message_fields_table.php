<?php

use App\BaseMigrations\CreateFieldsTableBase;

class CreateMessageFieldsTable extends CreateFieldsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Message\MessageField::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Message\Seeds\MessageFieldSeeder::class;
}
