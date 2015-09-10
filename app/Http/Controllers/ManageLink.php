<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use JWTAuth;

class ManageLink extends Controller {

    public function __construct()
    {
        $this->middleware('jwt.auth');
        $this->user = JWTAuth::parseToken()->toUser();
    }

	public function getLinkedStudents(){
        return $this->user->students;
    }

    public function saveLink(Request $request)
    {
        $this->user->students()->attach($request->get('stu_id'));

        //return $request->get('stu_id');
    }

    public function deleteLink($stu_id)
    {
        $this->user->students()->detach($stu_id);
    }

}
