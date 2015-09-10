<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use JWTAuth;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $casts = [
        'id' => 'integer'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'real_name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];

    /**
     * define relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    /**
     * define relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classrooms()
    {
        return $this->hasMany('App\Classroom', 'teacher_id');
    }

    /**
     * define relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students()
    {
        return $this->belongsToMany('App\Student');
    }

    /**
     * 虛擬屬性
     * role 是否為 admin
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        $is_admin = false;
        if($this->roles()->where('name','admin')->first()){
            $is_admin = true;
        }

        return $is_admin;
    }

    /**
     * 虛擬屬性
     * role 是否為 admin
     * @return bool
     */
    public function getIsTeacherAttribute()
    {
        $is_teacher = false;
        if($this->roles()->where('name','teacher')->first()){
            $is_teacher = true;
        }

        return $is_teacher;
    }

    /**
     * 虛擬屬性
     * 帳號類型
     * @return string 'facebook'、'google'、'openid'、'local'
     */
    public function getProviderAttribute()
    {
        $provider = is_null($this->password) ? explode('_', $this->name)[0] : 'local';

        return $provider;
    }

    /**
     * 自訂 method
     * 取得只包含 role name 之陣列
     * @return array
     */
    public function getRoleNames()
    {
        $role_names = $this->roles
                ->map(function($role){
                    return $role->name;
                });

        return $role_names;
    }

    /**
     * 自訂 method
     * 由 user 物件產生JWT token
     * 附加 real_name 、 roles 、 provider 欄位
     * @param $provider 帳號種類：local、facebook、google、openid
     * @return string JWT token
     */
    public function generateTokenFromUser()
    {
        $roles = $this->getRoleNames();

        $token = JWTAuth::fromUser(
            $this,
            [
                'real_name' => $this->real_name,
                'roles' => $roles,
                'provider' => $this->provider
            ]);

        return $token;
    }
}
