<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function($table) {
                $table->increments('id');
                $table->string('name', 255)->unique();
                $table->string('short_description', 255);
                $table->text('long_description')->nullable();
                $table->boolean('active')->default(true);
                $table->text('options')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                // Need to use InnoDB to support foreign key
                $table->engine = 'InnoDB';
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
        Schema::dropIfExists('categories');
    }
}
