<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function userManagement()
    {
        // users
        $users = DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->get();

        $account_roles = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->get()
            ->keyBy('username');

        $users = $users->map(function ($user) use ($account_roles) {
            $role = $account_roles->get($user->username); //match username

            $user->role_id = $role->role_id ?? null;
            $user->role_name = $role->name ?? '';
            $user->has_role = $role ? 1 : 0; // มี role = 1, ไม่มี = 0

            return $user;
        });

        $users = $users->filter(function ($user) {
            return $user->has_role === 1; // กรองเฉพาะผู้ใช้ที่มี role เท่านั้น
        });

        $users = $users->sortByDesc('has_role')->values();

        // roles
        $roles = DB::connection('mysql')
            ->table('role')
            ->get();

        return view('admin.management', compact('users', 'roles'));
    }
    public function findUser(Request $request)
    {
        $search = $request->input('search');

        // ดึง roles จาก MySQL
        $roles = DB::connection('mysql')
            ->table('role')
            ->get();

        // ดึง account_role มาจับคู่กับ users
        $account_roles = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->get()
            ->keyBy('username');

        // ค้นหาผู้ใช้จาก SQL Server
        $users = DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->where('username', 'LIKE', '%' . $search . '%')
            ->orWhere('fname', 'LIKE', '%' . $search . '%')
            ->orWhere('lname', 'LIKE', '%' . $search . '%')
            ->orderBy('cid', 'asc')
            ->limit(10)
            ->get();

        // แมพ role ให้ users
        $users = $users->map(function ($user) use ($account_roles) {
            $role = $account_roles->get($user->username); // match username
            $user->role_id = $role->role_id ?? null;
            $user->role_name = $role->name ?? '';
            return $user;
        });

        return view('admin.management', compact('users', 'roles'));
    }
    // กำหนดสิทธิ์
    public function setRole(Request $request, $username)
    {
        $request->validate([
            'role' => 'required|exists:role,id',
        ]);

        // เช็คว่าผู้ใช้มีสิทธิ์อยู่แล้วหรือไม่
        $existing =  DB::connection('mysql')
            ->table('account_role')
            ->where('username', $username)
            ->get();

        if ($existing->isNotEmpty()) {
            DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->update([
                    'role_id' => $request->role,
                ]);
            return response()->json(['success' => 'กำหนดสิทธิ์สำเร็จ']);
        }

        // เพิ่มสิทธิ์ใหม่
        DB::connection('mysql')
            ->table('account_role')
            ->insert([
                'username' => $username,
                'role_id' => $request->role,
            ]);

        return response()->json(['success' => 'กำหนดสิทธิ์สำเร็จ']);
    }
    // ลบผู้ใช้
    public function destroyUser($username)
    {
        try {
            //ตรวจสอบว่าผู้ใช้มีบัญชีในตาราง account หรือไม่
            $hasRole = DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->exists();

            if (!$hasRole) {
                return response()->json(['error' => 'ไม่พบผู้ใช้ในระบบ'], 404);
            }

            // ลบจากตาราง account_role ก่อน
            DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->delete();

            return redirect()->route('admin')->with('success', 'ลบผู้ใช้สำเร็จ');
        } catch (\Exception $e) {
            return response()->json(['error' => 'เกิดข้อผิดพลาดในการลบผู้ใช้: ' . $e->getMessage()], 500);
        }
    }
    // จัดการศสิทธิ์
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:role,name',
        ]);

        $insert = DB::table('role')->insert([
            'name' => $request->name,
        ]);

        if ($insert) {
            return redirect()->route('admin.management')->with('success', 'เพิ่มสิทธิ์สำเร็จ');
        } else {
            return redirect()->route('admin.management')->with('error', 'เพิ่มสิทธิ์ไม่สำเร็จ');
        }
    }

    public function destroyRole($id)
    {
        try {
            // ตรวจสอบว่ามีการใช้งานสิทธิ์นี้ในตาราง account_role หรือไม่
            $isUsed = DB::connection('mysql')
                ->table('account_role')
                ->where('role_id', $id)
                ->exists();

            if ($isUsed) {
                return redirect()->route('admin')->with('error', 'ไม่สามารถลบสิทธิ์นี้ได้ เนื่องจากมีการใช้งานอยู่');
            }

            // ลบสิทธิ์
            DB::connection('mysql')
                ->table('role')
                ->where('id', $id)
                ->delete();

            return redirect()->route('admin.management')->with('success', 'ลบสิทธิ์สำเร็จ');
        } catch (\Exception $e) {
            return redirect()->route('admin.management')->with('error', 'เกิดข้อผิดพลาดในการลบสิทธิ์: ' . $e->getMessage());
        }
    }

    public function notificationSettings()
    {
        $pmData = DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->select('username', 'tname', 'fname', 'lname', 'position', 'position2')
            ->get();

        $pmMap = $pmData->mapWithKeys(function ($item) {
            return [
                $item->username => [
                    'full_name' =>  $item->tname . ' ' . $item->fname . ' ' . $item->lname,
                    'position' => $item->position . $item->position2 ?? '',
                ]
            ];
        })->toArray();

        $subscribers = DB::connection('mysql')
            ->table('telegram_subscribers')
            ->get();

        $subscribers->transform(function ($subscriber) use ($pmMap) {
            $subscriber->fullName = $pmMap[$subscriber->pm]['full_name'] ?? 'ไม่ระบุ';
            $subscriber->position = $pmMap[$subscriber->pm]['position'] ?? '';
            return $subscriber;
        });


        return view('admin.notifySettings', compact('subscribers'));
    }

    public function updateNotificationStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer'
            ]);

            // ดึงข้อมูลปัจจุบัน
            $subscriber = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('id', $request->id)
                ->first();

            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'error' => 'ไม่พบข้อมูล Subscriber'
                ], 404);
            }

            // Toggle สถานะ (แปลง boolean)
            $newStatus = !$subscriber->allowed;

            // Update ด้วย Query Builder
            DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('id', $request->id)
                ->update([
                    'allowed' => $newStatus,
                    'updated_at' => now() // ถ้ามี column นี้
                ]);

            return response()->json([
                'success' => true,
                'allowed' => $newStatus,
                'message' => $newStatus ? 'เปิดการแจ้งเตือนเรียบร้อยแล้ว' : 'ปิดการแจ้งเตือนเรียบร้อยแล้ว'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'ข้อมูลไม่ถูกต้อง'
            ], 422);
        } catch (\Exception $e) {
            // Log error เพื่อ debug
            Log::error('Update Notification Status Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyNotify($id)
    {
        try {
            // ตรวจสอบว่าผู้ใช้มีบัญชีในตาราง telegram_subscribers หรือไม่
            $subscriber = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('id', $id)
                ->first();

            if (!$subscriber) {
                return response()->json(['error' => 'ไม่พบผู้ติดตามในระบบ'], 404);
            }

            // ลบข้อมูล
            DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('id', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบผู้ติดตามสำเร็จ'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการลบผู้ติดตาม: ' . $e->getMessage()
            ], 500);
        }
    }
}
