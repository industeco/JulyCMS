<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use July\Message\Seeds\MessageFieldMessageFormSeeder;

class CreateMessageFieldMessageFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_field_message_form', function (Blueprint $table) {
            $table->id();

            // 类型 id
            $table->string('mold_id');

            // 字段 id
            $table->string('field_id');

            // 顺序号
            $table->unsignedSmallInteger('delta')->default(0);

            $table->string('label')->nullable();
            $table->string('description')->nullable();

            // 建议长度
            $table->unsignedSmallInteger('maxlength')->default(0);

            // 是否必填
            $table->boolean('is_required')->default(false);

            // 表单字段下方输入提示
            $table->string('helpertext')->nullable();

            // 默认值
            $table->string('default_value')->nullable();

            // 可选值
            $table->string('options')->nullable();

            // 验证规则
            $table->string('rules')->nullable();

            // 字段占位符
            $table->string('placeholder')->nullable();

            // 字段 + 类型 唯一
            $table->unique(['mold_id', 'field_id']);
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
        Schema::dropIfExists('message_field_message_form');
    }

    /**
     * 填充数据
     *
     * @return void
     */
    protected function seed()
    {
        DB::beginTransaction();
        MessageFieldMessageFormSeeder::seed();
        DB::commit();

        MessageFieldMessageFormSeeder::afterSeeding();
    }
}
