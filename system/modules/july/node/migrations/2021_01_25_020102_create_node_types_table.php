<?php

use App\BaseMigrations\CreateMoldsTableBase;

class CreateNodeTypesTable extends CreateMoldsTableBase
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model = \July\Node\NodeType::class;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = \July\Node\Seeds\NodeTypeSeeder::class;
}

// class CreateNodeTypesTable extends Migration
// {
//     /**
//      * Run the migrations.
//      *
//      * @return void
//      */
//     public function up()
//     {
//         Schema::create('node_types', function (Blueprint $table) {
//             $table->string('id')->primary();
//             $table->string('label');
//             $table->string('description')->nullable();
//             $table->string('langcode', 12);

//             // 是否预设：
//             //  - 不可删除
//             //  - 只能通过程序添加
//             $table->boolean('is_reserved')->default(false);

//             $table->timestamps();
//         });

//         $this->seed();
//     }

//     /**
//      * Reverse the migrations.
//      *
//      * @return void
//      */
//     public function down()
//     {
//         Schema::dropIfExists('node_types');
//     }

//     /**
//      * 填充数据
//      *
//      * @return void
//      */
//     protected function seed()
//     {
//         DB::beginTransaction();
//         NodeTypeSeeder::seed();
//         DB::commit();

//         NodeTypeSeeder::afterSeeding();
//     }
// }
