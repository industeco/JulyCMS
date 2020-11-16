<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartialViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('partial_view', function (Blueprint $table) {
        //     $table->id();

        //     // 实体路径
        //     $table->string('path', 255);

        //     // 视图
        //     $table->string('view', 255);

        //     // 默认语言
        //     $table->string('langcode', 12);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('partial_view');
    }
}
