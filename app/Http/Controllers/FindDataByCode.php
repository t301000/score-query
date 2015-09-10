<?php namespace App\Http\Controllers;

use App\Classroom;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Student;
use Illuminate\Http\Request;

class FindDataByCode extends Controller {

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

	public function findClassStu($class_code, $stu_code)
    {

        $class = Classroom::firstByAttributes(['class_code' => $class_code]);
        if($class){
            $class->load('teacher');
            $stu = Student::firstByAttributes(['class_id' => $class->id, 'link_code' => $stu_code]);
        }
        return compact('class', 'stu');

        //return ;
    }

}
