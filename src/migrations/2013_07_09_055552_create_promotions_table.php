<?php

use Illuminate\Database\Migrations\Migration;

class CreatePromotionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('promotions', function($table) {
            $table->increments('id');
            $table->string('name', 64);
            $table->string('short_description', 255);
            $table->text('long_description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('active')->default(TRUE);
            $table->text('options')->nullable();
             // created_at | updated_at DATETIME
            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('promotions');
	}

}
