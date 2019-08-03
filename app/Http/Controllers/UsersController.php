<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * 展示注册页面
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * 创建用户的操作
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);

        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user]);
    }

    /**
     * 用户信息页面展示
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }


    /**
     * 利用laravel的隐性路由模型绑定功能，直接读取对应ID的用户实例，未找到则报错
     * 将查找的用户实例$user与编辑视图进行绑定
     * 编辑用户的操作页面
     */
    public function edit(User $user)
    {
        //compact 为页面渲染二维数组
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);


    }
}
