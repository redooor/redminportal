<?php

use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('categories');
    }
}
