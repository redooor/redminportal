<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddRemembertokenToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            // Add remember_token column to existing table
            if (! Schema::hasColumn('users', 'remember_token')) {
                Schema::table('users', function($table) {
                    $table->rememberToken();
                });
            }
            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('users')) {
            // Add remember_token column to existing table
            if (! Schema::hasColumn('users', 'remember_token')) {
                Schema::table('users', function(Blueprint $table)
                {
                    $table->dropColumn(['remember_token']);
                });
            }   
        }
    }
}
