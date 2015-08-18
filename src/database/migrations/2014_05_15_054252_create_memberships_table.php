<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('memberships')) {
            Schema::create('memberships', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('name', 255);
                $table->integer('rank')->default(0);
                $table->timestamps();
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
		Schema::dropIfExists('memberships');
	}

}
