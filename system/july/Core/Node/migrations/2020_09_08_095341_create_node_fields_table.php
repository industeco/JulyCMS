<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use July\Core\Node\NodeField;

class CreateNodeFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 内容字段
        Schema::create('node_fields', function (Blueprint $table) {
            // 字段 id
            $table->string('id', 32)->primary();

            // 字段类型 id
            $table->string('field_type_id', 32);

            // 存储该字段的值的实体名
            $table->string('storage_name', 32)->nullable();

            // 是否必要字段：必要字段是系统正常运行所需，不可删除
            $table->boolean('is_necessary')->default(0);

            // 是否可检索：可检索字段的内容会被 NodeIndex 索引
            $table->boolean('is_searchable')->default(1);

            // 搜索权重
            $table->unsignedDecimal('weight')->default(1);

            /**
             * 预设类型：
             *  0：非预设字段，新建内容类型时需手动选择
             *  1：预设字段，新建类型时会自动出现在字段列表的顶部，无需选择，也不能取消
             *  2：全局预设字段，新建类型时不会出现，新建内容时会自动出现在表单右侧
             */
            $table->unsignedTinyInteger('preset_type')->default(0);

            // 全局预设字段所属分组
            $table->string('global_group', 32)->nullable();

            // 显示顺序（新增时不方便处理）
            // $table->unsignedInteger('delta')->default(0);

            // 标签
            $table->string('label', 32);

            // 描述
            $table->string('description', 255)->nullable();

            // 字段的默认语言
            //  1. 字段需要默认语言吗？字段总是依附于实体而存在，不需要默认语言
            //  2. -
            // $table->string('langcode', 12);

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
        foreach (NodeField::all() as $field) {
            $field->getStorage()->tableDown();
        }

        Schema::dropIfExists('node_fields');
    }
}
