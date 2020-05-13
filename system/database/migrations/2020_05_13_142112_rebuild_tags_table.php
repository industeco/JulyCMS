<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RebuildTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tags');
        Schema::create('tags', function (Blueprint $table) {
            $table->string('tag', 32);

            // 是否预设
            $table->boolean('is_preset')->default(0);

            // 是否在页面上显示
            $table->boolean('is_show')->default(1);

            // 标签原文（表示翻译自该标签）
            $table->string('original', 32);

            // 语言
            $table->string('langcode', 12);

            // 时间戳
            $table->timestamps();

            $table->primary('tag');
            $table->unique(['original', 'langcode']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
