<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('modules', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 255);
			$table->string('sku', 255)->unique();
			$table->string('short_description', 255);
			$table->text('long_description')->nullable();
			$table->boolean('featured')->default(false);
			$table->boolean('active')->default(true);
			$table->text('options')->nullable();
			$table->integer('category_id')->nullable()->unsigned();
			$table->foreign('category_id')->references('id')->on('categories');
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
		Schema::drop('modules');
	}

}
