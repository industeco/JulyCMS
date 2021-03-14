<?php

use App\BaseMigrations\CreateFieldMoldPivotTableBase;

class CreateNodeFieldNodeTypeTable extends CreateFieldMoldPivotTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Node\NodeFieldNodeType::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Node\Seeds\NodeFieldNodeTypeSeeder::class;
}
