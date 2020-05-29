<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJulycmsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 实体配置
        Schema::create('configs', function (Blueprint $table) {
            // 配置键
            $table->string('keyname', 100)->primary();

            // 名称（用作字段标签）
            $table->string('name', 100);

            // 描述
            $table->string('description', 255)->nullable();

            // 配置值
            $table->binary('data');
        });

        // 内容字段
        Schema::create('node_fields', function (Blueprint $table) {
            // 字段真名
            $table->string('truename', 50)->primary();

            // 字段类型，由 cms 定义
            $table->string('field_type', 50);

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 是否可检索
            $table->boolean('is_searchable')->default(1);

            // 名称
            $table->string('name', 100);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 内容类型
        Schema::create('node_types', function (Blueprint $table) {
            // 真名
            $table->string('truename', 50)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 名称
            $table->string('name', 100);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        Schema::create('node_field_node_type', function (Blueprint $table) {
            $table->id();

            // 类型真名
            $table->string('node_type', 50);

            // 字段真名
            $table->string('node_field', 50);

            // 序号
            $table->unsignedTinyInteger('delta')->default(0);

            // 名称
            $table->string('name', 100)->nullable();

            // 描述
            $table->string('description', 255)->nullable();

            // 同一个字段在同一个类型中最多出现一次
            $table->unique(['node_type', 'node_field']);
        });

        // 目录
        Schema::create('catalogs', function (Blueprint $table) {
            // 真名
            $table->string('truename', 50)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 名称
            $table->string('name', 100);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳zh-Hans
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->string('tag', 50)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 是否在页面上显示
            $table->boolean('is_show')->default(1);

            // 标签原文（表示翻译自该标签）
            $table->string('original_tag', 50);

            // 语言
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();

            $table->unique(['original_tag', 'langcode']);
        });

        Schema::create('nodes', function (Blueprint $table) {
            $table->id();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 节点类型
            $table->string('node_type', 50);

            // 源语言（创建时的语言）
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();
        });

        // 目录与内容关联表
        Schema::create('catalog_node', function (Blueprint $table) {
            // 目录真名
            $table->string('catalog', 50);

            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 父级内容 id
            $table->unsignedBigInteger('parent_id')->nullable();

            // 相邻内容 id
            $table->unsignedBigInteger('prev_id')->nullable();

            // 路径
            $table->string('path')->default('/');
        });

        // 标签与内容关联表
        Schema::create('node_tag', function (Blueprint $table) {
            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 标签 id
            $table->string('tag', 32);

            // 语言代码
            $table->string('langcode', 12);
        });

        // 内容与内容关联表（引用表）
        Schema::create('node_node', function (Blueprint $table) {
            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 关联的内容 id
            $table->unsignedBigInteger('related_id');

            // 字段 id
            $table->unsignedBigInteger('field_id');

            // 语言代码
            $table->string('langcode', 12);
        });

        // 索引表
        Schema::create('indexes', function (Blueprint $table) {
            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 字段真名
            $table->string('node_field', 32);

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
        Schema::dropIfExists('julycms_tables');
    }
}
