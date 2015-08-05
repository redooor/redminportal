<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
                $table->string('title', 255);
                $table->string('slug', 255);
                $table->text('content');
                $table->boolean('private')->default(TRUE);
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
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
		Schema::dropIfExists('pages');
	}

}
