<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricelistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pricelists', function(Blueprint $table)
		{
			$table->increments('id');
			$table->float('price')->default(0);
			$table->integer('module_id');
			$table->foreign('module_id')->references('id')->on('modules');
			$table->integer('membership_id');
			$table->foreign('membership_id')->references('id')->on('memberships');
			$table->unique(array('module_id', 'membership_id'));
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
		Schema::drop('pricelists');
	}

}
