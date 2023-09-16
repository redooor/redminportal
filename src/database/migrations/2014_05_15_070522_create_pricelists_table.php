<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePricelistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('pricelists')) {
            Schema::create('pricelists', function(Blueprint $table)
            {
                $table->increments('id');
                $table->decimal('price', 8, 2)->default(0);
                $table->integer('module_id')->unsigned();
                $table->foreign('module_id')->references('id')->on('modules');
                $table->integer('membership_id')->unsigned();
                $table->foreign('membership_id')->references('id')->on('memberships');
                $table->unique(array('module_id', 'membership_id'));
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
		Schema::dropIfExists('pricelists');
	}

}
