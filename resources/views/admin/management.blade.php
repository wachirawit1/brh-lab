@extends('layout.app')
@section('title', 'User management - ระบบแจ้งเตือนผลแล็บ')

@section('content')
    @php
        // คำนวณสถิติเบื้องต้น
        $totalUsers = $users->count();
        $totalAdmins = $users->where('role_name', 'Admin')->count();
        $totalStaff = $users->where('role_name', '!=', 'Admin')->count();
        $totalRoles = $roles->count();
    @endphp

    <div class="px-2 md:px-6">
        <!-- Breadcrumb -->
        <nav class="flex mb-4 text-xs text-gray-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li><a href="{{ route('amr.index') }}" class="hover:text-brand-600 font-medium">หน้าแรก</a></li>
                <li class="flex items-center">
                    <i class="fa-solid fa-chevron-right mx-1.5 text-xs text-gray-400"></i>
                    <span class="text-gray-700 font-semibold">จัดการผู้ใช้และสิทธิ์</span>
                </li>
            </ol>
        </nav>

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-2xl bg-brand-50 text-brand-600 border border-brand-100 flex items-center justify-center text-xl shadow-2xs">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">การจัดการผู้ใช้งาน (User Management)</h1>
                    <p class="text-xs text-gray-500 mt-0.5">กำหนดระดับสิทธิ์และตรวจสอบสถานะการเข้าใช้งานของผู้ใช้ในระบบ</p>
                </div>
            </div>
            <button type="button" onclick="toggleModal('addRoleModal')"
                class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-xs flex items-center gap-2 text-sm cursor-pointer">
                <i class="fa-solid fa-plus-circle text-brand-400"></i>
                <span>เพิ่มสิทธิ์ใหม่</span>
            </button>
        </div>

        <!-- 📊 Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-xl flex items-center justify-center text-xl border border-sky-100">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">ผู้ใช้ทั้งหมด</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalUsers }}</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">แอดมิน (Admin)</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalAdmins }}</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center text-xl border border-teal-100">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">เจ้าหน้าที่ทั่วไป</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalStaff }}</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl border border-amber-100">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">ประเภทสิทธิ์</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalRoles }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area (Full Width Stack Layout) -->
        <div class="space-y-6">

            <!-- User Table Card -->
            <div class="bg-white rounded-2xl shadow-2xs border border-gray-200 overflow-hidden">
                <div class="p-5 md:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/40">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">รายชื่อผู้ใช้เข้าระบบ</h2>
                        <p class="text-xs text-gray-500">ค้นหาและจัดการสิทธิ์การเข้าถึงของผู้ใช้งาน</p>
                    </div>
                    <div class="relative w-full sm:w-80">
                        <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="search" name="search" aria-label="ค้นหาชื่อ หรือ Username"
                            class="w-full pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-xl text-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition text-gray-800 shadow-2xs"
                            placeholder="ค้นหาชื่อ หรือ Username..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="user-result overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3.5 text-left uppercase tracking-wider">ข้อมูลผู้ใช้</th>
                                <th class="px-6 py-3.5 text-left uppercase tracking-wider">ตำแหน่ง</th>
                                <th class="px-6 py-3.5 text-center uppercase tracking-wider">สิทธิ์การใช้งาน</th>
                                <th class="px-6 py-3.5 text-center uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-3.5 text-center uppercase tracking-wider">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-sky-50/40 transition-colors">
                                    <td class="px-6 py-4 flex items-center gap-3">
                                        <div class="h-9 w-9 flex-shrink-0 rounded-xl bg-brand-50 flex items-center justify-center text-brand-700 font-bold border border-brand-200 shadow-2xs text-xs">
                                            {{ mb_substr($user->fname, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 leading-tight">
                                                {{ $user->tname . $user->fname . ' ' . $user->lname }}</div>
                                            <div class="text-xs text-gray-500 font-medium tracking-wide mt-0.5">
                                                {{ '@' . $user->username }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 font-medium">
                                        {{ $user->position ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($user->role_name)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase {{ $user->role_name == 'Admin' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-brand-50 text-brand-700 border border-brand-200' }}">
                                                <i class="fa-solid {{ $user->role_name == 'Admin' ? 'fa-user-shield' : 'fa-user' }}"></i>
                                                {{ $user->role_name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 font-medium">ไม่มีสิทธิ์</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col justify-center items-center gap-1">
                                            @if (isset($activeSessions[$user->username]))
                                                <div class="flex items-center gap-1.5 px-2.5 py-0.5 bg-emerald-50 rounded-full border border-emerald-200">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span class="text-xs font-bold text-emerald-800 uppercase">Online</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="h-2 w-2 rounded-full {{ $user->role_name ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                                <span class="text-xs font-semibold {{ $user->role_name ? 'text-emerald-700' : 'text-gray-500' }}">{{ $user->role_name ? 'พร้อมใช้งาน' : 'ปิดใช้งาน' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button type="button" onclick="toggleModal('setRoleModal{{ $user->username }}')"
                                            aria-label="ตั้งค่าสิทธิ์ผู้ใช้ {{ $user->username }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-xs font-bold hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 transition shadow-2xs cursor-pointer">
                                            <i class="fa-solid fa-cog text-gray-400"></i>
                                            <span>ตั้งค่า</span>
                                        </button>

                                        <!-- Modal -->
                                        <div id="setRoleModal{{ $user->username }}"
                                            role="dialog" aria-modal="true" aria-labelledby="modalTitle{{ $user->username }}"
                                            class="fixed inset-0 z-50 hidden overflow-y-auto">
                                            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                                                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                                                    onclick="toggleModal('setRoleModal{{ $user->username }}')"></div>
                                                <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md relative z-10 border border-gray-200">
                                                    <div class="p-6">
                                                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                                                            <h3 id="modalTitle{{ $user->username }}" class="text-lg font-bold text-gray-900">
                                                                แก้ไขสิทธิ์ผู้ใช้งาน
                                                            </h3>
                                                            <button type="button" onclick="toggleModal('setRoleModal{{ $user->username }}')"
                                                                aria-label="ปิดหน้าต่าง"
                                                                class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                                                                <i class="fa-solid fa-xmark text-base"></i>
                                                            </button>
                                                        </div>

                                                        <div class="flex items-center gap-3.5 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                                            <div class="w-11 h-11 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-lg shadow-xs">
                                                                {{ mb_substr($user->fname, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <div class="font-bold text-gray-900 text-sm">
                                                                    {{ $user->tname . $user->fname . ' ' . $user->lname }}
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    {{ '@' . $user->username }} ({{ $user->position ?: 'ไม่ระบุตำแหน่ง' }})
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <form method="POST"
                                                            action="{{ route('admin.users.setRole', $user->username) }}"
                                                            class="set-role-form space-y-5">
                                                            @csrf
                                                            <div>
                                                                <label class="block text-xs font-bold text-gray-700 mb-2">
                                                                    ระดับสิทธิ์ (Access Role)
                                                                </label>
                                                                <select name="role" required
                                                                    class="block w-full rounded-xl border-gray-300 bg-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 py-2.5 px-3.5 border transition text-xs font-semibold text-gray-800">
                                                                    <option value="" disabled selected>-- เลือกสิทธิ์ --</option>
                                                                    @foreach ($roles as $role)
                                                                        <option value="{{ $role->id }}"
                                                                            {{ $user->role_name == $role->name ? 'selected' : '' }}>
                                                                            {{ $role->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <button type="submit"
                                                                class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2.5 rounded-xl shadow-xs transition-all font-bold text-xs cursor-pointer">
                                                                บันทึกข้อมูล
                                                            </button>
                                                        </form>

                                                        @if ($user->role_name)
                                                            <div class="mt-6 pt-4 border-t border-gray-100">
                                                                <form action="{{ route('admin.users.destroy', $user->username) }}"
                                                                    method="POST"
                                                                    class="destroy-user-form flex items-center justify-between p-3.5 bg-red-50/70 rounded-xl border border-red-200">
                                                                    @csrf @method('DELETE')
                                                                    <div class="text-xs font-bold text-red-700">
                                                                        ลบผู้ใช้ออกจากระบบ?
                                                                    </div>
                                                                    <button type="submit"
                                                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs cursor-pointer flex items-center gap-1.5">
                                                                        <i class="fa-solid fa-user-minus"></i>
                                                                        <span>ลบข้อมูล</span>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-16 text-center text-gray-400 font-medium">
                                        <i class="fa-solid fa-users-slash text-3xl mb-2 text-gray-300 block"></i>
                                        ไม่พบรายชื่อผู้ใช้งาน
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Roles List Card -->
            <div class="bg-white rounded-2xl shadow-2xs border border-gray-200 overflow-hidden">
                <div class="p-5 md:p-6 border-b border-gray-200 bg-gray-50/40">
                    <h2 class="text-lg font-bold text-gray-900">รายการสิทธิ์ในระบบ (Role Master)</h2>
                    <p class="text-xs text-gray-500">จัดการประเภทสิทธิ์และจำนวนผู้ใช้งานในแต่ละบทบาท</p>
                </div>
                <div class="p-5">
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left">ชื่อสิทธิ์</th>
                                    <th class="px-4 py-3 text-center">ผู้ใช้งาน</th>
                                    <th class="px-6 py-3 text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($roles as $role)
                                    <tr class="hover:bg-gray-50/80 transition-colors">
                                        <td class="px-6 py-3.5 font-bold text-gray-800 flex items-center gap-2.5">
                                            <i class="fa-solid fa-tag text-brand-500 text-xs"></i>
                                            <span>{{ $role->name }}</span>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">
                                                {{ $users->where('role_name', $role->name)->count() }} ท่าน
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            <button type="button"
                                                aria-label="ลบสิทธิ์ {{ $role->name }}"
                                                class="h-8 w-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-2xs border border-red-100 delete-role-btn cursor-pointer"
                                                data-role-id="{{ $role->id }}">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: เพิ่มสิทธิ์ใหม่ -->
    <div id="addRoleModal" role="dialog" aria-modal="true" aria-labelledby="addRoleModalTitle" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                onclick="toggleModal('addRoleModal')"></div>
            <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative z-10 border border-gray-200">
                <form method="POST" action="{{ route('admin.roles.store') }}" class="p-6">
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <h3 id="addRoleModalTitle" class="text-lg font-bold text-gray-900">เพิ่มสิทธิ์ใหม่</h3>
                        <button type="button" onclick="toggleModal('addRoleModal')"
                            aria-label="ปิดหน้าต่าง"
                            class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5">ชื่อระดับสิทธิ์</label>
                            <input type="text" name="name" required
                                class="w-full bg-white border border-gray-300 rounded-xl py-2 px-3.5 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-medium text-gray-800 text-xs shadow-2xs transition"
                                placeholder="ระบุชื่อสิทธิ์ (เช่น Pharmacist)...">
                        </div>
                    </div>
                    <div class="flex gap-2.5">
                        <button type="submit"
                            class="flex-grow bg-brand-600 hover:bg-brand-700 text-white py-2.5 px-4 rounded-xl font-bold text-xs shadow-xs transition-all cursor-pointer">
                            บันทึกข้อมูล
                        </button>
                        <button type="button" onclick="toggleModal('addRoleModal')"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl font-bold text-xs transition-all cursor-pointer">
                            ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('managementScript')
    <script>
        window.toggleModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;

            const opening = modal.classList.contains('hidden');
            modal.classList.toggle('hidden', !opening);
            window.BRHModalScroll?.set(`dom-modal:${id}`, opening);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;

            document.querySelectorAll('[role="dialog"].fixed:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
                window.BRHModalScroll?.set(`dom-modal:${modal.id}`, false);
            });
        });

        let searchT;
        $('input[name="search"]').on('input', function() {
            clearTimeout(searchT);
            const val = $(this).val().trim();
            searchT = setTimeout(() => {
                let url = (val.length > 0) ? "{{ route('admin.findUser') }}" :
                    "{{ route('admin.management') }}";
                $.get(url, {
                    search: val
                }, function(data) {
                    $('.user-result').html($(data).find('.user-result').html());
                });
            }, 500);
        });

        $(document).on('submit', '.set-role-form', function(e) {
            e.preventDefault();
            const f = $(this);
            const btn = f.find('button[type="submit"]');

            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-2"></i> กำลังบันทึก...');

            $.post(f.attr('action'), f.serialize(), function(r) {
                showToast(r.success || 'อัพเดทสำเร็จ', 'success');
                f.closest('.fixed').addClass('hidden');
                $('input[name="search"]').trigger('input');
            }).fail(function(err) {
                const msg = err.responseJSON ? err.responseJSON.error : 'เกิดข้อผิดพลาด';
                showToast(msg, 'danger');
            }).always(function() {
                btn.prop('disabled', false).html('บันทึกข้อมูล');
            });
        });

        $(document).on('submit', '.destroy-user-form', function(e) {
            e.preventDefault();
            const f = $(this);
            Swal.fire({
                title: 'ยืนยันการลบผู้ใช้?',
                text: "ข้อมูลการเข้าถึงระบบของผู้ใช้รายนี้จะถูกลบออก",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--status-danger-solid)',
                cancelButtonColor: 'var(--neutral-solid)',
                confirmButtonText: 'ใช่, ลบออก',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'font-kanit px-6 py-2.5 rounded-xl',
                    cancelButton: 'font-kanit px-6 py-2.5 rounded-xl'
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post(f.attr('action'), f.serialize(), function(r) {
                        showToast(r.success || 'ลบเรียบร้อย', 'success');
                        f.closest('.fixed').addClass('hidden');
                        $('input[name="search"]').trigger('input');
                    }).fail(function(err) {
                        const msg = err.responseJSON ? err.responseJSON.error :
                            'ไม่สามารถลบผู้ใช้ได้';
                        showToast(msg, 'danger');
                    });
                }
            });
        });

        $(document).on('click', '.delete-role-btn', function() {
            const rid = $(this).data('role-id');
            Swal.fire({
                title: 'ยืนยันการลบสิทธิ์?',
                text: "ตรวจสอบให้แน่ใจว่าไม่มีผู้ใช้คนใดใช้งานสิทธิ์นี้อยู่",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--status-danger-solid)',
                cancelButtonColor: 'var(--neutral-solid)',
                confirmButtonText: 'ลบสิทธิ์',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'font-kanit px-6 py-2.5 rounded-xl',
                    cancelButton: 'font-kanit px-6 py-2.5 rounded-xl'
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/roles/destroy') }}/" + rid,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            showToast(response.success || 'ลบทิ้งเรียบร้อย', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        },
                        error: function(err) {
                            const msg = err.responseJSON ? err.responseJSON.error :
                                'ไม่สามารถลบสิทธิ์ได้';
                            showToast(msg, 'danger');
                        }
                    });
                }
            });
        });
    </script>
@endpush
