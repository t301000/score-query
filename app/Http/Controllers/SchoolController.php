<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\School;
use Illuminate\Http\Request;
use JWTAuth;

class SchoolController extends Controller {

    private $user;

    public function __construct()
    {
        $this->middleware('jwt.auth');
        $this->user = JWTAuth::parseToken()->toUser();
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return School::all();
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
        $data = \Request::only('code', 'name');

        if($this->user->is_admin) {
            try {
                $newSchool = School::create( $data );
                $messages[] = [ 'type' => 'success', 'content' => '新增學校資料成功' ];

                return response()->json( [ "messages" => $messages, "newSchool" => $newSchool ], 200 );
                //return School::create($data);
            } catch ( \Exception $e ) {
                $messages[] = [ 'type' => 'error', 'content' => '新增學校資料失敗' ];

                return response()->json( [ "messages" => $messages, "Exception" => $e ], 200 );
            }
        }

        $messages[] = [ 'type' => 'error', 'content' => '權限不足' ];

        return response()->json( [ "messages" => $messages ], 200 );
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
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$school = School::find($id);

        if($this->user->is_admin) {
            if ( $school && $school->delete() ) {
                $messages[] = [ 'type' => 'success', 'content' => '刪除學校資料成功' ];

                return response()->json( [ "messages" => $messages ], 200 );
            }

            $messages[] = [ 'type' => 'error', 'content' => '刪除學校資料失敗' ];

            return response()->json( [ "messages" => $messages ], 200 );
        }

        $messages[] = [ 'type' => 'error', 'content' => '權限不足' ];

        return response()->json( [ "messages" => $messages ], 200 );
	}

}
