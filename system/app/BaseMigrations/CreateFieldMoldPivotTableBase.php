<?php

namespace App\BaseMigrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldMoldPivotTableBase extends MigrationBase
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->id();

            // 类型 id
            $table->string('mold_id');

            // 字段 id
            $table->string('field_id');

            $table->string('label')->nullable();
            $table->string('description')->nullable();

            // 顺序号
            $table->unsignedSmallInteger('delta')->default(0);

            // 字段元数据
            $table->binary('field_meta')->nullable();

            // 字段 + 类型 唯一
            $table->unique(['mold_id', 'field_id']);
        });

        $this->seed();
    }
}
