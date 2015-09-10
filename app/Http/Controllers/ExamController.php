<?php namespace App\Http\Controllers;

use App\Classroom;
use App\Exam;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Input;
use Illuminate\Http\Request;
use JWTAuth;

class ExamController extends Controller {

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($classID)
	{
		return Exam::where('class_id', $classID)->orderBy('created_at', 'desc')->get()->toArray();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($classID)
	{
        $user = JWTAuth::parseToken()->toUser();

        if($user->is_teacher){
            $newData = \Input::all();
            $newData['class_id'] = $classID;
            $newExam = Exam::create($newData);

            $messages[] = ['type' => 'success', 'content' => '新增評量成功'];

            return response()->json(["messages" => $messages], 200);
        }

        $messages[] = ['type' => 'error', 'content' => '新增評量失敗'];

        return response()->json(["messages" => $messages], 403);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($classID, $id)
	{
		return Exam::find($id);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($classID, $id)
	{
        $user = JWTAuth::parseToken()->toUser();
        $class = Classroom::find($classID);
        $exam = Exam::find($id);

        if($exam && $exam->class_id == $classID && $class->teacher_id == $user->id){
            $exam->grade = Input::get('grade');
            $exam->half = Input::get('half');
            $exam->name = Input::get('name');
            $exam->social_merge = Input::get('social_merge');
            $exam->save();
        }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($classID, $id)
	{
        $user = JWTAuth::parseToken()->toUser();

        $classIDs = $user->classrooms()->select('id')->lists('id');

        $exam = Exam::find($id);

        if(!in_array($exam->class_id, $classIDs)){
            $messages[] = ['type' => 'error', 'content' => '錯誤：沒有權限或評量不存在'];

            return response()->json(["messages" => $messages], 403);
        }

        if( Exam::destroy($id) ){
            $messages[] = ['type' => 'success', 'content' => '刪除評量成功'];

            return response()->json(["messages" => $messages], 200);
        }

        $messages[] = ['type' => 'error', 'content' => '刪除評量失敗'];

        return response()->json(["messages" => $messages], 403);
	}

}
