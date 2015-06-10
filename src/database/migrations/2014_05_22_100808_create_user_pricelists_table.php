<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPricelistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_pricelists');
    }
}
