<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodeTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_types', function (Blueprint $table) {
            // 真名
            $table->string('id', 32)->primary();

            // 是否必要类型：必要类型不可删除
            $table->boolean('is_necessary')->default(0);

            // 标签
            $table->string('label', 32);

            // 描述
            $table->string('description', 255)->nullable();

            // 语言
            $table->string('langcode', 12);

            // 时间戳
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
        Schema::dropIfExists('node_types');
    }
}
