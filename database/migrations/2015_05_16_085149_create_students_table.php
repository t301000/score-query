<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('students');

        Schema::create('students', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('class_id');
            $table->foreign('class_id')->references('id')->on('classrooms')->onDelete('cascade');
			$table->smallInteger('num')->unsigned();
            $table->string('name');
            $table->string('link_code');
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
		Schema::drop('students');
	}

}
