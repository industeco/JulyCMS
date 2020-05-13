<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTagsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('tags_data');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        // Schema::create('tags_data', function (Blueprint $table) {
        //     // 标签 id
        //     $table->unsignedBigInteger('tag_id');

        //     // 语言代码
        //     $table->string('langcode', 12);

        //     // 标签文字
        //     $table->string('name', 50);

        //     // 时间戳
        //     $table->timestamps();
        // });
    }
}
