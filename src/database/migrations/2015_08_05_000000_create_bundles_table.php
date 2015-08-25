<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBundlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('bundles')) {
            Schema::create('bundles', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('sku', 255)->unique();
                $table->string('short_description', 255);
                $table->text('long_description')->nullable();
                $table->decimal('price', 8, 2)->default(0);
                $table->boolean('featured')->default(FALSE);
                $table->boolean('active')->default(TRUE);
                $table->text('options')->nullable();
                $table->integer('category_id')->nullable()->unsigned();
                $table->foreign('category_id')->references('id')->on('categories');
                 // created_at | updated_at DATETIME
                $table->timestamps();
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
		Schema::dropIfExists('bundles');
	}

}
