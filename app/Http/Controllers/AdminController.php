<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function userManagement(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $accountRoles = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->select('account_role.username', 'account_role.role_id', 'role.name as role_name')
            ->get()
            ->keyBy('username');
        $activeSessions = $this->activeSessions();
        $roles = DB::connection('mysql')->table('role')->orderBy('name')->get();

        if ($search !== '') {
            $directoryUsers = $this->directorySearch($search, 200);
            $matchedUsers = $this->attachRoles($directoryUsers, $accountRoles)
                ->filter(fn ($user) => $user->role_id !== null)
                ->values();
            $users = $this->paginateCollection($matchedUsers, $request, 25);
        } else {
            $users = $this->assignedUsersPage($accountRoles, $request, 25);
        }

        return view('admin.management', $this->managementViewData(
            $users,
            $roles,
            $activeSessions,
            $accountRoles,
            false
        ));
    }

    public function findUser(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $roles = DB::connection('mysql')->table('role')->orderBy('name')->get();
        $accountRoles = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->select('account_role.username', 'account_role.role_id', 'role.name as role_name')
            ->get()
            ->keyBy('username');
        $activeSessions = $this->activeSessions();
        $users = mb_strlen($search) >= 2
            ? $this->attachRoles($this->directorySearch($search, 50), $accountRoles)
            : collect();

        return view('admin.management', $this->managementViewData(
            $users,
            $roles,
            $activeSessions,
            $accountRoles,
            true
        ));
    }

    private function directorySearch(string $search, int $limit)
    {
        return DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->where(function ($query) use ($search) {
                $query->where('username', 'LIKE', '%'.$search.'%')
                    ->orWhere('fname', 'LIKE', '%'.$search.'%')
                    ->orWhere('lname', 'LIKE', '%'.$search.'%');
            })
            ->orderBy('fname')
            ->orderBy('lname')
            ->limit($limit)
            ->get();
    }

    private function attachRoles($users, $accountRoles)
    {
        return $users->map(function ($user) use ($accountRoles) {
            $assignment = $accountRoles->get($user->username);
            $user->role_id = $assignment->role_id ?? null;
            $user->role_name = $assignment->role_name ?? '';

            return $user;
        });
    }

    private function assignedUsersPage($accountRoles, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, LengthAwarePaginator::resolveCurrentPage());
        $assignments = $accountRoles->sortBy('username')->values();
        $pageAssignments = $assignments->forPage($page, $perPage)->values();
        $directoryUsers = collect();

        foreach ($pageAssignments->pluck('username')->chunk(500) as $usernames) {
            $directoryUsers = $directoryUsers->concat(
                DB::connection('sqlsrv2')->table('vwUserInfo')->whereIn('username', $usernames->all())->get()
            );
        }

        $directoryByUsername = $directoryUsers->keyBy('username');
        $mapped = $pageAssignments->map(function ($assignment) use ($directoryByUsername) {
            $user = $directoryByUsername->get($assignment->username) ?? (object) [
                'username' => $assignment->username,
                'tname' => '',
                'fname' => $assignment->username,
                'lname' => '',
                'position' => null,
            ];
            $user->role_id = $assignment->role_id;
            $user->role_name = $assignment->role_name;

            return $user;
        });

        return new LengthAwarePaginator(
            $mapped,
            $assignments->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function paginateCollection($items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, LengthAwarePaginator::resolveCurrentPage());

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function activeSessions()
    {
        if (! Schema::connection('mysql')->hasTable('app_user_sessions')) {
            return collect();
        }

        return DB::connection('mysql')
            ->table('app_user_sessions')
            ->where('last_activity', '>=', now()->subMinutes(5))
            ->get()
            ->keyBy('username');
    }

    private function managementViewData($users, $roles, $activeSessions, $accountRoles, bool $directoryMode): array
    {
        $roleUsageCounts = $accountRoles->groupBy('role_id')->map->count();
        $userStats = [
            'total' => $accountRoles->count(),
            'admins' => $accountRoles->where('role_name', 'Admin')->count(),
            'online' => $activeSessions->keys()->intersect($accountRoles->keys())->count(),
            'roles' => $roles->count(),
        ];

        return compact(
            'users',
            'roles',
            'activeSessions',
            'roleUsageCounts',
            'userStats',
            'directoryMode'
        ) + ['recentAuditLogs' => $this->recentAccessAuditLogs()];
    }
    // กำหนดสิทธิ์
    public function setRole(Request $request, $username)
    {
        $request->validate([
            'role' => 'required|exists:role,id',
        ]);

        // เช็คว่าผู้ใช้มีสิทธิ์อยู่แล้วหรือไม่
        $existing = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
            ->where('username', $username)
            ->select('account_role.role_id', 'role.name as role_name')
            ->first();

        $newRole = DB::connection('mysql')->table('role')->where('id', $request->integer('role'))->first();

        if ($existing) {
            DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->update([
                    'role_id' => $request->role,
                ]);
        } else {
            DB::connection('mysql')
                ->table('account_role')
                ->insert([
                    'username' => $username,
                    'role_id' => $request->integer('role'),
                ]);
        }

        AuditLogger::record($request, $existing ? 'access.role_changed' : 'access.role_assigned',
            $existing ? 'เปลี่ยนบทบาทผู้ใช้งาน' : 'กำหนดบทบาทให้ผู้ใช้งาน', [
                'category' => 'access_control',
                'target_type' => 'user',
                'target_id' => $username,
                'old_values' => $existing ? ['role_id' => $existing->role_id, 'role_name' => $existing->role_name] : null,
                'new_values' => ['role_id' => $newRole->id, 'role_name' => $newRole->name],
            ]);

        return response()->json(['success' => 'กำหนดสิทธิ์สำเร็จ']);
    }
    // ลบผู้ใช้
    public function destroyUser(Request $request, $username)
    {
        try {
            //ตรวจสอบว่าผู้ใช้มีบัญชีในตาราง account หรือไม่
            $currentRole = DB::connection('mysql')
                ->table('account_role')
                ->leftJoin('role', 'account_role.role_id', '=', 'role.id')
                ->where('username', $username)
                ->select('account_role.role_id', 'role.name as role_name')
                ->first();

            if (! $currentRole) {
                AuditLogger::record($request, 'access.revocation_failed', 'ถอนสิทธิ์ผู้ใช้งานไม่สำเร็จ', [
                    'category' => 'access_control',
                    'result' => 'failed',
                    'target_type' => 'user',
                    'target_id' => $username,
                    'metadata' => ['reason' => 'role_not_found'],
                ]);
                return response()->json(['error' => 'ไม่พบผู้ใช้ในระบบ'], 404);
            }

            // ลบจากตาราง account_role ก่อน
            DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->delete();

            AuditLogger::record($request, 'access.revoked', 'ถอนสิทธิ์เข้าใช้งาน', [
                'category' => 'access_control',
                'target_type' => 'user',
                'target_id' => $username,
                'old_values' => ['role_id' => $currentRole->role_id, 'role_name' => $currentRole->role_name],
                'new_values' => ['role_id' => null, 'role_name' => null],
            ]);

            return response()->json(['success' => 'ลบผู้ใช้สำเร็จ']);
        } catch (\Exception $e) {
            AuditLogger::record($request, 'access.revocation_failed', 'ถอนสิทธิ์ผู้ใช้งานไม่สำเร็จ', [
                'category' => 'access_control',
                'result' => 'failed',
                'target_type' => 'user',
                'target_id' => $username,
                'metadata' => ['exception' => $e::class],
            ]);
            Log::error('Unable to revoke user access.', ['username' => $username, 'exception' => $e]);

            return response()->json(['error' => 'เกิดข้อผิดพลาดในการถอนสิทธิ์ผู้ใช้'], 500);
        }
    }
    // จัดการศสิทธิ์
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:role,name',
        ]);

        $roleId = DB::connection('mysql')->table('role')->insertGetId([
            'name' => $request->name,
        ]);

        if ($roleId) {
            AuditLogger::record($request, 'access.role_created', 'เพิ่มบทบาทใหม่', [
                'category' => 'access_control',
                'target_type' => 'role',
                'target_id' => (string) $roleId,
                'new_values' => ['id' => $roleId, 'name' => $request->name],
            ]);
            return redirect()->route('admin.management')->with('success', 'เพิ่มสิทธิ์สำเร็จ');
        } else {
            return redirect()->route('admin.management')->with('error', 'เพิ่มสิทธิ์ไม่สำเร็จ');
        }
    }

    public function destroyRole(Request $request, $id)
    {
        try {
            $role = DB::connection('mysql')->table('role')->where('id', $id)->first();

            if (! $role) {
                AuditLogger::record($request, 'access.role_deletion_failed', 'ลบบทบาทไม่สำเร็จ', [
                    'category' => 'access_control',
                    'result' => 'failed',
                    'target_type' => 'role',
                    'target_id' => (string) $id,
                    'metadata' => ['reason' => 'role_not_found'],
                ]);

                return response()->json(['error' => 'ไม่พบบทบาทที่ต้องการลบ'], 404);
            }

            // ตรวจสอบว่ามีการใช้งานสิทธิ์นี้ในตาราง account_role หรือไม่
            $isUsed = DB::connection('mysql')
                ->table('account_role')
                ->where('role_id', $id)
                ->exists();

            if ($isUsed) {
                AuditLogger::record($request, 'access.role_deletion_denied', 'ปฏิเสธการลบบทบาทที่ยังมีผู้ใช้งาน', [
                    'category' => 'access_control',
                    'result' => 'denied',
                    'target_type' => 'role',
                    'target_id' => (string) $id,
                    'old_values' => ['id' => $role->id, 'name' => $role->name],
                    'metadata' => ['reason' => 'role_in_use'],
                ]);
                return response()->json(['error' => 'ไม่สามารถลบสิทธิ์นี้ได้ เนื่องจากมีการใช้งานอยู่'], 422);
            }

            // ลบสิทธิ์
            DB::connection('mysql')
                ->table('role')
                ->where('id', $id)
                ->delete();

            AuditLogger::record($request, 'access.role_deleted', 'ลบบทบาทออกจากระบบ', [
                'category' => 'access_control',
                'target_type' => 'role',
                'target_id' => (string) $id,
                'old_values' => ['id' => $role->id, 'name' => $role->name],
            ]);

            return response()->json(['success' => 'ลบสิทธิ์สำเร็จ']);
        } catch (\Exception $e) {
            AuditLogger::record($request, 'access.role_deletion_failed', 'ลบบทบาทไม่สำเร็จ', [
                'category' => 'access_control',
                'result' => 'failed',
                'target_type' => 'role',
                'target_id' => (string) $id,
                'metadata' => ['exception' => $e::class],
            ]);
            Log::error('Unable to delete role.', ['role_id' => $id, 'exception' => $e]);

            return response()->json(['error' => 'เกิดข้อผิดพลาดในการลบบทบาท'], 500);
        }
    }

    private function recentAccessAuditLogs()
    {
        try {
            if (! Schema::connection('mysql')->hasTable('system_audit_logs')) {
                return collect();
            }

            return DB::connection('mysql')
                ->table('system_audit_logs')
                ->where('category', 'access_control')
                ->latest('occurred_at')
                ->limit(8)
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Unable to load access audit logs.', ['exception' => $exception::class]);

            return collect();
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
