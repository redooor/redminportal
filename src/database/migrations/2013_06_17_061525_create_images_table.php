<?php

use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('images', function($table) {
             // auto incremental id (PK)
            $table->increments('id');
             // path limit to 320 characters
            $table->string('path', 320);
             // Imageable
            $table->integer('imageable_id');
            $table->string('imageable_type', 255);
             // created_at | updated_at DATETIME
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
		Schema::drop('images');
	}

}
