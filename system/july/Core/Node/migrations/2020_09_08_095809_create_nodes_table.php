<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 内容表
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();

            // 节点类型
            $table->string('node_type_id', 32);

            // 源语言（创建时的语言）
            $table->string('langcode', 12);

            // 属性三原色
            $table->boolean('is_red')->default(false);
            $table->boolean('is_green')->default(false);
            $table->boolean('is_blue')->default(false);

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
        Schema::dropIfExists('nodes');
    }
}
