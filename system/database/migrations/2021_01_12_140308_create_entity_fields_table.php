<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_fields', function (Blueprint $table) {
            $table->id();

            // 字段 handle
            $table->string('handle');

            // 字段类型
            $table->string('field_type');

            // 字段所属实体
            $table->string('entity_name')->nullable();

            // 是否默认字段：新建实体类型时，默认字段将被自动添加到字段列表中
            $table->boolean('is_default')->default(false);

            // 取消预设类型，简化为全局字段
            $table->boolean('is_global')->default(false);

            // 全局字段所属分组
            $table->string('global_group')->nullable();

            // 是否可检索：可检索字段的内容会被 NodeIndex 索引
            $table->boolean('is_searchable')->default(true);

            // 搜索权重
            $table->unsignedDecimal('search_weight')->default(1.0);

            // 标签
            $table->string('label');

            // 描述
            $table->string('description')->nullable();

            // 时间戳
            $table->timestamps();

            // handle + entity_name 唯一（所属实体中不能出现同名的字段）
            $table->unique(['handle', 'entity_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_fields');
    }
}
