<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundleProductTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('bundle_product')) {
            Schema::create('bundle_product', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('bundle_id')->unsigned();
                $table->integer('product_id')->unsigned();
                $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
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
		Schema::dropIfExists('bundle_product');
	}

}
