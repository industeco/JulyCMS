<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_translations', function (Blueprint $table) {
            $table->id();

            // 实体 id
            $table->unsignedBigInteger('entity_id');

            // 节点类型
            $table->string('mold_id');

            // 标题
            $table->string('title');

            // 视图文件
            $table->string('view')->nullable();

            // 属性三原色
            $table->boolean('is_red')->default(false);
            $table->boolean('is_green')->default(false);
            $table->boolean('is_blue')->default(false);

            // 语言版本
            $table->string('langcode', 12);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('node_translations');
    }
}
