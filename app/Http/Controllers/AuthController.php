<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Support\AuditLogger;
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




        $legacyPasswordHash = strtoupper(md5($request->password));

        if ($user && hash_equals((string) $user->password, $legacyPasswordHash)) {

            if (empty($role)) {
                AuditLogger::record($request, 'auth.login_denied', 'ปฏิเสธการเข้าสู่ระบบเนื่องจากไม่มีสิทธิ์', [
                    'category' => 'authentication',
                    'result' => 'denied',
                    'actor_username' => $request->username,
                    'target_type' => 'user',
                    'target_id' => $request->username,
                    'metadata' => ['reason' => 'role_not_assigned'],
                ]);

                return back()->withErrors([
                    'username' => 'ไม่มีสิทธิ์เข้าใช้งานระบบ',
                ])->withInput($request->only('username'));
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

            AuditLogger::record($request, 'auth.login_success', 'เข้าสู่ระบบสำเร็จ', [
                'category' => 'authentication',
                'target_type' => 'session',
                'metadata' => ['role' => $role],
            ]);

            return redirect()->intended(route('amr.index'))->with('success', 'เข้าสู่ระบบสำเร็จ');
        }

        AuditLogger::record($request, 'auth.login_failed', 'เข้าสู่ระบบไม่สำเร็จ', [
            'category' => 'authentication',
            'result' => 'failed',
            'actor_username' => $request->username,
            'target_type' => 'user',
            'target_id' => $request->username,
            'metadata' => ['reason' => 'invalid_credentials'],
        ]);

        return back()->withErrors([
            'username' => 'Username หรือ Password ไม่ถูกต้อง',
        ])->withInput($request->only('username'));
    }
    public function logout(Request $request)
    {
        AuditLogger::record($request, 'auth.logout', 'ออกจากระบบ', [
            'category' => 'authentication',
            'target_type' => 'session',
        ]);

        Auth::logout();
        Session::forget('user');
        // ล้าง session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ออกจากระบบสำเร็จ');
    }
}
