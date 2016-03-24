<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductVariantTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('product_variant')) {
            Schema::create('product_variant', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('product_id')->unsigned();
                $table->integer('variant_id')->unsigned();
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('variant_id')->references('id')->on('products')->onDelete('cascade');
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
		Schema::dropIfExists('product_variant');
	}

}
