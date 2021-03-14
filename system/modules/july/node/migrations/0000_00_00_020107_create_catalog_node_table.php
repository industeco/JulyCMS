<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalog_node', function (Blueprint $table) {
            $table->id();

            // 目录 id
            $table->string('catalog_id');

            // 节点 id
            $table->unsignedBigInteger('node_id');

            // 上级节点的 id
            $table->unsignedBigInteger('parent_id')->nullable();

            // 前一个节点的 id
            $table->unsignedBigInteger('prev_id')->nullable();

            // 路径
            $table->string('path')->default('/');

            // 每个节点在一个目录中最多出现一次
            $table->unique(['catalog_id', 'node_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalog_node');
    }
}
