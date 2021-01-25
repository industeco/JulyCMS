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

            // 字段/实体类型所绑定实体的实体名
            $table->string('entity_name');

            // 字段 id
            $table->string('field_id');

            // 实体类型 id
            $table->string('mold_id')->nullable();

            // 语言版本
            $table->string('langcode', 12);

            // 占位字符
            $table->string('placeholder')->nullable();

            // 默认值
            $table->string('default_value')->nullable();

            // 可选值
            $table->string('options')->nullable();

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
