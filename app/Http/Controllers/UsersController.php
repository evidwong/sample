<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        //$user=Auth::user();
        //var_dump($user->toArray());
        //$link = $user->link($params = ['source' => 'list']);
        //dd($link);

        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        $statuses = $user->statuses()->orderBy('created_at', 'desc')->paginate(10);
//        dd($statuses->toArray());
        return view('users.show', compact('user', 'statuses'));
    }

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
        $this->sendMailConfirmationTo($user);
        return redirect('/')->with('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        //Auth::login($user);
        //return redirect()->route('users.show', [$user])->with('success','欢迎，您将在这里开启一段新的旅程~');
    }

    /**
     * 发送激活邮件
     * @param $user 用户对象
     */
    public function sendMailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";
        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        return redirect()->route('users.show', [$user])->with('success', '激活成功！');
    }

    public function edit(User $user)
    {

        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);
//         DB::enableQueryLog();
        // var_dump($user);
        /*$user->update([
            'name' => $request->name,
            'password' => bcrypt($request->password),
        ]);*/
//        $sql=DB::getQueryLog();
//        var_dump($sql);
        // return redirect()->route('users.show',$user->id)->with('success','更新成功');

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        // DB::enableQueryLog();
        // var_dump($user);
        /*$user->update([
            'name' => $request->name,
            'password' => bcrypt($request->password),
        ]);*/
        // $sql=DB::getQueryLog();
        // var_dump($sql);
        return redirect()->route('users.show', $user->id)->with('success', '更新成功');
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', '删除用户成功');
    }


    public function followings(User $user)
    {
        $users = $user->followings()->paginate(10);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(10);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}
