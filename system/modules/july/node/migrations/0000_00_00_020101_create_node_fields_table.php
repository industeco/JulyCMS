<?php

use App\BaseMigrations\CreateFieldsTableBase;

class CreateNodeFieldsTable extends CreateFieldsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Node\NodeField::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Node\Seeds\NodeFieldSeeder::class;
}
