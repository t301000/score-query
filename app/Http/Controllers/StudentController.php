<?php namespace App\Http\Controllers;

use App\Classroom;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Student;
use Illuminate\Http\Request;
use Input;
use JWTAuth;

class StudentController extends Controller {

    /**
     * StudentController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['getAllScores']]);
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($classID)
	{

        return Classroom::find($classID)->students;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($classID)
	{
        // 收到的資料：[{num: 4, name: "a"}]
        $newStudents = Input::all();

        $user = JWTAuth::parseToken()->toUser();
        $classroom = Classroom::with('teacher')->find($classID);

        if($user->id === $classroom->teacher->id){
            // 取得班級內已有的學生 link_code 陣列
            $exist_link_codes = $classroom->students
                                    ->map(function($stu){
                                        return $stu->link_code;
                                    })->toArray();
            // 取得已存在之座號
            $exist_stu_nums = Student::where('class_id', $classID)->lists('num');
            //return $exist_stu_nums;
            // 存放新增之學生物件之陣列
            $newStudentModels = [];

            // 可以新增學生
            foreach($newStudents as $stu){
                // 如果座號已存在則跳過
                if(in_array($stu['num'], $exist_stu_nums)){
                    continue;
                }
                // 產生不重複的 link_code
                $code = str_random(4);
                while(in_array($code, $exist_link_codes)){
                    $code = str_random(4);
                }
                $stu['link_code'] = $code;
                $exist_link_codes[] = $code; // 將新的 link_code 加入陣列
                $newStudentModels[] = new Student($stu);
            }
            // 有新增加之學生
            if($newStudentModels) {
                $classroom->students()
                          ->saveMany( $newStudentModels );
            }

            $messages[] = ['type' => 'success', 'content' => '新增學生成功'];

            return response()->json(["messages" => $messages], 200);
        }

        $messages[] = ['type' => 'error', 'content' => '新增學生失敗'];

        return response()->json(["messages" => $messages], 403);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($classID, $stuID)
	{
        $user = JWTAuth::parseToken()->toUser();
        $class = Classroom::find($classID);

        if($class->teacher_id == $user->id){
            $stu = Student::find($stuID);
            if( !is_null($stu) && $stu->update(Input::all()) ){
                $messages[] = ['type' => 'success', 'content' => '更新學生資料成功'];

                return response()->json(["messages" => $messages], 200);;
            }
        }

        $messages[] = ['type' => 'error', 'content' => '更新學生資料時發生錯誤'];

        return response()->json(["messages" => $messages], 403);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($classID, $stuID)
	{
        $user = JWTAuth::parseToken()->toUser();
        $classroom = Classroom::with('teacher')->find($classID);

        if($user->id === $classroom->teacher->id){
            Student::destroy($stuID);

            $messages[] = ['type' => 'success', 'content' => '刪除學生成功'];

            return response()->json(["messages" => $messages], 200);
        }

        $messages[] = ['type' => 'error', 'content' => '刪除學生失敗'];

        return response()->json(["messages" => $messages], 403);
	}

    public function getAllScores($stu_id){
        $user = JWTAuth::parseToken()->toUser();

        if($user->is_teacher){
            $curr_stu = Student::find($stu_id);
            if(!is_null($curr_stu) && $curr_stu->classroom->teacher_id != $user->id){
                $messages[] = ['type' => 'error', 'content' => '您不是該班導師'];

                return response()->json(["messages" => $messages], 403);
            }
        }else {
            $curr_stu = $user->students()
                             ->find( $stu_id );
        }

        if(is_null($curr_stu)){
            $messages[] = ['type' => 'error', 'content' => '找不到資料'];

            return response()->json(["messages" => $messages], 403);
        }

        return $curr_stu->load('scores.exam');
    }

    public function regetLinkCode($stuID)
    {
        $stu = Student::find($stuID);

        if(!is_null($stu)){
            $link_codes = Student::select('link_code')->where('class_id', $stu->class_id)->lists('link_code');
            $code = str_random(4);
            while(in_array($code, $link_codes)){
                $code = str_random(4);
            }
            $stu->link_code = $code;
            $stu->save();
            return $code;
        }

    }

}
