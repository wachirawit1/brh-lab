<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $timeout = 60; // กำหนดเวลา session หมดอายุ (นาที)
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user') || Session::get('user.logged_in') !== true) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        // 1. ตรวจสอบว่าผู้ใช้ยังมีสิทธิ์อยู่ในระบบฐานข้อมูลจริงๆ หรือไม่ (Real-time Guard)
        $userDB = DB::connection('mysql')->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->where('account_role.username', Session::get('user.username'))
            ->select('account_role.username', 'role.name as role_name')
            ->first();

        if (!$userDB) {
            Session::forget('user');
            Auth::logout();
            return redirect('/login')->with('error', 'บัญชีของคุณถูกระงับหรือไม่มีสิทธิ์เข้าใช้งาน');
        }

        // ใช้สิทธิ์ล่าสุดจากฐานข้อมูลเป็นแหล่งข้อมูลเดียวสำหรับเมนูและ middleware อื่น
        Session::put('user.role', $userDB->role_name);

        $lastActivity = Session::get('user.last_activity');
        $now = now();

        // 2. ตรวจสอบว่า session หมดอายุหรือไม่
        if ($now->diffInMinutes($lastActivity) > $this->timeout) {
            Session::forget('user');
            Auth::logout();
            return redirect('/login')->with('error', 'Session หมดอายุ กรุณาล็อคอินใหม่');
        }

        // 3. บันทึก/อัปเดตข้อมูลการใช้งานลงในตาราง app_user_sessions
        try {
            DB::connection('mysql')->table('app_user_sessions')->updateOrInsert(
                ['session_id' => Session::getId()],
                [
                    'username' => Session::get('user.username'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_page' => $request->fullUrl(),
                    'last_activity' => $now,
                    'updated_at' => $now
                ]
            );
        } catch (\Exception $e) {
            // ข้ามไปหากตารางยังไม่ถูกสร้าง
        }

        // อัปเดตเวลาการใช้งานล่าสุดใน session
        Session::put('user.last_activity', $now);

        return $next($request);
    }
}
