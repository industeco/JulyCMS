<?php

namespace App\BaseMigrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFieldTranslationsTableBase extends MigrationBase
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();

            // 字段 id
            $table->string('field_id');

            // 实体类型 id
            $table->string('mold_id')->nullable();

            // 语言版本
            $table->string('langcode', 12);

            // 字段参数
            $table->binary('field_meta')->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 填充数据
        $this->seed();
    }
}
