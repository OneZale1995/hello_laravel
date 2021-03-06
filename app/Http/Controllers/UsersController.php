<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    /**
     * 使用 Laravel 提供身份验证 (Auth) 中间件来过滤未登录用户的edit,update操作
     */
    public function __construct()
    {
        $this->middleware('auth', [
            //设定指定动作不使用 Auth 中间件进行过滤
            'except' => ['show', 'create', 'store', 'confirmEmail']
        ]);
            //只让未登录用户访问登录页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /**
     * 用户列表展示
     */
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

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

        $this->sendEmailConfirmationTo($user);

        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    /**
     * 为新注册的用户发送邮件进行验证
     */
    public function sendEmailConfirmationTo($user)
    {
        $view = "emails.confirm";
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认您的邮箱";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);

        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user]);
    }

    /**
     * 用户信息页面展示
     */
    public function show(User $user)
    {
        $statuses = $user->statuses()
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        return view('users.show', compact('user', 'statuses'));
    }


    /**
     * 利用laravel的隐性路由模型绑定功能，直接读取对应ID的用户实例，未找到则报错
     * 将查找的用户实例$user与编辑视图进行绑定
     * 编辑用户的操作页面
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        //compact 为页面渲染二维数组
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
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

    /**
     * 删除指定用户的操作
     */
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户'. $user->name . '！');
        return back();
    }

    //关注用户列表
    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = $user->name . '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }
    //粉丝列表
    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = $user->name . '的粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}
