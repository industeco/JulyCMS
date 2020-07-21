<?php

use App\Models\ContentField;
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
        Schema::create('config_table', function (Blueprint $table) {
            // 配置键
            $table->string('keyname', 128)->primary();

            // 配置分组
            $table->string('group', 128)->nullable();

            // 标签
            $table->string('label', 64);

            // 描述
            $table->string('description', 255)->nullable();

            // 配置值
            $table->binary('data');
        });

        // 用户表
        Schema::create('user_table', function (Blueprint $table) {
            $table->id();

            // 用户名
            $table->string('name', 32)->unique();

            // 密码
            $table->string('password');

            // 角色：supperadmin, admin, editor, user
            $table->string('role', 32)->default('user');

            // 登录令牌，防止重复登录
            $table->string('login_token')->nullable();

            // remember_token
            $table->rememberToken();

            // 时间戳
            $table->timestamps();
        });

        // 偏好设置表
        Schema::create('user_preference_table', function (Blueprint $table) {
            // 用户 id
            $table->unsignedBigInteger('user_id');

            // 配置键
            $table->string('config_keyname', 128);

            // 配置值
            $table->binary('data');
        });

        // 字段参数表
        Schema::create('field_parameters_table', function (Blueprint $table) {
            // 参数所属字段的真名
            $table->string('field_truename', 32);

            // 参数所属类型的真名
            $table->string('mold_truename', 32)->nullable();

            // 参数所属实体的 id
            $table->string('entity_id', 32)->nullable();

            // 语言代码
            $table->string('langcode', 12)->nullable();

            // 参数值
            $table->binary('parameters');
        });

        // 内容字段
        Schema::create('node_field_table', function (Blueprint $table) {
            // 字段真名
            $table->string('truename', 32)->primary();

            // 字段类型别名
            $table->string('field_type', 32);

            // 保留字段，不可删除
            $table->boolean('is_reserved')->default(0);

            // 是否预设字段；预设字段会出现在
            $table->boolean('is_preset')->default(0);

            // 是否全局字段（全局字段是所有类型通用的，但不出现在具体类型中）
            $table->boolean('is_global')->default(0);

            // 是否可检索
            $table->boolean('is_searchable')->default(1);

            // 搜索权重
            $table->unsignedDecimal('weight')->default(1);

            // 分组（分组标签）
            $table->string('group', 32)->nullable();

            // 标签
            $table->string('label', 64);

            // 描述
            $table->string('description', 255)->nullable();

            // 语言
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();
        });

        // 内容类型
        Schema::create('node_mold_table', function (Blueprint $table) {
            // 真名
            $table->string('truename', 32)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 标签
            $table->string('label', 64);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 内容字段与内容类型关联表
        Schema::create('node_mold_node_field_table', function (Blueprint $table) {
            // 类型真名
            $table->string('node_mold', 32);

            // 字段真名
            $table->string('node_field', 32);

            // 序号
            $table->unsignedTinyInteger('delta')->default(0);

            // 搜索权重
            $table->unsignedDecimal('weight')->nullable();

            // 标签
            $table->string('label', 64)->nullable();

            // 描述
            $table->string('description', 255)->nullable();

            // 同一个字段在同一个类型中最多出现一次
            $table->unique(['node_mold', 'node_field']);
        });

        // 内容表
        Schema::create('node_table', function (Blueprint $table) {
            $table->id();

            // 节点类型
            $table->string('node_mold', 32);

            // 源语言（创建时的语言）
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();
        });

        // 目录
        Schema::create('catalog_table', function (Blueprint $table) {
            // 真名
            $table->string('truename', 32)->primary();

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 标签
            $table->string('label', 64);

            // 描述
            $table->string('description', 255)->nullable();

            // 时间戳
            $table->timestamps();
        });

        // 内容与目录关联表
        Schema::create('catalog_node_table', function (Blueprint $table) {
            // 目录真名
            $table->string('catalog', 32);

            // 内容 id
            $table->unsignedBigInteger('id');

            // 父级内容 id
            $table->unsignedBigInteger('parent_id')->nullable();

            // 相邻内容 id
            $table->unsignedBigInteger('prev_id')->nullable();

            // 路径
            $table->string('path')->default('/');
        });

        // 标签
        Schema::create('tag_table', function (Blueprint $table) {
            // 标签名
            $table->string('tag', 64)->primary();

            // 是否在页面上显示
            $table->boolean('is_show')->default(1);

            // 标签原文（表示翻译自该标签）
            $table->string('original_tag', 64);

            // 语言
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();

            $table->unique(['original_tag', 'langcode']);
        });

        // 节点与标签关联表
        Schema::create('node_tag_table', function (Blueprint $table) {
            // 节点 id
            $table->unsignedBigInteger('node_id');

            // 标签 id
            $table->string('tag', 50);

            // 语言代码
            $table->string('langcode', 12);
        });

        // 节点内容索引表
        Schema::create('node_index_table', function (Blueprint $table) {
            // 节点 id
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
        Schema::dropIfExists('node_index_table');
        Schema::dropIfExists('node_tag_table');
        Schema::dropIfExists('tag_table');
        Schema::dropIfExists('catalog_node_table');
        Schema::dropIfExists('catalog_table');
        Schema::dropIfExists('node_table');
        Schema::dropIfExists('node_type_node_field_table');
        Schema::dropIfExists('node_type_table');

        foreach (NodeField::all() as $field) {
            $field->tableDown();
        }

        Schema::dropIfExists('node_field_table');
        Schema::dropIfExists('field_parameters_table');
        Schema::dropIfExists('user_preferences_table');
        Schema::dropIfExists('user_table');
        Schema::dropIfExists('config_table');
    }
}
