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
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('real_name');
            $table->string('email');
            $table->string('password', 60)->nullable();
            $table->timestamps();
        });

        App\User::create([
            'name' => 'admin',
            'real_name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456')
        ]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
