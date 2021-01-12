<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_views', function (Blueprint $table) {
            $table->id();

            // 实体名
            $table->string('entity_name');

            // 实体 id
            $table->string('entity_id');

            // 语言版本
            $table->string('langcode', 12);

            // 路径别名
            $table->string('view');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_views');
    }
}
