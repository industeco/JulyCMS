<?php

namespace App\BaseMigrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFieldsTableBase extends MigrationBase
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            // 字段 id
            $table->string('id')->primary();

            // 字段类型（直接保存类全名可好？）
            $table->string('field_type');

            $table->string('label');
            $table->string('description')->nullable();

            // 是否预设：预设字段由开发人员维护，用户不可删除
            $table->boolean('is_reserved')->default(false);

            // 是否全局字段：全局字段会在新建模型时默认出现在全局字段列表
            $table->boolean('is_global')->default(false);

            // 字段分组
            $table->string('field_group')->nullable();

            // 搜索权重
            $table->unsignedTinyInteger('weight')->default(0);

            // 字段元数据
            $table->binary('field_meta')->nullable();

            // 初始语言版本
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();
        });

        // 填充数据
        $this->seed();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->model::all() as $field) {
            $field->tableDown();
        }

        Schema::dropIfExists($this->getTable());
    }
}
