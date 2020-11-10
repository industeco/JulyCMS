<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeFieldNodeTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 内容字段与内容类型关联表
        Schema::create('node_field_node_type', function (Blueprint $table) {
            $table->id();

            // 类型 id
            $table->string('node_type_id', 32);

            // 字段 id
            $table->string('node_field_id', 32);

            // 序号
            $table->unsignedTinyInteger('delta')->default(0);

            // 搜索权重
            $table->unsignedDecimal('weight')->nullable();

            // 标签
            $table->string('label', 32)->nullable();

            // 描述
            $table->string('description', 255)->nullable();

            // 同一个字段在同一个类型中最多出现一次
            $table->unique(['node_type_id', 'node_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_field_node_type');
    }
}
