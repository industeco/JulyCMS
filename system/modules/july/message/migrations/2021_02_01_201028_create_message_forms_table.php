<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use July\Message\Seeds\MessageFormSeeder;

class CreateMessageFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_forms', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('langcode', 12);

            // 默认主题
            $table->string('subject')->nullable();

            // 是否预设：
            //  - 不可删除
            //  - 只能通过程序添加
            $table->boolean('is_reserved')->default(false);

            $table->timestamps();
        });

        $this->seed();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_forms');
    }

    /**
     * 填充数据
     *
     * @return void
     */
    protected function seed()
    {
        DB::beginTransaction();
        MessageFormSeeder::seed();
        DB::commit();

        MessageFormSeeder::afterSeeding();
    }
}
