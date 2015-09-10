<?php namespace App\Http\Controllers;

use App\Http\Requests\LocalLoginFormRequest;
use App\Role;
use App\School;
use App\User;
use Illuminate\Http\Request;
use Input;
use JWTAuth;
use Socialize;

//use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Providers\JWTAuthServiceProvider;

//use Illuminate\Http\Request;

class JWTAuthController extends Controller {

    //private $redirect_token_to = '/#/?token=';
    private $redirect_token_to;

    public function __construct()
    {
        $this->middleware('guest');
        $this->redirect_token_to = '/#/?token=';
    }

    /**
     * 本機帳號登入
     * @param LocalLoginFormRequest|Request $request
     * @return array
     */
    public function local(LocalLoginFormRequest $request)
    {
        $user = User::where( 'name', $request->get('name') )->first();

        if( $user && \Hash::check($request->get('password'), $user->password) ){
            //$token = $this->generateTokenFromUser($user, 'local');
            $token = $user->generateTokenFromUser();

            return compact('token');
        }

        $messages[] = ['type' => 'error', 'content' => '帳號或密碼錯誤'];

        return response()->json(["messages" => $messages], 401);

    }

    /**
     * OpenID 登入，回傳 token 給前端
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function openid()
    {
        $openid = app('ntpcopenid');

        if(!$openid->mode) {
            return redirect($openid->authUrl());
        }

        switch ($openid->mode) {
            case 'cancel': // 取消授權
                return redirect()->route('index');
                break;

            case 'id_res': // 同意授權
                if (!$openid->validate()) {
                    // 驗證未過
                    // 導向至首頁                
                    return redirect()->route('index');
                }

                // 驗證通過
                // 取得 user data，回傳陣列
                $data = $openid->getUserData();
                
                // 取得允許 OpenID 登入之學校代碼
                $arr_openid_schools = School::all()->lists('code')->toArray();

                if( $arr_openid_schools && !in_array($data['pref/timezone']['id'], $arr_openid_schools) ){
                    // 有設定允許 OpenID 登入之學校代碼
                    // 且登入者所屬學校代碼不在允許清單
                    // 則重導回首頁
                    return redirect('/');
                }

                if( $arr_openid_schools && !in_array('導師', $data['pref/timezone']['groups']) ){
                    // 有設定允許 OpenID 登入之學校代碼
                    // 且登入者所屬學校代碼　在　允許清單中
                    // 但職稱別不含　導師
                    // 則重導回首頁
                    return redirect('/');
                }

                $userdata = [
                    'name' => 'openid_' . $data['openid'],
                    'real_name' => $data['namePerson'],
                    'email' => $data['contact/email']
                ];

                // 產生 user token
                // 傳入現有或新增之 user 物件
                $user = $this->findOrCreateSocialUser( $userdata, 'teacher' );
                //$token = $this->generateTokenFromUser($user, 'openid');
                $token = $user->generateTokenFromUser();

                return redirect( $this->redirect_token_to . $token );
                break;

            default: // 其他，如直接輸入網址瀏覽
                return redirect()->route('index');
                break;
        }


    }

    /**
     * 導向 facebook or google 同意畫面
     * @param $social facebook or google
     * @return 轉向
     */
    public function socialRedirect($social)
    {
        return Socialize::with($social)->redirect();
    }

    /**
     * for facebook or google 認證完導回
     * @param $social facebook or google
     * @return string JWT token 回傳給前端
     */
    public function getUserToken($social)
    {
        if( Input::has('error') && Input::get('error') == 'access_denied' )
        {
            //使用者取消
            return redirect()->route('index');
        }

        $attr = (array)Socialize::with($social)->user();

        $userdata = [
            'name'      => $social . '_' . $attr[ 'id' ],
            'real_name' => $attr[ 'name' ],
            'email'     => $attr[ 'email' ]
        ];
        $user = $this->findOrCreateSocialUser( $userdata, 'parents' );
        //$token = $this->generateTokenFromUser($user, $social);
        $token = $user->generateTokenFromUser();

        return redirect( $this->redirect_token_to . $token );
    }

    /**
     * for OpenID facebook google
     * 取出已存在之user或新增user
     * 若為新增，則附加相關的 role
     *
     * @param $userdata user資料陣列
     * @param null $role_name role名稱字串
     * @return App\User物件
     */
    private function findOrCreateSocialUser( $userdata, $role_name = null )
    {
        $user = User::where( 'name', $userdata[ 'name' ] )
                    ->first();
        if ( is_null( $user ) ) {
            $user = User::create( $userdata );
            if(!is_null($role_name)) {
                $user->roles()->attach( Role::whereName( $role_name )
                                          ->first()->id );
            }
        }

        return $user;
    }


    public function refreshJWT(){
        try {
            $old_token = Input::get('old_token');

            $newToken = JWTAuth::refresh($old_token);
        } catch (TokenExpiredException $e) {
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch (JWTException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        }

        return ['token_type' => 'refresh_only', 'token' => $newToken, 'old'=> $old_token];
    }

    /**
     * 由 user 物件產生JWT token
     * 附加 real_name 、 roles 欄位
     * @param $user App\User 物件
     * @return string JWT token
     */
    //private function generateTokenFromUser(User $user, $provider)
    //{
    //    //$roles = $user
    //    //        ->roles
    //    //        ->map(function($role){
    //    //            return $role->name;
    //    //        });
    //    $roles = $user->getRoleNames();
    //
    //    $token = JWTAuth::fromUser(
    //        $user,
    //        [
    //            'real_name' => $user->real_name,
    //            'roles' => $roles,
    //            'provider' => $provider
    //        ]);
    //
    //    return $token;
    //}

}
