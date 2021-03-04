<?php

namespace App\MigrationBase;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldMoldTableBase extends MigrationBase
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->model::getModelTable(), function (Blueprint $table) {
            $table->id();

            // 类型 id
            $table->string('mold_id');

            // 字段 id
            $table->string('field_id');

            $table->string('label')->nullable();
            $table->string('description')->nullable();

            // 顺序号
            $table->unsignedSmallInteger('delta')->default(0);

            // 字段参数
            $table->binary('parameters')->nullable();

            // 字段 + 类型 唯一
            $table->unique(['mold_id', 'field_id']);
        });

        $this->seed();
    }
}
