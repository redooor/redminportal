<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBundleOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('bundle_order')) {
            Schema::create('bundle_order', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('order_id')->unsigned();
                $table->integer('bundle_id')->unsigned();
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('bundle_id')->references('id')->on('bundles')->onDelete('cascade');
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
		Schema::dropIfExists('bundle_order');
	}

}
