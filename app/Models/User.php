<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 指明一个用户拥有多条博客
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /**
     * 通过用户邮箱访问gravatar获取头像
     */
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    /**
     * 监听事件
     */
    public static function boot() {
        parent::boot();
        //创建用户前添加为activation_token 字段赋值
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }

    public function feed()
    {
        return $this->statuses()
                    ->orderBy('created_at', 'desc');
    }

}
