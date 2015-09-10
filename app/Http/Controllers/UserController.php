<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Role;
use App\User;
use DB;
use Input;
use JWTAuth;

class UserController extends Controller {

	private $user;

	public function __construct()
	{
        $this->middleware('jwt.auth');
        //$this->middleware('jwt.refresh');
        $this->user = JWTAuth::parseToken()->toUser();
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

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
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id 要取得資料的 user id
	 * @return Response
	 */
	public function show($id)
	{
        //$current_user = JWTAuth::parseToken()->toUser();
        $current_user = $this->user;
        if( (int)$id === (int)$current_user->id ){
            //取得自己的資料，附加 provider 後回傳
            $current_user['provider'] = $current_user->provider;
            return $current_user;
        } else if( $current_user->is_admin ) {
            // admin 取得別人的資料
            $user = User::find($id);
            if(is_null($user)){
                //　帳號不存在
                $messages[] = ['type' => 'error', 'content' => '該帳號不存在'];

                return response()->json(["messages" => $messages], 406);
            }
            // 該帳號存在，附加 provider 後回傳
            $user['provider'] = $user->provider;
            return $user;
        }

        // 不是自己的，也不是 admin
        //　權限不足
        $messages[] = ['type' => 'error', 'content' => '您沒有取得他人資料之權限'];

        return response()->json(["messages" => $messages], 403);

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
        if( ((int)$this->user->id === (int)$id) or ($this->user->is_admin) ){
            // 只有自己和 admin 能進行更新

            // 取得要更新之 user
            $user = User::find($id);

            switch(Input::get('mode')) {
                case 'toggle_role':
                    // 變更角色
                    return $this->toggleUserRole( $user, Input::get('roleName') );
                    break;

                case 'update_profile':
                    // 更新 user data
                    return $this->updateProfile( $user, Input::all() );
                    break;
            }
		}

        // 非自己也非 admin
        $messages[] = ['type' => 'error', 'content' => '您沒有變更他人資料之權限'];
        return response()->json(["messages" => $messages], 403);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


    /**
     * 依照頁數取得分頁之 user list
     * @param $page 頁數
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUsersByPage($page)
    {
        // 排序欄位，預設：real_name
        $order_by = Input::get('sortBy', 'real_name');
        // 遞增或遞減，前端傳過來的值是 asc 或 dsc
        $sort_order = ( Input::get('sortOrder', 'asc') === 'dsc' ) ? 'desc' : 'asc';
        // 一頁筆數，預設：10
        $per_page = Input::get('count', 10);
        // 各個角色是否列出之篩選陣列
        $filter_roles = json_decode(Input::get('filterBy', null), true)['roles'];
        $roles=[]; // 存放要列出之 role name
        foreach($filter_roles as $k => $v){
            // $v = true or false
            // 可能為字串形式，所以強制轉型較保險
            if( (bool)$v ) $roles[]=$k;
        }
        // 依據 role name，取出要列出之 role id
        $role_ids = array_fetch( Role::whereIn('name', $roles)->get(['id'])->toArray(), 'id');
        // 從 pivot table 取出 user id
        $user_ids = array_fetch( DB::table('role_user')->distinct()->whereIn('role_id',$role_ids)->get(['user_id']), 'user_id');

        if($this->user->is_admin){
            // 只有 admin 才能取得列表
            // role 只取 id、name
            // 預設以 user real_name 遞增排序
            // 預設一頁 10 筆
            return User::with(['roles'=>function($query) use ($roles){
                        $query->addSelect('id','name');
                    }])->whereIn('id', $user_ids)
                        ->orderBy( $order_by, $sort_order )
                        ->paginate( $per_page );

        }else{
            $messages[] = ['type' => 'error', 'content' => '權限不足'];

            return response()->json(["messages" => $messages], 403);
        }
    }

    /**
     * 切換 user 之 role 設定
     * @param App\User $user 要變更 role 之 user
     * @param $role_name 要變更之 role name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function toggleUserRole( User $user, $role_name )
    {
        $role_id_to_change = Role::whereName( $role_name )
                                 ->first()->id;

        if ( $user->roles()->whereName( $role_name )->first() ) {
            // 有該權限，則取消該權限
            $user->roles()
                 ->detach( $role_id_to_change );

            $messages[ ] = [ 'type' => 'success', 'content' => '角色移除完成' ];

            return response()->json( [ "messages" => $messages ], 200 );

        }
        // 沒有該權限，則新增該權限
        $user->roles()
             ->attach( $role_id_to_change );

        $messages[ ] = [ 'type' => 'success', 'content' => '角色新增完成' ];

        return response()->json( [ "messages" => $messages ], 200 );
    }

    /**
     * 更新 user data
     * @param App\User $user
     * @param $input
     * @return array
     * @internal param $messages
     */
    private function updateProfile( User $user, $input )
    {
        $user->real_name = $input['real_name'];
        $user->email = $input['email'];

        // 處理密碼 for local user only
        // 取得 $provider for user
        if ( $user->password ) {
            // user has password
            // user is a local user
            // 如果有 $new_passwd，則變更密碼
            $new_passwd = isset($input['new_passwd']) ? $input['new_passwd'] : null;
            $new_passwd2 = isset($input['new_passwd2']) ? $input['new_passwd2'] : null;
            if ( $new_passwd && $new_passwd === $new_passwd2 ) {
                $user->password = bcrypt( $new_passwd );
            }
        }

        $user->save();
        $token = $user->generateTokenFromUser();
        $token_type = 'refresh_profile';
        $messages[ ] = [ 'type' => 'success', 'content' => '個人資料已更新' ];

        return compact( 'token', 'token_type', 'messages' );
    }

    public function saveLink()
    {
        //$user = JWTAuth::parseToken()->toUser();
        //dd($user->id);
        $this->user->students()->attach(\Request::get('stu_id'));
        return \Request::get('stu_id');
    }

}
