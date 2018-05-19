<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

//    public $timestamps = trye;

    /*protected function getDateFormat()
    {
        return date('Y-m-d H:i:s');
    }

    protected function asDateTime($value)
    {
        return $value;
    }*/

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

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    public function link($params = [])
    {
        $params = array_merge([$this->id], $params);
        return route('users.show', $params);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public static function boot()
    {
        parent::boot();

        // 创建监听方法，用于创建用户时自动生成 activation_toke
        static::creating(function ($user) {
            $user->activation_token = str_random(32);
        });

        // 监听方法，用户创建用户成功后执行
        static::created(function ($user) {
            //
        });
    }

    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);
        // with 预加载Status模型里面的 user关联数据

        return Status::whereIn('user_id', $user_ids)->with('user')->orderBy('created_at', 'desc'); //返回查询构造器
    }

    /**
     * 粉丝多对多
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * 用户关注的多对多
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        return $this->followings()->sync($user_ids, false);
    }

    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        return $this->followings()->detach($user_ids);
    }

    public function isFollowing($user_id)
    {
        // 1. 返回的是一个 HasMany 对象
        // $this->followings()
        // 2. 返回的是一个 Collection 集合
        // $this->followings
        // 3. 第2个其实相当于这样
        // $this->followings()->get()
        // 如果不需要条件直接使用 2 那样，写起来更短
        // contains 是collection里面的一个方法
        //$user->followings == $user->followings()->get() // 等于 true
        return $this->followings->contains($user_id);
    }
}
