<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Account::where('username', $request->username)->first();

        $role = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->where('account_role.username', $request->username)
            ->value('name');




        if ($user && strtoupper(md5($request->password)) === $user->password) {

            if (empty($role)) {
                return back()->withErrors([
                    'username' => 'ไม่มีสิทธิ์เข้าใช้งานระบบ',
                ])->withInput($request->only('username', 'password'));
            }

            Auth::login($user);

            // เก็บ session เพิ่มเติม
            Session::put('user', [
                'logged_in' => true,
                'user_id' => $user->userid,
                'username' => $user->username,
                'fullname' => $user->fname . ' ' . $user->lname,
                'role' => $role,
                'last_activity' => now(),
            ]);

            return redirect()->intended(route('index'))->with('success', 'เข้าสู่ระบบสำเร็จ');;
        }

        // ส่งกลับพร้อมข้อมูลเดิม รวมถึง password
        return back()->withErrors([
            'username' => 'Username หรือ Password ไม่ถูกต้อง',
        ])->withInput($request->only('username', 'password', 'remember'));
    }
    public function logout(Request $request)
    {
        Session::forget('user');
        // ล้าง session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ออกจากระบบสำเร็จ');
    }
}
