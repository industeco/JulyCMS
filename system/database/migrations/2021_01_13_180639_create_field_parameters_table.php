<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('field_parameters', function (Blueprint $table) {
            $table->id();

            // 字段 id
            $table->string('field_id');

            // 实体名
            $table->string('entity_name');

            // 实体类型名
            $table->string('entity_mold_name')->nullable();

            // 语言
            $table->string('langcode', 12);

            // 字段配置
            $table->binary('parameters');

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
        Schema::dropIfExists('field_parameters');
    }
}
