<?php namespace App\Http\Controllers;

use App\Classroom;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\User;
use Illuminate\Http\Request;
use Input;
use JWTAuth;

class ClassroomController extends Controller {

	private $user;

	public function __construct()
	{
		$this->middleware('jwt.auth');
		//$this->middleware('jwt.refresh');
		$this->user = JWTAuth::parseToken()->toUser();
	}

	/**
	 * 取得某位老師管理的所有班級
	 *
	 * @return Response
	 */
	public function index()
	{
		return $this->user->classrooms;
	}

    /**
     * 取得所有班級列表 for admin
     *
     * @return Response
     */
    public function adminIndex()
    {
        if(!$this->user->is_admin){
            $messages[] = [ 'type' => 'error', 'content' => '沒有系統管理員之權限' ];

            return response()->json( [ "messages" => $messages ], 403 );
        }

        return Classroom::all();
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
	public function store()
	{
		if($this->user->is_teacher) {

            $exist_codes = Classroom::all( [ 'class_code' ] )
                                    ->toArray();
            $new_code = str_random( 4 );
            while ( in_array( $new_code, $exist_codes ) ) {
                $new_code = str_random( 4 );
            }

            $class_data = [
                'school_year_in' => (int)Input::get( 'school_year_in' ),
                'class_name'     => Input::get( 'class_name' ),
                'teacher_id'     => $this->user->id,
                'class_code'     => $new_code
            ];

            if ( $classroom = Classroom::create( $class_data ) ) {
                $messages[] = [ 'type' => 'success', 'content' => '班級新增成功' ];

                return response()->json( [ "new_class" => $classroom, "messages" => $messages ], 200 );
            }

            $messages[] = [ 'type' => 'error', 'content' => '班級新增失敗' ];

            return response()->json( [ "messages" => $messages ], 403 );
        }

        $messages[] = [ 'type' => 'error', 'content' => '沒有新增班級之權限' ];

        return response()->json( [ "messages" => $messages ], 403 );
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        //$get_fields = ['id', 'school_year_in', 'class_name'];

        //return $this->user->classrooms()->where('id', $id)->first($get_fields);
        return $this->user->classrooms()->where('id', $id)->first();
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
	public function update($id)
	{
        $this->user->classrooms()->where('id', $id)->first()->update(Input::all());
        $messages[] = ['type' => 'success', 'content' => '班級資料更新成功'];

        return response()->json(["new_data" => Input::all(),"messages" => $messages], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $class = Classroom::find($id);
        if(!$class){
            $messages[] = [ 'type' => 'error', 'content' => '班級不存在' ];

            return response()->json( [ "messages" => $messages ], 403 );
        }
        // 檢查權限
        if(!$this->user->isAdmin && $class->teacher_id != $this->user->id){
            $messages[] = [ 'type' => 'error', 'content' => '沒有權限' ];

            return response()->json( [ "messages" => $messages ], 403 );
        }

        // 篩選出刪除班級之學生家長
        $allIDs = User::whereHas('students', function($q) use($id){
            $q->where('class_id', $id);
        })->lists('id');
        // 具有至少 2 種身份的 user
        $mustKeepIDs = User::has('roles', '>=', 2)->lists('id');
        // 篩選出同時也是別的班級的家長
        $keepIDs = User::whereHas('students', function($q) use($id){
            $q->where('class_id', $id);
        })->whereHas('students', function($q) use($id){
            $q->where('class_id', '!=', $id);
        })->lists('id');
        // 合併陣列，並刪除重複值，產生最後須保留之ID array
        $keepIDs = array_unique(array_merge($keepIDs, $mustKeepIDs));
        // 要刪除之user id
        $removeIDs = collect($allIDs)->diff($keepIDs)->flatten()->all();
        //return compact('allIDs', 'mustKeepIDs', 'keepIDs', 'removeIDs');
        // 刪除班級
        $success = $this->user->classrooms()->find($id)->delete();

        if($success) {
            // 刪除班級成功後，刪除家長
            // 紀錄刪除之資料筆數
            $count = 0;
            if(!empty($removeIDs)){
                $count = User::destroy($removeIDs);
            }

            $messages[] = [
                'type' => 'success',
                'content' => "班級刪除成功，刪除了 $count 位家長"
            ];

            return response()->json( [ "messages" => $messages ], 200 );

        }else{
            $messages[] = [ 'type' => 'error', 'content' => '班級刪除失敗' ];

            return response()->json( [ "messages" => $messages ], 403 );
        }
	}

}
