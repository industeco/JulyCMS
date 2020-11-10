<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 节点的目录结构
        Schema::create('catalogs', function (Blueprint $table) {
            // 目录 id
            $table->string('id', 32)->primary();

            // 是否必要目录：必要项不可删除
            $table->boolean('is_necessary')->default(0);

            // 标签
            $table->string('label', 64);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 节点与目录关联表
        Schema::create('catalog_node', function (Blueprint $table) {
            $table->id();

            // 目录 id
            $table->string('catalog_id', 32);

            // 节点 id
            $table->unsignedBigInteger('node_id');

            // 上级节点的 id
            $table->unsignedBigInteger('parent_id')->nullable();

            // 前一个节点的 id
            $table->unsignedBigInteger('prev_id')->nullable();

            // 路径
            $table->string('path')->default('/');
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
        Schema::dropIfExists('catalogs');
    }
}
