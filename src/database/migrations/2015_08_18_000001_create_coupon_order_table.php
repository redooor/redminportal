<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponOrderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('coupon_order')) {
            Schema::create('coupon_order', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('coupon_id')->unsigned();
                $table->integer('order_id')->unsigned();
                $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
		Schema::dropIfExists('coupon_order');
	}

}
