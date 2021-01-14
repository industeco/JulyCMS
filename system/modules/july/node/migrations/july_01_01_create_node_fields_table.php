<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use July\Node\NodeField;

class CreateNodeFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_fields', function (Blueprint $table) {
            // 字段 id
            $table->string('id')->primary();

            // 字段类型
            $table->string('field_type');

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

            // 最大长度（仅对部分类型有效）
            $table->unsignedSmallInteger('maxlength')->nullable();

            $table->string('label');
            $table->string('description')->nullable();

            // 表单字段下方辅助文本
            $table->string('helpertext')->nullable();

            // 是否必填
            $table->boolean('is_required')->default(false);

            // 初始语言版本
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (NodeField::all() as $field) {
            $field->tableDown();
        }

        Schema::dropIfExists('node_fields');
    }
}
