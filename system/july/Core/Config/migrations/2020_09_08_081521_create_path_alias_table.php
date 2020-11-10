<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePathAliasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('path_alias', function (Blueprint $table) {
        //     $table->id();

        //     // 实体路径
        //     $table->string('path', 255);

        //     // 路径别名
        //     $table->string('alias', 255)->unique();

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
        // Schema::dropIfExists('path_alias');
    }
}
