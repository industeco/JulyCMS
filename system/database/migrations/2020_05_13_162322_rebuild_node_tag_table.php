<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RebuildNodeTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('node_tag');
        Schema::create('node_tag', function (Blueprint $table) {
            // 内容 id
            $table->unsignedBigInteger('node_id');

            // 标签 id
            $table->string('tag', 32);

            // 语言代码
            $table->string('langcode', 12);
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
