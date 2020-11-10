<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 节点内容索引表
        Schema::create('node_index', function (Blueprint $table) {
            $table->id();

            // 节点 id
            $table->unsignedBigInteger('node_id');

            // 字段真名
            $table->string('field_id', 32);

            // 字段值
            $table->text('field_value');

            // 语言代码
            $table->string('langcode', 12);

            // 权重
            $table->unsignedFloat('weight')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_index');
    }
}
