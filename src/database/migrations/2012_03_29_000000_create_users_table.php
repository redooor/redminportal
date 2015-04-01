<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
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
            if (! Schema::hasColumn('remember_token ')) {
                Schema::table('users', function($table) {
                    $table->rememberToken();
                });
            }
            
        } else {
            // Continue support of Cartalyst/Sentry schema
            Schema::create('users', function(Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password', 60);
                $table->rememberToken();
                $table->timestamps();
                
                $table->text('permissions')->nullable();
                $table->boolean('activated')->default(0);
                $table->string('activation_code')->nullable();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('last_login')->nullable();
                $table->string('persist_code')->nullable();
                $table->string('reset_password_code')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                
                // We'll need to ensure that MySQL uses the InnoDB engine to
                // support the indexes, other engines aren't affected.
                $table->engine = 'InnoDB';
                $table->index('activation_code');
                $table->index('reset_password_code');
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
        Schema::dropIfExists('users');
    }
}
