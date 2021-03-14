<?php

use App\BaseMigrations\CreateMoldsTableBase;

class CreateMessageFormsTable extends CreateMoldsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Message\MessageForm::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Message\Seeds\MessageFormSeeder::class;
}
