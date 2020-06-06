<?php

use App\Models\NodeField;
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
        // 配置表
        Schema::create('configs', function (Blueprint $table) {
            //
            $table->id();

            // 配置键
            $table->string('keyname', 100);

            // 配置分组
            $table->string('group', 50)->nullable();

            // 标签
            $table->string('label', 50);

            // 描述
            $table->string('description', 255)->nullable();

            // 用户
            $table->string('user', 50)->default('any');

            // 配置值
            $table->binary('data');

            // 时间戳
            $table->timestamps();
        });

        // 管理员账户表
        Schema::create('administrators', function (Blueprint $table) {
            $table->id();
            $table->string('truename', 50)->unique();
            $table->string('name', 20);
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // 字段参数表
        Schema::create('field_parameters', function (Blueprint $table) {
            // 字段，例：node_field.title 或 node_field.title.node_type.basic
            $table->string('keyname', 100);

            // 配置值
            $table->binary('data');

            // 语言
            $table->string('langcode', 12);
        });

        // 内容字段
        Schema::create('node_fields', function (Blueprint $table) {
            // 字段真名
            $table->string('truename', 50)->primary();

            // 字段类型，由 cms 定义
            $table->string('field_type', 50);

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 是否通用字段（会出现在表单右侧）
            $table->boolean('is_global')->default(0);

            // 是否可检索
            $table->boolean('is_searchable')->default(1);

            // 搜索权重
            $table->unsignedDecimal('weight')->default(1);

            // 分组（分组标签）
            $table->string('group', 50)->nullable();

            // 标签
            $table->string('label', 50);

            // 描述
            $table->string('description', 255)->nullable();

            // 语言
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();
        });

        // 内容类型
        Schema::create('node_types', function (Blueprint $table) {
            // 真名
            $table->string('truename', 50)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 标签
            $table->string('label', 50);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 内容字段与内容类型关联表
        Schema::create('node_field_node_type', function (Blueprint $table) {
            $table->id();

            // 类型真名
            $table->string('node_type', 50);

            // 字段真名
            $table->string('node_field', 50);

            // 序号
            $table->unsignedTinyInteger('delta')->default(0);

            // 搜索权重
            $table->unsignedDecimal('weight')->nullable();

            // 标签
            $table->string('label', 50)->nullable();

            // 描述
            $table->string('description', 255)->nullable();

            // 语言
            $table->string('langcode', 12);

            // 同一个字段在同一个类型中最多出现一次
            $table->unique(['node_type', 'node_field']);
        });

        // 内容表
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

        // 目录
        Schema::create('catalogs', function (Blueprint $table) {
            // 真名
            $table->string('truename', 50)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 标签
            $table->string('label', 50);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 内容与目录关联表
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

        // 标签
        Schema::create('tags', function (Blueprint $table) {
            // 标签名
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

        // 内容与标签关联表
        Schema::create('node_tag', function (Blueprint $table) {
            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 标签 id
            $table->string('tag', 50);

            // 语言代码
            $table->string('langcode', 12);
        });

        // 内容值索引表
        Schema::create('node_index', function (Blueprint $table) {
            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 字段真名
            $table->string('node_field', 50);

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
        Schema::dropIfExists('node_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('catalog_node');
        Schema::dropIfExists('catalogs');
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('node_field_node_type');
        Schema::dropIfExists('node_types');

        foreach (NodeField::all() as $field) {
            $field->tableDown();
        }

        Schema::dropIfExists('node_fields');
        Schema::dropIfExists('field_parameters');
        Schema::dropIfExists('administrators');
        Schema::dropIfExists('configs');
    }
}
