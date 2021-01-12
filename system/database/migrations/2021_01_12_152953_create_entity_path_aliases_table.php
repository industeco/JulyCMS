<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityPathAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_path_aliases', function (Blueprint $table) {
            $table->id();

            // 实体名
            $table->string('entity_name');

            // 实体 id
            $table->string('entity_id');

            // 语言版本
            $table->string('langcode', 12);

            // 路径别名
            $table->string('alias')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_path_aliases');
    }
}
