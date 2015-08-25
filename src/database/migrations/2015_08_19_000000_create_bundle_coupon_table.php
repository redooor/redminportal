<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundleCouponTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('bundle_coupon')) {
            Schema::create('bundle_coupon', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('coupon_id')->unsigned();
                $table->integer('bundle_id')->unsigned();
                $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
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
		Schema::dropIfExists('bundle_coupon');
	}

}
