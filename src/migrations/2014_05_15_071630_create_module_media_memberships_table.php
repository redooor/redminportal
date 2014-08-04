<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleMediaMembershipsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('module_media_memberships', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('module_id');
			$table->foreign('module_id')->references('id')->on('modules');
			$table->integer('media_id');
			$table->foreign('media_id')->references('id')->on('medias');
			$table->integer('membership_id');
			$table->foreign('membership_id')->references('id')->on('memberships');
			$table->unique(array('module_id', 'media_id', 'membership_id'));
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
		Schema::drop('module_media_memberships');
	}

}
