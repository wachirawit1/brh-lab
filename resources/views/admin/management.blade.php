@extends('layout.app')
@section('title', 'จัดการผู้ใช้และสิทธิ์ - ระบบแจ้งเตือนผลแล็บ')

@section('content')
    @php
        $directoryMode = $directoryMode ?? false;
        $totalUsers = $userStats['total'] ?? $users->count();
        $totalAdmins = $userStats['admins'] ?? $users->where('role_name', 'Admin')->count();
        $onlineUsers = $userStats['online'] ?? $users->filter(fn ($user) => isset($activeSessions[$user->username]))->count();
        $totalRoles = $userStats['roles'] ?? $roles->count();
        $emptyTitle = $directoryMode && mb_strlen((string) request('search')) < 2
            ? 'พิมพ์อย่างน้อย 2 ตัวอักษรเพื่อค้นหาบุคลากร'
            : ($directoryMode ? 'ไม่พบบุคลากรที่ค้นหา' : 'ไม่พบผู้มีสิทธิ์ในระบบ');
    @endphp

    <style>
        .permission-filter[aria-pressed="true"] { background: var(--brand-solid); border-color: var(--brand-solid); color: var(--brand-on-solid); }
        .permission-filter[aria-pressed="false"] { background: var(--bg-surface); border-color: var(--border-color); color: var(--text-secondary); }
        .permission-scope[aria-pressed="true"] { background: var(--bg-surface); color: var(--brand-text); box-shadow: 0 1px 2px rgb(15 23 42 / .08); }
        .permission-scope[aria-pressed="false"] { color: var(--text-secondary); }
        .permission-entry[hidden] { display: none !important; }
        @media (prefers-reduced-motion: no-preference) {
            .permission-panel { animation: permission-panel-in 320ms cubic-bezier(.16, 1, .3, 1) both; }
            @keyframes permission-panel-in { from { opacity: .7; transform: translateY(8px); } }
        }
    </style>

    <main class="mx-auto max-w-[1440px] px-2 pb-8 md:px-6">
        <nav class="mb-4 flex text-xs text-gray-500" aria-label="เส้นทางนำทาง">
            <ol class="inline-flex items-center gap-1.5">
                <li><a href="{{ route('amr.index') }}" class="font-medium hover:text-brand-600">หน้าแรก</a></li>
                <li class="flex items-center gap-1.5" aria-current="page">
                    <i class="fa-solid fa-chevron-right text-xs text-gray-400" aria-hidden="true"></i>
                    <span class="font-semibold text-gray-700">ผู้ใช้และสิทธิ์</span>
                </li>
            </ol>
        </nav>

        <header class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <div class="mb-2 flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-lg text-white shadow-sm" aria-hidden="true">
                        <i class="fa-solid fa-users-gear"></i>
                    </span>
                    <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 md:text-3xl">จัดการผู้ใช้และสิทธิ์</h1>
                </div>
                <p class="max-w-2xl text-sm leading-6 text-gray-600">ค้นหาบุคลากร กำหนดขอบเขตการเข้าถึง และตรวจสอบผู้ที่กำลังใช้งานระบบจากหน้าจอเดียว</p>
            </div>
            <button type="button" data-open-modal="addRoleModal"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>เพิ่มบทบาทใหม่
            </button>
        </header>

        <section class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" aria-label="ภาพรวมสิทธิ์ผู้ใช้">
            <div class="grid grid-cols-2 divide-x divide-y divide-gray-200 sm:grid-cols-4 sm:divide-y-0">
                @foreach ([
                    ['fa-user-group', 'text-brand-600', 'ผู้มีสิทธิ์ทั้งหมด', $totalUsers],
                    ['fa-user-shield', 'text-indigo-600', 'ผู้ดูแลระบบ', $totalAdmins],
                    ['fa-signal', 'text-emerald-600', 'ออนไลน์ขณะนี้', $onlineUsers],
                    ['fa-layer-group', 'text-amber-600', 'บทบาทในระบบ', $totalRoles],
                ] as [$icon, $color, $label, $value])
                    <div class="flex items-center gap-3 p-4 md:px-5">
                        <i class="fa-solid {{ $icon }} {{ $color }}" aria-hidden="true"></i>
                        <div><p class="text-xs font-medium text-gray-500">{{ $label }}</p><p class="text-xl font-extrabold text-gray-900">{{ number_format($value) }}</p></div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <section class="permission-panel min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" aria-labelledby="user-list-title">
                <div class="border-b border-gray-200 p-4 md:p-5">
                    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 id="user-list-title" class="text-lg font-bold text-gray-900">รายชื่อผู้ใช้งาน</h2>
                            <p class="mt-0.5 text-xs text-gray-500">รายการเริ่มต้นแสดงผู้ที่ได้รับสิทธิ์แล้ว ค้นหาเพื่อเพิ่มบุคลากรคนใหม่</p>
                        </div>
                        <p class="text-xs font-semibold text-gray-500" aria-live="polite">แสดง <span id="visibleUserCount" class="text-gray-900">{{ $totalUsers }}</span> รายการ</p>
                    </div>
                    <div class="mb-4 inline-flex w-full rounded-xl bg-gray-100 p-1 sm:w-auto" aria-label="ขอบเขตการค้นหา">
                        <button type="button" class="permission-scope min-h-10 flex-1 rounded-lg px-4 text-xs font-bold transition sm:flex-none" data-user-scope="assigned" aria-pressed="{{ $directoryMode ? 'false' : 'true' }}">
                            <i class="fa-solid fa-user-check mr-1.5" aria-hidden="true"></i>ผู้มีสิทธิ์ในระบบ
                        </button>
                        <button type="button" class="permission-scope min-h-10 flex-1 rounded-lg px-4 text-xs font-bold transition sm:flex-none" data-user-scope="directory" aria-pressed="{{ $directoryMode ? 'true' : 'false' }}">
                            <i class="fa-solid fa-address-book mr-1.5" aria-hidden="true"></i>ค้นหาบุคลากรเพื่อเพิ่ม
                        </button>
                    </div>
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <label class="relative block w-full lg:max-w-md">
                            <span class="sr-only">ค้นหาผู้ใช้</span>
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400" aria-hidden="true"></i>
                            <input id="userSearch" type="search" name="search" autocomplete="off"
                                class="min-h-11 w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                                placeholder="{{ $directoryMode ? 'พิมพ์อย่างน้อย 2 ตัวอักษร' : 'ค้นหาเฉพาะผู้มีสิทธิ์ในระบบ' }}" value="{{ request('search') }}">
                            <span id="searchSpinner" class="pointer-events-none absolute right-3.5 top-1/2 hidden -translate-y-1/2 text-brand-600" aria-hidden="true"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                        </label>
                        <div class="flex gap-2 overflow-x-auto pb-1 lg:pb-0" aria-label="กรองรายชื่อผู้ใช้">
                            <button type="button" class="permission-filter min-h-10 shrink-0 rounded-lg border px-3.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-brand-500" data-user-filter="all" aria-pressed="true">ทั้งหมด</button>
                            <button type="button" class="permission-filter min-h-10 shrink-0 rounded-lg border px-3.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-brand-500" data-user-filter="online" aria-pressed="false"><span class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>ออนไลน์</button>
                            <button type="button" class="permission-filter min-h-10 shrink-0 rounded-lg border px-3.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-brand-500" data-user-filter="admin" aria-pressed="false">ผู้ดูแลระบบ</button>
                            <button type="button" class="permission-filter js-directory-only min-h-10 shrink-0 rounded-lg border px-3.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-brand-500 {{ $directoryMode ? '' : 'hidden' }}" data-user-filter="unassigned" aria-pressed="false">ยังไม่มีสิทธิ์</button>
                        </div>
                    </div>
                    <p id="searchModeHint" class="mt-2 text-xs text-gray-500">
                        {{ $directoryMode ? 'ค้นหาจากทะเบียนบุคลากรโรงพยาบาลเพื่อกำหนดสิทธิ์คนใหม่' : 'ค้นหาเฉพาะบัญชีที่ได้รับสิทธิ์เข้าใช้ระบบนี้แล้ว' }}
                    </p>
                </div>

                <div class="user-result" aria-live="polite" aria-busy="false">
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-bold text-gray-600">
                                <tr><th class="px-5 py-3 text-left">ผู้ใช้งาน</th><th class="px-4 py-3 text-left">ตำแหน่ง</th><th class="px-4 py-3 text-left">บทบาท</th><th class="px-4 py-3 text-left">สถานะ</th><th class="px-5 py-3"><span class="sr-only">จัดการ</span></th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($users as $user)
                                    @php
                                        $fullName = trim($user->tname . $user->fname . ' ' . $user->lname);
                                        $isOnline = isset($activeSessions[$user->username]);
                                        $isAdmin = $user->role_name === 'Admin';
                                    @endphp
                                    <tr class="permission-entry js-user-row transition-colors hover:bg-sky-50/40" data-filter-online="{{ $isOnline ? 1 : 0 }}" data-filter-admin="{{ $isAdmin ? 1 : 0 }}" data-filter-unassigned="{{ $user->role_name ? 0 : 1 }}">
                                        <td class="px-5 py-4"><div class="flex min-w-[210px] items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 font-bold text-brand-700 ring-1 ring-brand-200">{{ mb_substr($user->fname, 0, 1) }}</span><span class="min-w-0"><span class="block truncate font-bold text-gray-900">{{ $fullName }}</span><span class="block truncate text-xs text-gray-500">{{ '@' . $user->username }}</span></span></div></td>
                                        <td class="max-w-[220px] px-4 py-4 text-gray-600">{{ $user->position ?: 'ไม่ระบุตำแหน่ง' }}</td>
                                        <td class="px-4 py-4">
                                            @if ($user->role_name)
                                                <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-bold {{ $isAdmin ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-sky-200 bg-sky-50 text-sky-700' }}"><i class="fa-solid {{ $isAdmin ? 'fa-user-shield' : 'fa-id-badge' }}"></i>{{ $user->role_name }}</span>
                                            @else
                                                <span class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">ยังไม่มีสิทธิ์</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1.5 text-xs {{ $isOnline ? 'font-bold text-emerald-700' : 'font-medium text-gray-500' }}">
                                                <span class="h-2 w-2 rounded-full {{ $isOnline ? 'bg-emerald-500' : 'bg-gray-300' }}" aria-hidden="true"></span>
                                                {{ $isOnline ? 'ออนไลน์' : 'ออฟไลน์' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">@include('admin.partials.user-role-button', ['compact' => false])</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-16 text-center"><i class="fa-solid fa-user-slash mb-3 text-3xl text-gray-300"></i><p class="font-bold text-gray-700">{{ $emptyTitle }}</p><p class="mt-1 text-xs text-gray-500">ค้นหาได้จากชื่อ นามสกุล หรือชื่อผู้ใช้</p></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="divide-y divide-gray-100 md:hidden">
                        @forelse($users as $user)
                            @php
                                $fullName = trim($user->tname . $user->fname . ' ' . $user->lname);
                                $isOnline = isset($activeSessions[$user->username]);
                                $isAdmin = $user->role_name === 'Admin';
                            @endphp
                            <article class="permission-entry p-4" data-filter-online="{{ $isOnline ? 1 : 0 }}" data-filter-admin="{{ $isAdmin ? 1 : 0 }}" data-filter-unassigned="{{ $user->role_name ? 0 : 1 }}">
                                <div class="mb-3 flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 font-bold text-brand-700 ring-1 ring-brand-200">{{ mb_substr($user->fname, 0, 1) }}</span>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate font-bold text-gray-900">{{ $fullName }}</h3>
                                        <p class="truncate text-xs text-gray-500">{{ '@' . $user->username }} · {{ $user->position ?: 'ไม่ระบุตำแหน่ง' }}</p>
                                    </div>
                                    <span class="mt-1 h-2 w-2 rounded-full {{ $isOnline ? 'bg-emerald-500' : 'bg-gray-300' }}" title="{{ $isOnline ? 'ออนไลน์' : 'ออฟไลน์' }}"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    @if ($user->role_name)<span class="inline-flex rounded-lg border px-2.5 py-1 text-xs font-bold {{ $isAdmin ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-sky-200 bg-sky-50 text-sky-700' }}">{{ $user->role_name }}</span>@else<span class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">ยังไม่มีสิทธิ์</span>@endif
                                    @include('admin.partials.user-role-button', ['compact' => true])
                                </div>
                            </article>
                        @empty
                            <div class="px-6 py-14 text-center"><i class="fa-solid fa-user-slash mb-3 text-3xl text-gray-300"></i><p class="font-bold text-gray-700">{{ $emptyTitle }}</p></div>
                        @endforelse
                    </div>
                    <div id="filterEmptyState" class="hidden px-6 py-14 text-center"><i class="fa-solid fa-filter-circle-xmark mb-3 text-3xl text-gray-300"></i><p class="font-bold text-gray-700">ไม่มีผู้ใช้ในตัวกรองนี้</p><button type="button" class="mt-2 text-sm font-bold text-brand-700 hover:underline" data-user-filter="all">แสดงผู้ใช้ทั้งหมด</button></div>
                    @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
                        <div class="border-t border-gray-200 px-4 py-4">{{ $users->onEachSide(1)->links() }}</div>
                    @endif
                </div>
            </section>

            <aside class="permission-panel overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm xl:sticky xl:top-24" aria-labelledby="role-list-title">
                <div class="flex items-start justify-between gap-3 border-b border-gray-200 p-5"><div><h2 id="role-list-title" class="font-bold text-gray-900">บทบาทในระบบ</h2><p class="mt-1 text-xs leading-5 text-gray-500">บทบาทกำหนดขอบเขตที่ผู้ใช้เข้าถึงได้</p></div><button type="button" data-open-modal="addRoleModal" aria-label="เพิ่มบทบาทใหม่" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-500"><i class="fa-solid fa-plus"></i></button></div>
                <div class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                        @php $roleUserCount = ($roleUsageCounts ?? collect())->get($role->id, $users->where('role_name', $role->name)->count()); @endphp
                        <div class="flex items-center gap-3 px-5 py-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $role->name === 'Admin' ? 'bg-indigo-50 text-indigo-700' : 'bg-sky-50 text-sky-700' }}"><i class="fa-solid {{ $role->name === 'Admin' ? 'fa-user-shield' : 'fa-id-badge' }}"></i></span>
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold text-gray-800">{{ $role->name }}</p><p class="text-xs text-gray-500">ใช้งาน {{ number_format($roleUserCount) }} คน</p></div>
                            @if ($roleUserCount > 0)
                                <span class="flex h-9 w-9 items-center justify-center text-gray-300" title="ยังลบไม่ได้เพราะมีผู้ใช้บทบาทนี้"><i class="fa-solid fa-lock text-xs"></i></span>
                            @else
                                <button type="button" class="delete-role-btn flex h-9 w-9 items-center justify-center rounded-lg text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}" aria-label="ลบบทบาท {{ $role->name }}"><i class="fa-solid fa-trash-can text-xs"></i></button>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-gray-500">ยังไม่มีบทบาทในระบบ</div>
                    @endforelse
                </div>
                <div class="border-t border-gray-200 bg-gray-50 px-5 py-4 text-xs leading-5 text-gray-500"><i class="fa-solid fa-circle-info mr-1 text-brand-600"></i>ต้องถอนผู้ใช้ออกจากบทบาทก่อน จึงจะลบบทบาทนั้นได้</div>

                <div class="border-t border-gray-200">
                    <div class="px-5 pb-3 pt-5">
                        <h2 class="font-bold text-gray-900">กิจกรรมสิทธิ์ล่าสุด</h2>
                        <p class="mt-1 text-xs text-gray-500">หลักฐานการเปลี่ยนแปลงสำหรับตรวจสอบย้อนหลัง</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse(($recentAuditLogs ?? collect()) as $auditLog)
                            <div class="px-5 py-3.5">
                                <div class="flex items-start gap-2.5">
                                    <span class="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $auditLog->result === 'success' ? 'bg-emerald-50 text-emerald-700' : ($auditLog->result === 'denied' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                        <i class="fa-solid {{ $auditLog->result === 'success' ? 'fa-check' : 'fa-triangle-exclamation' }} text-xs" aria-hidden="true"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold leading-5 text-gray-800">{{ $auditLog->action }}</p>
                                        <p class="truncate text-xs text-gray-500">
                                            {{ $auditLog->actor_username ?: 'ผู้ใช้ไม่ระบุ' }}
                                            @if ($auditLog->target_id) · {{ $auditLog->target_id }} @endif
                                        </p>
                                        <time class="mt-0.5 block text-xs text-gray-400" datetime="{{ $auditLog->occurred_at }}">
                                            {{ \Carbon\Carbon::parse($auditLog->occurred_at)->format('d/m/Y H:i') }} น.
                                        </time>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-7 text-center">
                                <i class="fa-solid fa-clock-rotate-left mb-2 text-xl text-gray-300" aria-hidden="true"></i>
                                <p class="text-xs font-medium text-gray-500">ยังไม่มีประวัติ หรือยังไม่ได้ติดตั้งตาราง Audit Log</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <div id="editUserRoleModal" role="dialog" aria-modal="true" aria-labelledby="editUserRoleTitle" class="fixed inset-0 z-50 hidden overflow-y-auto p-4 sm:p-6">
        <div class="fixed inset-0 bg-gray-900/60" data-close-modal="editUserRoleModal"></div>
        <div class="relative z-10 mx-auto flex min-h-full max-w-lg items-center justify-center">
            <div class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 sm:px-6"><div><h2 id="editUserRoleTitle" class="text-lg font-bold text-gray-900">กำหนดสิทธิ์ผู้ใช้งาน</h2><p class="mt-1 text-xs text-gray-500">เลือกบทบาทที่เหมาะกับหน้าที่ของบุคลากร</p></div><button type="button" data-close-modal="editUserRoleModal" aria-label="ปิดหน้าต่าง" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500"><i class="fa-solid fa-xmark"></i></button></div>
                <div class="px-5 py-5 sm:px-6">
                    <div class="mb-5 flex items-center gap-3 rounded-xl bg-gray-50 p-4"><span id="editUserAvatar" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white"></span><div class="min-w-0"><p id="editUserName" class="truncate font-bold text-gray-900"></p><p id="editUserMeta" class="truncate text-xs text-gray-500"></p></div></div>
                    <form id="editUserRoleForm" method="POST" class="set-role-form">@csrf
                        <label for="editUserRoleSelect" class="mb-2 block text-sm font-bold text-gray-700">บทบาทที่อนุญาต</label>
                        <select id="editUserRoleSelect" name="role" required class="min-h-11 w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20"><option value="" disabled>เลือกบทบาท</option>@foreach ($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select>
                        <p class="mt-2 text-xs leading-5 text-gray-500">การเปลี่ยนแปลงจะมีผลกับการเข้าใช้งานครั้งถัดไปของผู้ใช้</p>
                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" data-close-modal="editUserRoleModal" class="min-h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 hover:bg-gray-50">ยกเลิก</button><button type="submit" class="min-h-11 rounded-xl bg-brand-600 px-5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">บันทึกสิทธิ์</button></div>
                    </form>
                    <div id="removeAccessSection" class="mt-6 hidden border-t border-gray-200 pt-5"><div class="flex flex-col gap-3 rounded-xl border border-red-200 bg-red-50 p-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-bold text-red-800">ถอนสิทธิ์เข้าใช้งาน</p><p class="mt-0.5 text-xs leading-5 text-red-700">บัญชีบุคลากรยังคงอยู่ แต่จะเข้าใช้ระบบนี้ไม่ได้</p></div><form id="removeUserAccessForm" method="POST" class="destroy-user-form shrink-0">@csrf @method('DELETE')<button type="submit" class="min-h-10 rounded-lg border border-red-300 bg-white px-3.5 text-xs font-bold text-red-700 hover:bg-red-600 hover:text-white">ถอนสิทธิ์</button></form></div></div>
                </div>
            </div>
        </div>
    </div>

    <div id="addRoleModal" role="dialog" aria-modal="true" aria-labelledby="addRoleModalTitle" class="fixed inset-0 z-50 hidden overflow-y-auto p-4 sm:p-6">
        <div class="fixed inset-0 bg-gray-900/60" data-close-modal="addRoleModal"></div>
        <div class="relative z-10 mx-auto flex min-h-full max-w-md items-center justify-center">
            <form method="POST" action="{{ route('admin.roles.store') }}" class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl">@csrf
                <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4"><div><h2 id="addRoleModalTitle" class="text-lg font-bold text-gray-900">เพิ่มบทบาทใหม่</h2><p class="mt-1 text-xs text-gray-500">ตั้งชื่อให้สื่อถึงขอบเขตงานของผู้ใช้</p></div><button type="button" data-close-modal="addRoleModal" aria-label="ปิดหน้าต่าง" class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button></div>
                <div class="px-5 py-5"><label for="newRoleName" class="mb-2 block text-sm font-bold text-gray-700">ชื่อบทบาท</label><input id="newRoleName" type="text" name="name" required maxlength="100" autocomplete="off" class="min-h-11 w-full rounded-xl border border-gray-300 bg-white px-3.5 text-sm font-medium text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20" placeholder="เช่น เภสัชกร หรือ พยาบาลควบคุมการติดเชื้อ"><div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" data-close-modal="addRoleModal" class="min-h-11 rounded-xl border border-gray-300 bg-white px-4 text-sm font-bold text-gray-700 hover:bg-gray-50">ยกเลิก</button><button type="submit" class="min-h-11 rounded-xl bg-brand-600 px-5 text-sm font-bold text-white shadow-sm hover:bg-brand-700">เพิ่มบทบาท</button></div></div>
            </form>
        </div>
    </div>
@endsection

@push('managementScript')
    <script>
        (() => {
            let activeScope = @json($directoryMode ? 'directory' : 'assigned');
            let activeFilter = 'all';
            let lastModalTrigger = null;
            let searchTimer = null;
            let userRequestSequence = 0;
            const scrollKey = id => `dom-modal:${id}`;

            function openModal(id, trigger = null) {
                const modal = document.getElementById(id);
                if (!modal) return;
                lastModalTrigger = trigger || document.activeElement;
                modal.classList.remove('hidden');
                window.BRHModalScroll?.set(scrollKey(id), true);
                window.setTimeout(() => modal.querySelector('select, input, button')?.focus(), 20);
            }

            function closeModal(id) {
                const modal = document.getElementById(id);
                if (!modal) return;
                modal.classList.add('hidden');
                window.BRHModalScroll?.set(scrollKey(id), false);
                lastModalTrigger?.focus?.();
            }

            function applyFilter(filter = activeFilter) {
                activeFilter = filter;
                document.querySelectorAll('.permission-filter').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.userFilter === filter)));
                const rows = [...document.querySelectorAll('.js-user-row')];
                document.querySelectorAll('.permission-entry').forEach(entry => {
                    const key = `filter${filter.charAt(0).toUpperCase()}${filter.slice(1)}`;
                    entry.hidden = !(filter === 'all' || entry.dataset[key] === '1');
                });
                const visible = rows.filter(row => !row.hidden).length;
                const counter = document.getElementById('visibleUserCount');
                if (counter) counter.textContent = visible;
                document.getElementById('filterEmptyState')?.classList.toggle('hidden', visible > 0 || rows.length === 0);
            }

            function updateScopeUI() {
                document.querySelectorAll('[data-user-scope]').forEach(button => {
                    button.setAttribute('aria-pressed', String(button.dataset.userScope === activeScope));
                });

                const directoryMode = activeScope === 'directory';
                const input = document.getElementById('userSearch');
                const hint = document.getElementById('searchModeHint');
                if (input) input.placeholder = directoryMode ? 'พิมพ์อย่างน้อย 2 ตัวอักษร' : 'ค้นหาเฉพาะผู้มีสิทธิ์ในระบบ';
                if (hint) hint.textContent = directoryMode
                    ? 'ค้นหาจากทะเบียนบุคลากรโรงพยาบาลเพื่อกำหนดสิทธิ์คนใหม่'
                    : 'ค้นหาเฉพาะบัญชีที่ได้รับสิทธิ์เข้าใช้ระบบนี้แล้ว';
                document.querySelectorAll('.js-directory-only').forEach(element => element.classList.toggle('hidden', !directoryMode));
            }

            function loadUsers(query = '') {
                const requestId = ++userRequestSequence;
                const spinner = document.getElementById('searchSpinner');
                const result = document.querySelector('.user-result');
                const url = activeScope === 'directory'
                    ? @json(route('admin.findUser'))
                    : @json(route('admin.management'));

                spinner?.classList.remove('hidden');
                result?.setAttribute('aria-busy', 'true');
                $.get(url, { search: query, scope: activeScope }).done(data => {
                    if (requestId !== userRequestSequence) return;
                    $('.user-result').html($(data).find('.user-result').html());
                    applyFilter('all');
                }).fail(() => {
                    if (requestId === userRequestSequence) showToast('ค้นหารายชื่อไม่สำเร็จ กรุณาลองใหม่', 'danger');
                }).always(() => {
                    if (requestId !== userRequestSequence) return;
                    spinner?.classList.add('hidden');
                    document.querySelector('.user-result')?.setAttribute('aria-busy', 'false');
                });
            }

            function refreshUsers() {
                loadUsers(document.getElementById('userSearch')?.value.trim() || '');
            }

            document.addEventListener('click', event => {
                const open = event.target.closest('[data-open-modal]');
                if (open) openModal(open.dataset.openModal, open);
                const close = event.target.closest('[data-close-modal]');
                if (close) closeModal(close.dataset.closeModal);
                const filter = event.target.closest('[data-user-filter]');
                if (filter) applyFilter(filter.dataset.userFilter);
                const scope = event.target.closest('[data-user-scope]');
                if (scope && scope.dataset.userScope !== activeScope) {
                    clearTimeout(searchTimer);
                    activeScope = scope.dataset.userScope;
                    const input = document.getElementById('userSearch');
                    if (input) input.value = '';
                    updateScopeUI();
                    loadUsers();
                }
                const edit = event.target.closest('.js-edit-user');
                if (!edit) return;
                const roleId = edit.dataset.roleId || '';
                document.getElementById('editUserName').textContent = edit.dataset.name;
                document.getElementById('editUserMeta').textContent = `@${edit.dataset.username} · ${edit.dataset.position}`;
                document.getElementById('editUserAvatar').textContent = edit.dataset.name.trim().charAt(0);
                document.getElementById('editUserRoleForm').action = edit.dataset.updateAction;
                document.getElementById('removeUserAccessForm').action = edit.dataset.deleteAction;
                document.getElementById('editUserRoleSelect').value = roleId;
                document.getElementById('removeAccessSection').classList.toggle('hidden', !roleId);
                document.getElementById('editUserRoleTitle').textContent = roleId ? 'แก้ไขสิทธิ์ผู้ใช้งาน' : 'กำหนดสิทธิ์ผู้ใช้งาน';
                openModal('editUserRoleModal', edit);
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') document.querySelectorAll('[role="dialog"].fixed:not(.hidden)').forEach(modal => closeModal(modal.id));
            });

            document.getElementById('userSearch')?.addEventListener('input', event => {
                clearTimeout(searchTimer);
                const query = event.target.value.trim();
                const spinner = document.getElementById('searchSpinner');
                spinner?.classList.remove('hidden');
                searchTimer = setTimeout(() => {
                    loadUsers(query);
                }, 400);
            });

            $(document).on('submit', '.set-role-form', function(event) {
                event.preventDefault();
                const form = $(this), button = form.find('button[type="submit"]'), original = button.html();
                button.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>กำลังบันทึก');
                $.post(form.attr('action'), form.serialize()).done(response => {
                    showToast(response.success || 'บันทึกสิทธิ์เรียบร้อย', 'success');
                    closeModal('editUserRoleModal');
                    refreshUsers();
                }).fail(error => showToast(error.responseJSON?.error || 'บันทึกสิทธิ์ไม่สำเร็จ กรุณาตรวจสอบข้อมูล', 'danger')).always(() => button.prop('disabled', false).html(original));
            });

            $(document).on('submit', '.destroy-user-form', function(event) {
                event.preventDefault();
                const form = $(this), safeName = $('<div>').text(document.getElementById('editUserName').textContent).html();
                Swal.fire({ title: 'ถอนสิทธิ์ผู้ใช้งาน?', html: `คุณกำลังถอนสิทธิ์ของ <strong>${safeName}</strong><br><span class="text-sm">บัญชีบุคลากรจะไม่ถูกลบ</span>`, icon: 'warning', showCancelButton: true, confirmButtonColor: 'var(--status-danger-solid)', cancelButtonColor: 'var(--neutral-solid)', confirmButtonText: 'ถอนสิทธิ์', cancelButtonText: 'ยกเลิก', reverseButtons: true }).then(result => {
                    if (!result.isConfirmed) return;
                    $.post(form.attr('action'), form.serialize()).done(response => { showToast(response.success || 'ถอนสิทธิ์เรียบร้อย', 'success'); closeModal('editUserRoleModal'); refreshUsers(); }).fail(error => showToast(error.responseJSON?.error || 'ถอนสิทธิ์ไม่สำเร็จ', 'danger'));
                });
            });

            $(document).on('click', '.delete-role-btn', function() {
                const button = $(this), safeRole = $('<div>').text(button.data('role-name')).html();
                Swal.fire({ title: 'ลบบทบาทนี้?', html: `ต้องการลบบทบาท <strong>${safeRole}</strong> ออกจากระบบ`, icon: 'warning', showCancelButton: true, confirmButtonColor: 'var(--status-danger-solid)', cancelButtonColor: 'var(--neutral-solid)', confirmButtonText: 'ลบบทบาท', cancelButtonText: 'ยกเลิก', reverseButtons: true }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({ url: @json(url('admin/roles/destroy')) + '/' + button.data('role-id'), method: 'POST', data: { _token: @json(csrf_token()), _method: 'DELETE' } }).done(response => { showToast(response.success || 'ลบบทบาทเรียบร้อย', 'success'); setTimeout(() => location.reload(), 500); }).fail(error => showToast(error.responseJSON?.error || 'ลบบทบาทไม่สำเร็จ', 'danger'));
                });
            });

            updateScopeUI();
            applyFilter();
        })();
    </script>
@endpush
