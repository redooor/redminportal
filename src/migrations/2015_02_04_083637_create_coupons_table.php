<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('coupons', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('code');
            $table->text('description')->nullable();
            $table->decimal('amount', 8, 2);
            $table->boolean('is_percent')->default(false);
			$table->dateTime('start_date')->nullable();
            $table->dateTime('end_date');
            $table->timestamps();
            /* Usage limitation */
            $table->integer('usage_limit_per_coupon')->unsigned()->nullable();
            $table->integer('usage_limit_per_user')->unsigned()->nullable();
            $table->decimal('max_spent', 8, 2)->nullable();
            $table->decimal('min_spent', 8, 2)->nullable();
            $table->boolean('multiple_coupons')->default(false);
            $table->boolean('exclude_sale_item')->default(false);
            $table->integer('usage_limit_per_coupon_count')->unsigned()->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('coupons');
	}

}
