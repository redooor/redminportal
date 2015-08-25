<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
                $table->string('title', 255);
                $table->string('slug', 255);
                $table->text('content');
                $table->boolean('featured')->default(TRUE);
                $table->boolean('private')->default(TRUE);
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                // Need to use InnoDB to support foreign key and indexing
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
		Schema::dropIfExists('posts');
	}

}
