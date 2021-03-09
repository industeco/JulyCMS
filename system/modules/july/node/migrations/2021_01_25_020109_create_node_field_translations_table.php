<?php

use App\BaseMigrations\CreateFieldTranslationsTableBase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeFieldTranslationsTable extends CreateFieldTranslationsTableBase
{
    /**
     * 获取本次迁移使用的表名
     *
     * @return string
     */
    public function getTable()
    {
        return 'node_field_translations';
    }
}
