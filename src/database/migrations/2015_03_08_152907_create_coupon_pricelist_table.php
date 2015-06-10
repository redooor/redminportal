<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponPricelistTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('coupon_pricelist')) {
            Schema::create('coupon_pricelist', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('coupon_id')->unsigned();
                $table->integer('pricelist_id')->unsigned();
                $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
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
		Schema::dropIfExists('coupon_pricelist');
	}

}
