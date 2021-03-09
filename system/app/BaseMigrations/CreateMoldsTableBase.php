<?php

namespace App\BaseMigrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoldsTableBase extends MigrationBase
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->getTable(), function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('label');
            $table->string('description')->nullable();

            // 是否预设 —— 预设字段不可删除，只能通过程序添加
            $table->boolean('is_reserved')->default(false);

            $table->string('langcode', 12);

            $table->timestamps();
        });

        $this->seed();
    }
}
