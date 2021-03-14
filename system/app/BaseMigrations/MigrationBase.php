<?php

namespace App\BaseMigrations;

use DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class MigrationBase extends Migration
{
    /**
     * 模型名
     *
     * @var string
     */
    protected $model;

    /**
     * 填充文件
     *
     * @var string|null
     */
    protected $seeder = null;

    /**
     * Run the migrations.
     *
     * @return void
     */
    abstract public function up();

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->getTable());
    }

    /**
     * 填充数据
     *
     * @return void
     */
    protected function seed()
    {
        if ($this->seeder) {
            DatabaseSeeder::register($this->seeder);
        }
    }

    /**
     * 获取本次迁移使用的表名，默认从绑定的模型获取
     *
     * @return string
     */
    public function getTable()
    {
        return $this->model::getModelTable();
    }
}
