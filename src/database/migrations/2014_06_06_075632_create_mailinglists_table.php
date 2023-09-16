<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateMailinglistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('mailinglists')) {
            Schema::create('mailinglists', function(Blueprint $table) {
                $table->increments('id');
                $table->string('email', 255)->unique();
                $table->string('first_name', 255);
                $table->string('last_name', 255);
                $table->boolean('active')->default(true);
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
        Schema::dropIfExists('mailinglists');
    }
}
