<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntityReferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entity_references', function (Blueprint $table) {
            $table->id();

            // 实体名
            $table->string('entity_name');

            // 实体 id
            $table->string('entity_id');

            // 实体 id
            $table->string('field_id');

            // 被引用的实体名
            $table->string('reference_name');

            // 被引用的实体 id
            $table->string('reference_id');

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
        Schema::dropIfExists('entity_references');
    }
}
