<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// 全域 route 參數限制
// social 為 facebook or google
$router->pattern('social', '(facebook|google)');

// 首頁
Route::get('/', ['as' => 'index', 'uses' => 'AppController@index']);

Route::group(['prefix' => 'auth'], function() {
    // 本機帳號登入
    Route::post( 'local', 'JWTAuthController@local' );
    // OpenID登入，完成後回傳JWT token
    Route::get( 'openid', 'JWTAuthController@openid' );
    // facebook or google 認證完導回後，回傳JWT token
    Route::get( '{social}/token', 'JWTAuthController@getUserToken' );
    // 導向 facebook or google 同意畫面
    Route::get( '{social}', 'JWTAuthController@socialRedirect' );
    //Route::post('refresh_jwt', ['middleware' => 'jwt.auth', 'uses' => 'JWTAuthController@refreshJWT']);
    Route::post('refresh_jwt', 'JWTAuthController@refreshJWT');
});

// 取得登入者資料
Route::get('me', ['as' => 'user.me', 'uses' => 'UserController@getCurrUser']);
// 以分頁方式取得 user 列表，參數 page 為頁數
Route::get('users/page/{page}', 'UserController@getUsersByPage')->where('page', '[0-9]+');
// RESTful api for User resource
Route::resource('users', 'UserController');

// RESTful api for Classroom resource
Route::resource('classrooms', 'ClassroomController');

Route::get('manage-classrooms', 'ClassroomController@adminIndex');

// RESTful api for Student sub-resource
Route::resource('classrooms/{classID}/students', 'StudentController');
Route::get('reget-link-code/{stuID}', 'StudentController@regetLinkCode');
// RESTful api for Exam sub-resource
Route::resource('classrooms/{classID}/exams', 'ExamController');
// RESTful api for Score sub-resource
Route::resource('classrooms/{classID}/exams/{examID}/scores', 'ScoreController');

// RESTful api for School resource
Route::resource('schools', 'SchoolController');

// 以班級代碼與學生代碼取得班級（含導師）與學生資料
Route::get('find-by-code/{class_code}/{stu_code}', 'FindDataByCode@findClassStu');

// 管理 user student classroom 之對應
Route::get('manage-link', 'ManageLink@getLinkedStudents');
Route::post('manage-link', 'ManageLink@saveLink');
Route::delete('manage-link/{stu_id}', 'ManageLink@deleteLink');

// 以 stu_id 取得歷次成績
Route::get('query-scores/{stu_id}', 'StudentController@getAllScores');

Route::get('test/{class_id}', function($class_id){
    $allIDs = App\User::whereHas('students', function($q) use($class_id){
            $q->where('class_id', $class_id);
        })->lists('id');
    $mustKeepIDs = App\User::has('roles', '>=', 2)->lists('id');
    $keepIDs = App\User::whereHas('students', function($q) use($class_id){
            $q->where('class_id', $class_id);
        }) // 篩選出某班級學生之家長
        ->whereHas('students', function($q) use($class_id){
            $q->where('class_id', '!=', $class_id);
        }) // 且篩選出同時也是別的班級的家長
        ->lists('id');

    $keepIDs = array_unique(array_merge($keepIDs, $mustKeepIDs));

    $removeIDs = collect($allIDs)->diff($keepIDs)->flatten()->all();

    return empty($removeIDs)? 'empty':$removeIDs;

    //return $removeIDs;
});