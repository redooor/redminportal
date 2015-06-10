<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProductTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('order_product')) {
            Schema::create('order_product', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('order_id')->unsigned();
                $table->integer('product_id')->unsigned();
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
		Schema::dropIfExists('order_product');
	}

}
