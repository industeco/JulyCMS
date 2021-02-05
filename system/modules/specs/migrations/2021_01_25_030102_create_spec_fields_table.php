<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spec_fields', function (Blueprint $table) {
            $table->id();

            // 字段 id
            $table->string('field_id', 32);

            // 规格 id
            $table->string('spec_id', 32);

            // 字段标签
            $table->string('label', 32);

            // 字段描述
            $table->string('description', 255)->nullable();

            // 字段类型（规格字段的特有类型）
            $table->string('field_type_id', 32);

            // 默认值
            $table->string('default')->nullable();

            // 可选项
            $table->string('options')->nullable();

            // 固定小数位
            $table->unsignedTinyInteger('places')->nullable();

            // 唯一性
            $table->boolean('is_unique')->default(false);

            // 可分组
            $table->boolean('is_groupable')->default(false);

            // 可搜索
            $table->boolean('is_searchable')->default(true);

            // 已删除
            $table->boolean('is_deleted')->default(false);

            // 字段次序
            $table->unsignedMediumInteger('delta')->default(0);

            $table->timestamps();

            $table->unique(['field_id', 'spec_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spec_fields');
    }
}
