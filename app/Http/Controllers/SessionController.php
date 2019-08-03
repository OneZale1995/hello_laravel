<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionController extends Controller
{

    public function __construct()
    {
        //只让未登录用户访问登录页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /**
     * 用户登录页面显示
     */
    public function create()
    {
        return view('session.create');
    }

    /**
     * 处理用户登录的操作
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if(Auth::attempt($credentials, $request->has('remember'))) {
            //登录成狗后的相关操作
            session()->flash('success', '欢迎回来!');
            $fallback = route('users.show', Auth::user());
            return redirect()->intended($fallback);
        }else{
            //登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}