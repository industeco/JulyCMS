<?php

use App\BaseMigrations\CreateMoldsTableBase;

class CreateNodeTypesTable extends CreateMoldsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Node\NodeType::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Node\Seeds\NodeTypeSeeder::class;
}
