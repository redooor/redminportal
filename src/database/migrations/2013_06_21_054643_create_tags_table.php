<?php

use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tags', function($table) {
             // auto incremental id (PK)
            $table->increments('id');
             // name limit to 64 characters
            $table->string('name', 64);
             // Tagable
            $table->integer('tagable_id');
            $table->string('tagable_type', 32);
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
		Schema::drop('tags');
	}

}