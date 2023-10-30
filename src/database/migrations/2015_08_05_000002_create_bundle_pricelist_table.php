<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBundlePricelistTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('bundle_pricelist')) {
            Schema::create('bundle_pricelist', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('bundle_id')->unsigned();
                $table->integer('pricelist_id')->unsigned();
                $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
                $table->foreign('pricelist_id')->references('id')->on('pricelists')->onDelete('cascade');
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
		Schema::dropIfExists('bundle_pricelist');
	}

}
