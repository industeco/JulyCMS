<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use July\Node\Seeds\NodeFieldNodeTypeSeeder;

class CreateNodeFieldNodeTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('node_field_node_type', function (Blueprint $table) {
            $table->id();

            // 类型 id
            $table->string('mold_id');

            // 字段 id
            $table->string('field_id');

            // 顺序号
            $table->unsignedSmallInteger('delta')->default(0);

            $table->string('label')->nullable();
            $table->string('description')->nullable();

            // 是否必填
            $table->boolean('is_required')->default(false);

            // 表单字段下方输入提示
            $table->string('helpertext')->nullable();

            // 默认值
            $table->string('default_value')->nullable();

            // 可选值
            $table->string('options')->nullable();

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
        Schema::dropIfExists('node_field_node_type');
    }

    /**
     * 填充数据
     *
     * @return void
     */
    protected function seed()
    {
        DB::beginTransaction();
        NodeFieldNodeTypeSeeder::seed();
        DB::commit();

        NodeFieldNodeTypeSeeder::afterSeeding();
    }
}
