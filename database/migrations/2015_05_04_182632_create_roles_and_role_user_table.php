<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesAndRoleUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('role_user');
		Schema::dropIfExists('roles');

		Schema::create('roles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name')->unique();
			$table->string('display_name')->nullable();
		});

		Schema::create('role_user', function(Blueprint $table)
		{
			$table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        App\Role::create([
                'name' => 'admin',
                'display_name' => '管理員'
            ]);
        App\Role::create([
            'name' => 'teacher',
            'display_name' => '導師'
        ]);
        App\Role::create([
            'name' => 'parents',
            'display_name' => '家長'
        ]);

        App\User::find(1)->roles()->attach(1);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('role_user');
        Schema::drop('roles');
	}

}
