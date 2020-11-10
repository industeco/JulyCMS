<?php

use App\ContentEntity\Models\ContentField;
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
        // // 配置表
        // Schema::create('configs', function (Blueprint $table) {
        //     // 配置键
        //     $table->string('id', 128)->primary();

        //     // 配置分组
        //     $table->string('group', 128)->default('default');

        //     // 标签
        //     $table->string('label', 32)->nullable();

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 是否可见：是否在后台展示该选项
        //     $table->boolean('is_visible')->default(true);

        //     // 是否可变更
        //     $table->boolean('is_changable')->default(true);

        //     // 是否可翻译
        //     $table->boolean('is_translatable')->default(false);

        //     // 默认语言
        //     $table->string('langcode', 12)->nullable();
        // });

        // // 配置表
        // Schema::create('config__value', function (Blueprint $table) {
        //     // 配置键
        //     $table->string('config_id', 128);

        //     // 标签
        //     $table->string('langcode', 12)->nullable();

        //     // 配置值
        //     $table->binary('value');
        // });

        // // 用户表
        // Schema::create('users', function (Blueprint $table) {
        //     $table->id();

        //     // 用户名
        //     $table->string('name', 32)->unique();

        //     // 密码
        //     $table->string('password');

        //     // 角色：superadmin, admin, editor, user
        //     $table->string('role', 32)->default('user');

        //     // 登录令牌，防止重复登录
        //     $table->string('login_token')->nullable();

        //     // remember_token
        //     $table->rememberToken();

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 偏好设置表
        // Schema::create('user__preferences', function (Blueprint $table) {
        //     // 用户 id
        //     $table->unsignedBigInteger('user_id');

        //     // 配置键
        //     $table->string('config_id', 128);

        //     // 配置值
        //     $table->binary('preferences');
        // });

        // // 内容字段
        // Schema::create('node_fields', function (Blueprint $table) {
        //     // 字段真名
        //     $table->string('id', 32)->primary();

        //     // 字段类型的实体 id
        //     $table->string('field_type', 32);

        //     // 是否必要字段：必要字段不可删除
        //     $table->boolean('is_necessary')->default(0);

        //     // 是否预设字段：预设字段会默认出现在 type 中
        //     $table->boolean('is_preset')->default(0);

        //     // 是否全局字段：全局字段是所有类型通用的，但不出现在具体类型中
        //     $table->boolean('is_global')->default(0);

        //     // 是否可检索
        //     $table->boolean('is_searchable')->default(1);

        //     // 搜索权重
        //     $table->unsignedDecimal('weight')->default(1);

        //     // 分组（分组标签）
        //     $table->string('group', 32)->nullable();

        //     // 标签
        //     $table->string('label', 32);

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 语言
        //     $table->string('langcode', 12);

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 内容类型
        // Schema::create('node_types', function (Blueprint $table) {
        //     // 真名
        //     $table->string('id', 32)->primary();

        //     // 是否必要类型：必要类型不可删除
        //     $table->boolean('is_necessary')->default(0);

        //     // 标签
        //     $table->string('label', 32);

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 语言
        //     $table->string('langcode', 12);

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 内容字段与内容类型关联表
        // Schema::create('node_type_node_field', function (Blueprint $table) {
        //     // 类型真名
        //     $table->string('node_type_id', 32);

        //     // 字段真名
        //     $table->string('node_field_id', 32);

        //     // 序号
        //     $table->unsignedTinyInteger('delta')->default(0);

        //     // 搜索权重
        //     $table->unsignedDecimal('weight')->nullable();

        //     // 标签
        //     $table->string('label', 32)->nullable();

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 同一个字段在同一个类型中最多出现一次
        //     $table->unique(['node_type_id', 'node_field_id']);
        // });

        // // 内容字段参数表
        // Schema::create('node_field__parameters', function (Blueprint $table) {
        //     // 字段真名
        //     $table->string('node_field_id', 32);

        //     // 类型真名
        //     $table->string('node_type_id', 32)->nullable();

        //     // 语言
        //     $table->string('langcode', 12);

        //     // 字段配置
        //     $table->binary('parameters');
        // });

        // // 内容表
        // Schema::create('nodes', function (Blueprint $table) {
        //     $table->id();

        //     // 节点类型
        //     $table->string('node_type_id', 32);

        //     // 源语言（创建时的语言）
        //     $table->string('langcode', 12);

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 目录
        // Schema::create('catalogs', function (Blueprint $table) {
        //     // id
        //     $table->string('id', 32)->primary();

        //     // 是否必要目录：必要项不可删除
        //     $table->boolean('is_necessary')->default(0);

        //     // 标签
        //     $table->string('label', 32);

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 内容与目录关联表
        // Schema::create('catalog_node', function (Blueprint $table) {
        //     // 目录真名
        //     $table->string('catalog_id', 32);

        //     // 内容 id
        //     $table->unsignedBigInteger('node_id');

        //     // 父级内容 id
        //     $table->unsignedBigInteger('parent_id')->nullable();

        //     // 相邻内容 id
        //     $table->unsignedBigInteger('prev_id')->nullable();

        //     // 路径
        //     $table->string('path')->default('/');
        // });

        // // 内容字段
        // Schema::create('term_fields', function (Blueprint $table) {
        //     // 字段真名
        //     $table->string('id', 32)->primary();

        //     // 字段类型的实体 id
        //     $table->string('field_type', 32);

        //     // 是否必要字段：必要字段不可删除
        //     $table->boolean('is_necessary')->default(0);

        //     // 是否预设字段：预设字段会默认出现在 type 中
        //     $table->boolean('is_preset')->default(0);

        //     // 是否全局字段：全局字段是所有类型通用的，但不出现在具体类型中
        //     $table->boolean('is_global')->default(0);

        //     // 是否可检索
        //     $table->boolean('is_searchable')->default(1);

        //     // 搜索权重
        //     $table->unsignedDecimal('weight')->default(1);

        //     // 分组（分组标签）
        //     $table->string('group', 32)->nullable();

        //     // 标签
        //     $table->string('label', 32);

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 语言
        //     $table->string('langcode', 12);

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 词汇表
        // Schema::create('vocabularies', function (Blueprint $table) {
        //     // 词汇表 id （机读名）
        //     $table->string('id', 32)->primary();

        //     // 是否必要：必要项不可删除
        //     $table->boolean('is_necessary')->default(0);

        //     // 标签
        //     $table->string('label', 32);

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 语言
        //     $table->string('langcode', 12);

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 内容字段与内容类型关联表
        // Schema::create('vocabulary_term_field', function (Blueprint $table) {
        //     // 类型真名
        //     $table->string('vocabulary_id', 32);

        //     // 字段真名
        //     $table->string('term_field_id', 32);

        //     // 序号
        //     $table->unsignedTinyInteger('delta')->default(0);

        //     // 搜索权重
        //     $table->unsignedDecimal('weight')->nullable();

        //     // 标签
        //     $table->string('label', 32)->nullable();

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 字段在同一类型中只能出现一次
        //     $table->unique(['vocabulary_id', 'term_field_id']);
        // });

        // // 内容字段参数表
        // Schema::create('term_field__parameters', function (Blueprint $table) {
        //     // 字段真名
        //     $table->string('term_field_id', 32);

        //     // 词汇表
        //     $table->string('vocabulary_id', 32)->nullable();

        //     // 语言
        //     $table->string('langcode', 12);

        //     // 字段配置
        //     $table->binary('parameters');
        // });

        // // 词汇
        // Schema::create('terms', function (Blueprint $table) {
        //     $table->id();

        //     // 词汇表
        //     $table->string('vocabulary_id', 32);

        //     // 源语言（创建时的语言）
        //     $table->string('langcode', 12);

        //     // 时间戳
        //     $table->timestamps();
        // });

        // // 节点与标签关联表
        // Schema::create('node_term', function (Blueprint $table) {
        //     // 节点 id
        //     $table->unsignedBigInteger('node_id');

        //     // 标签 id
        //     $table->string('term_id', 32);
        // });

        // // 节点内容索引表
        // Schema::create('node_index', function (Blueprint $table) {
        //     // 节点 id
        //     $table->unsignedBigInteger('node_id');

        //     // 字段真名
        //     $table->string('node_field_id', 32);

        //     // 字段值
        //     $table->text('content');

        //     // 语言代码
        //     $table->string('langcode', 12);

        //     // 权重
        //     $table->unsignedFloat('weight')->default(1);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('node_index');
        // Schema::dropIfExists('node_tag');
        // Schema::dropIfExists('tags');
        // Schema::dropIfExists('catalog_node');
        // Schema::dropIfExists('catalogs');
        // Schema::dropIfExists('nodes');
        // Schema::dropIfExists('node_field__parameterss');
        // Schema::dropIfExists('node_field_node_type');
        // Schema::dropIfExists('node_types');

        // foreach (ContentField::all() as $field) {
        //     $field->tableDown();
        // }

        // Schema::dropIfExists('node_fields');
        // Schema::dropIfExists('user__preferencess');
        // Schema::dropIfExists('users');
        // Schema::dropIfExists('config__data');
        // Schema::dropIfExists('configs');
    }
}
