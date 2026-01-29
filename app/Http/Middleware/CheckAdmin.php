<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user') || session('user.logged_in') !== true) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        // ตรวจสอบสิทธิ์ล่าสุดจากฐานข้อมูล (Real-time Guard)
        $currentRole = DB::connection('mysql')->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->where('account_role.username', session('user.username'))
            ->value('name');

        if ($currentRole !== 'Admin') {
            // อัปเดต session ให้เป็นค่าปัจจุบัน (เผื่อกรณีเขายังมีสิทธิ์อื่นอยู่แต่ไม่ใช่ Admin)
            if ($currentRole) {
                session(['user.role' => $currentRole]);
            }
            abort(403, 'คุณไม่มีสิทธิ์เข้าใช้งานหน้านี้');
        }

        return $next($request);
    }
}
