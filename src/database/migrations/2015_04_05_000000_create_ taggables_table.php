<?php

use Illuminate\Database\Migrations\Migration;

class CreateTaggablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('taggables')) {
            Schema::create('taggables', function($table) {
                $table->integer('tag_id')->unsigned();
                $table->integer('taggable_id')->unsigned();
                $table->string('taggable_type');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taggables');
    }
}
