<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
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
        //     // 配置 id 键
        //     $table->string('id', 128)->primary();

        //     // 分组
        //     $table->string('group', 64)->nullable();

        //     // 标签
        //     $table->string('label', 32)->nullable();

        //     // 描述
        //     $table->string('description', 255)->nullable();

        //     // 是否只读
        //     // $table->boolean('is_readonly')->default(false);

        //     // 是否可翻译
        //     // $table->boolean('is_translatable')->default(false);

        //     // 默认语言
        //     $table->string('langcode', 12)->default('zxx');
        // });

        // // 配置值表
        // Schema::create('config__value', function (Blueprint $table) {
        //     $table->id();

        //     // 配置 id
        //     $table->string('config_id', 128);

        //     // 语言
        //     $table->string('langcode', 12)->default('zxx');

        //     // 配置值
        //     $table->binary('value');
        // });

        // // 用户偏好设置表
        // Schema::create('user_preferences', function (Blueprint $table) {
        //     $table->id();

        //     // 配置 id
        //     $table->string('config_id', 128);

        //     // 用户 id
        //     $table->unsignedBigInteger('user_id');

        //     // 配置值
        //     $table->binary('value');
        // });

        // Schema::create('path_alias', function (Blueprint $table) {
        //     $table->id();

        //     // 实体路径
        //     $table->string('path', 255);

        //     // 路径别名
        //     $table->string('alias', 255)->unique();

        //     // 默认语言
        //     $table->string('langcode', 12);
        // });

        // Schema::create('partial_view', function (Blueprint $table) {
        //     $table->id();

        //     // 实体路径
        //     $table->string('path', 255);

        //     // 视图
        //     $table->string('view', 255);

        //     // 默认语言
        //     $table->string('langcode', 12);
        // });

        // Schema::create('entity_configs', function (Blueprint $table) {
        //     $table->id();

        //     // 实体路径
        //     $table->string('path', 255);

        //     // 默认语言
        //     $table->string('langcode', 12)->default('zxx');

        //     // 配置值
        //     $table->binary('config');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('entity_configs');
        // Schema::dropIfExists('partial_view');
        // Schema::dropIfExists('path_alias');
        // Schema::dropIfExists('user_preferences');
        // Schema::dropIfExists('config__value');
        // Schema::dropIfExists('configs');
    }
}
