<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('revisions')) {
            Schema::create('revisions', function (Blueprint $table) {
                $table->increments('id');
                $table->morphs('revisionable');
                $table->integer('user_id')->nullable();
                $table->string('attribute');
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
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
        if (Schema::hasTable('revisions')) {
            Schema::drop('revisions');
        }
    }
}
