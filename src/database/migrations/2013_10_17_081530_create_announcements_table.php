<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnnouncementsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('title', 255);
                $table->text('content');
                $table->boolean('private')->default(TRUE);
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
		Schema::dropIfExists('announcements');
	}

}
