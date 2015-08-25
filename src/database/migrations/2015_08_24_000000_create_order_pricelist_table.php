<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPricelistTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        // Create the new table
        if (! Schema::hasTable('order_pricelist')) {
            Schema::create('order_pricelist', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('order_id')->unsigned();
                $table->integer('pricelist_id')->unsigned();
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('pricelist_id')->references('id')->on('pricelists')->onDelete('cascade');
            });
        }
        
        // Transfer existing data to new table, at the end of it drop table user_pricelists
        if (Schema::hasTable('user_pricelists') and Schema::hasTable('order_pricelist')) {
            $orders = DB::table('user_pricelists')->get();
            
            foreach ($orders as $item) {
                $order_id = DB::table('orders')->insertGetId([
                    'user_id' => $item->user_id,
                    'paid' => $item->paid,
                    'transaction_id' => $item->transaction_id,
                    'payment_status' => $item->payment_status,
                    'options' => $item->options,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at
                ]);
                
                DB::table('order_pricelist')->insert([
                    'order_id' => $order_id,
                    'pricelist_id' => $item->pricelist_id
                ]);
            }
            
            Schema::dropIfExists('user_pricelists');
        }
	}
    
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        // Create the old table
        if (! Schema::hasTable('user_pricelists')) {
            Schema::create('user_pricelists', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
                $table->integer('pricelist_id')->unsigned();
                $table->foreign('pricelist_id')->references('id')->on('pricelists');
                $table->unique(array('user_id', 'pricelist_id'));
                $table->decimal('paid', 8, 2)->default(0);
                $table->string('transaction_id')->default('Unknown')->nullable();
                $table->string('payment_status')->default('Completed')->nullable();
                $table->text('options')->nullable();
                $table->timestamps();
            });
        }
        
        // Transfer existing data to old table, at the end of it drop table order_pricelist
        if (Schema::hasTable('user_pricelists') and Schema::hasTable('order_pricelist')) {
            $orders = DB::table('orders')
                ->join('order_pricelist', 'orders.id', '=', 'order_pricelist.order_id')
                ->select('orders.*', 'order_pricelist.pricelist_id')
                ->get();
            
            foreach ($orders as $item) {
                $order_id = DB::table('user_pricelists')->insertGetId([
                    'user_id' => $item->user_id,
                    'paid' => $item->paid,
                    'transaction_id' => $item->transaction_id,
                    'payment_status' => $item->payment_status,
                    'options' => $item->options,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'pricelist_id' => $item->pricelist_id
                ]);
            }
            
            Schema::dropIfExists('order_pricelist');
        }
	}

}
