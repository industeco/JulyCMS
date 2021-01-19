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
            $table->string('node_type_id');

            // 字段 id
            $table->string('node_field_id');

            // 顺序号
            $table->unsignedSmallInteger('delta')->default(0);

            $table->string('label')->nullable();
            $table->string('description')->nullable();

            // 表单字段下方辅助文本
            $table->string('helpertext')->nullable();

            // 是否必填
            $table->boolean('is_required')->default(false);

            // 同一个字段在同一个类型中最多出现一次
            $table->unique(['node_type_id', 'node_field_id']);
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
