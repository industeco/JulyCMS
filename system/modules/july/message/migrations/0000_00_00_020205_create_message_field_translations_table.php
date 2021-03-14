<?php

use App\BaseMigrations\CreateFieldTranslationsTableBase;

class CreateMessageFieldTranslationsTable extends CreateFieldTranslationsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Message\MessageFieldTranslation::class;
}
