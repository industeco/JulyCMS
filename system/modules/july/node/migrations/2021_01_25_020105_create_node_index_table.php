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
            $table->unsignedBigInteger('entity_id');

            // 字段 id
            $table->string('field_id');

            // 被索引的内容
            $table->text('content');

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
