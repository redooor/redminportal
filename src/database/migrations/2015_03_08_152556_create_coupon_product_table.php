<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponProductTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('coupon_product')) {
            Schema::create('coupon_product', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('coupon_id')->unsigned();
                $table->integer('product_id')->unsigned();
                $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
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
		Schema::dropIfExists('coupon_product');
	}

}
