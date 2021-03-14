<?php

use App\BaseMigrations\CreateFieldTranslationsTableBase;

class CreateNodeFieldTranslationsTable extends CreateFieldTranslationsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Node\NodeFieldTranslation::class;
}
