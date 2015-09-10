<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentUserPivotTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('student_user', function(Blueprint $table)
		{
			$table->integer('student_id')->unsigned()->index();
			$table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
			$table->integer('user_id')->unsigned()->index();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			//$table->integer('class_id')->unsigned()->index();
			//$table->foreign('class_id')->references('id')->on('classrooms')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('student_user');
	}

}
