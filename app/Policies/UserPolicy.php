<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 将登录用户实例与要授权的用户实例进行比较
     * 当两个ID相同时，则代表是相同用户，通过授权
     * 否则抛出403错误
     */
    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }

    /**
     * 用户删除策略
     * 只有当前登录用户为管理员才允许删除
     * 删除的用户对象不能是自己（即使是管理员也不能删除自己）
     */
    public function destroy(User $currentUser, User $user)
    {
        return $currentUser->id !== $user->id && $currentUser->is_admin;
    }
}
