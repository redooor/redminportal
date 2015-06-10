<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('products')) {
            Schema::create('products', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('sku', 255)->unique();
                $table->string('short_description', 255);
                $table->text('long_description')->nullable();
                $table->float('price')->default(0);
                $table->boolean('featured')->default(FALSE);
                $table->boolean('active')->default(TRUE);
                $table->text('options')->nullable();
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                 // created_at | updated_at DATETIME
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
		Schema::dropIfExists('products');
	}

}
