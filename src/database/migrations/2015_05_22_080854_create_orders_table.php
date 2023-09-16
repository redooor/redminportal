<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
                $table->decimal('paid', 8, 2)->default(0);
                $table->string('transaction_id')->default('Unknown')->nullable();
                $table->string('payment_status')->default('Completed')->nullable();
                $table->text('options')->nullable();
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
		Schema::dropIfExists('orders');
	}

}
