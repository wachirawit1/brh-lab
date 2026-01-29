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
        <nav class="flex mb-4 text-xs text-gray-400" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="/" class="hover:text-brand-600">หน้าแรก</a></li>
                <li class="flex items-center">
                    <i class="fa-solid fa-chevron-right mx-2 text-[8px]"></i>
                    <span class="text-gray-500">จัดการผู้ใช้และสิทธิ์</span>
                </li>
            </ol>
        </nav>

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-brand-600 p-3 rounded-2xl shadow-lg shadow-brand-100">
                    <i class="fa-solid fa-users-gear text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">User management</h1>
                    <p class="text-gray-500 text-sm">จัดการบัญชีผู้ใช้ สิทธิ์ และการเข้าถึงระบบ</p>
                </div>
            </div>
            <button onclick="toggleModal('addRoleModal')"
                class="mt-4 md:mt-0 bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-md flex items-center gap-2">
                <i class="fa-solid fa-plus-circle"></i> เพิ่มสิทธิ์ใหม่
            </button>
        </div>

        <!-- 📊 Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">ผู้ใช้ทั้งหมด</p>
                    <p class="text-2xl font-black text-gray-800">{{ $totalUsers }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">แอดมิน</p>
                    <p class="text-2xl font-black text-gray-800">{{ $totalAdmins }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">เจ้าหน้าที่ทั่วไป</p>
                    <p class="text-2xl font-black text-gray-800">{{ $totalStaff }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">ประเภทสิทธิ์</p>
                    <p class="text-2xl font-black text-gray-800">{{ $totalRoles }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: User Table -->
            <div class="lg:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800">รายชื่อผู้ใช้เข้าระบบ</h2>
                    <div class="relative w-full sm:w-72">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="search" name="search"
                            class="w-full pl-11 pr-4 py-2 bg-gray-50 border-none rounded-full text-sm focus:ring-2 focus:ring-brand-500/20 transition text-gray-700"
                            placeholder=" ค้นหาชื่อ หรือ Username..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="user-result overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-left font-bold text-gray-400 uppercase tracking-wider">
                                    ข้อมูลผู้ใช้</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-400 uppercase tracking-wider">ตำแหน่ง
                                </th>
                                <th class="px-6 py-4 text-center font-bold text-gray-400 uppercase tracking-wider">
                                    สิทธิ์การใช้งาน</th>
                                <th class="px-6 py-4 text-center font-bold text-gray-400 uppercase tracking-wider">สถานะ
                                </th>
                                <th class="px-8 py-4 text-center font-bold text-gray-400 uppercase tracking-wider">การจัดการ
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($users as $user)
                                <tr class="hover:bg-brand-50/30 transition-colors">
                                    <td class="px-8 py-5 flex items-center gap-4">
                                        <div
                                            class="h-10 w-10 flex-shrink-0 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center text-brand-700 font-bold border border-brand-200">
                                            {{ mb_substr($user->fname, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 leading-none mb-1">
                                                {{ $user->tname . $user->fname . ' ' . $user->lname }}</div>
                                            <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">
                                                {{ $user->username }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-gray-500 font-medium italic">
                                        {{ $user->position ?: '-' }}
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @if ($user->role_name)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $user->role_name == 'Admin' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-brand-50 text-brand-600 border border-brand-100' }}">
                                                <i
                                                    class="fa-solid {{ $user->role_name == 'Admin' ? 'fa-user-shield' : 'fa-user' }}"></i>
                                                {{ $user->role_name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-300 font-medium">ไม่มีสิทธิ์</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex flex-col justify-center items-center gap-1">
                                            @if (isset($activeSessions[$user->username]))
                                                <div
                                                    class="flex items-center gap-1.5 px-2 py-0.5 bg-emerald-50 rounded-full border border-emerald-100">
                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    <span
                                                        class="text-[9px] font-black text-emerald-600 uppercase">Online</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-2 mt-1">
                                                <span
                                                    class="h-2 w-2 rounded-full {{ $user->role_name ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                                <span
                                                    class="text-xs font-bold {{ $user->role_name ? 'text-emerald-600' : 'text-gray-400' }}">{{ $user->role_name ? 'พร้อมใช้งาน' : 'ปิดใช้งาน' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <button onclick="toggleModal('setRoleModal{{ $user->username }}')"
                                            class="inline-flex items-center gap-2 px-4 py-1.5 bg-white border border-brand-200 text-brand-600 rounded-full text-xs font-bold hover:bg-brand-600 hover:text-white hover:border-brand-600 transition shadow-sm">
                                            <i class="fa-solid fa-cog"></i> ตั้งค่า
                                        </button>

                                        <!-- Modal -->
                                        <div id="setRoleModal{{ $user->username }}"
                                            class="fixed inset-0 z-50 hidden overflow-y-auto">
                                            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                                                <div class="fixed inset-0 bg-gray-900 bg-opacity-40 backdrop-blur-[2px]"
                                                    onclick="toggleModal('setRoleModal{{ $user->username }}')"></div>
                                                <div
                                                    class="inline-block bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md relative z-10">
                                                    <div class="p-8">
                                                        <h3 class="text-2xl font-black text-gray-800 mb-2">
                                                            แก้ไขสิทธิ์ผู้ใช้งาน</h3>
                                                        <p class="text-gray-500 text-sm mb-6 pb-6 border-b border-gray-100">
                                                            ระบุบทบาทที่เหมาะสมกับการปฏิบัติงานของเจ้าหน้าที่</p>

                                                        <div
                                                            class="flex items-center gap-4 mb-8 p-4 bg-gray-50 rounded-2xl">
                                                            <div
                                                                class="w-12 h-12 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl">
                                                                {{ mb_substr($user->fname, 0, 1) }}</div>
                                                            <div>
                                                                <div class="font-bold text-gray-800 text-base">
                                                                    {{ $user->tname . $user->fname . ' ' . $user->lname }}
                                                                </div>
                                                                <div class="text-xs text-gray-500 italic">
                                                                    {{ '@' . $user->username }}</div>
                                                            </div>
                                                        </div>

                                                        <form method="POST"
                                                            action="{{ route('admin.users.setRole', $user->username) }}"
                                                            class="set-role-form space-y-6">
                                                            @csrf
                                                            <div>
                                                                <label
                                                                    class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">ระดับสิทธิ์
                                                                    (Access Role)
                                                                </label>
                                                                <select name="role" required
                                                                    class="block w-full rounded-2xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-brand-500 focus:border-brand-500 py-3 px-4 border transition font-bold text-gray-700">
                                                                    <option value="" disabled selected>-- เลือกสิทธิ์
                                                                        --</option>
                                                                    @foreach ($roles as $role)
                                                                        <option value="{{ $role->id }}"
                                                                            {{ $user->role_name == $role->name ? 'selected' : '' }}>
                                                                            {{ $role->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <button type="submit"
                                                                class="w-full bg-brand-600 hover:bg-brand-700 text-white py-4 rounded-2xl shadow-lg shadow-brand-200 transition-all font-bold">บันทึกข้อมูล</button>
                                                        </form>

                                                        @if ($user->role_name)
                                                            <div class="mt-10 pt-6 border-t border-gray-100">
                                                                <form
                                                                    action="{{ route('admin.users.destroy', $user->username) }}"
                                                                    method="POST"
                                                                    class="destroy-user-form flex items-center justify-between p-4 bg-red-50/50 rounded-2xl border border-red-100">
                                                                    @csrf @method('DELETE')
                                                                    <div
                                                                        class="text-[10px] font-bold text-red-500 uppercase tracking-widest">
                                                                        ลบผู้ใช้ออก?</div>
                                                                    <button type="submit"
                                                                        class="text-red-600 hover:text-red-700 font-bold text-sm flex items-center gap-2 transition">
                                                                        <i class="fa-solid fa-user-minus"></i> ลบข้อมูล
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
                                    <td colspan="5"
                                        class="py-20 text-center text-gray-300 font-medium italic font-kanit">
                                        ไม่พบรายชื่อผู้ใช้งาน</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Column: Roles List -->
            <div class="lg:col-span-4 space-y-6 font-kanit">
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
                    <div class="p-8 border-b border-gray-50">
                        <h2 class="text-xl font-bold text-gray-800">รายการสิทธิ์ในระบบ</h2>
                    </div>
                    <div class="p-4">
                        <div class="overflow-hidden rounded-3xl border border-gray-50">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-bold text-gray-400">ชื่อสิทธิ์</th>
                                        <th class="px-4 py-4 text-center font-bold text-gray-400">ผู้ใช้งาน</th>
                                        <th class="px-6 py-4 text-center font-bold text-gray-400">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($roles as $role)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-bold text-gray-700 flex items-center gap-3">
                                                <i class="fa-solid fa-tag text-brand-400 text-[10px]"></i>
                                                {{ $role->name }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <span
                                                    class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                                    {{ $users->where('role_name', $role->name)->count() }} ท่าน
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button
                                                    class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all delete-role-btn"
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

                <!-- Helper Card -->
                <div
                    class="bg-gradient-to-br from-brand-600 to-brand-800 rounded-[2.5rem] p-8 text-white shadow-xl shadow-brand-100 relative overflow-hidden">
                    <i class="fa-solid fa-shield-halved absolute -right-4 -bottom-4 text-8xl text-white/10 rotate-12"></i>
                    <h4 class="text-lg font-bold mb-2">คำแนะนำเพิ่มเติม</h4>
                    <p class="text-white/80 text-xs leading-relaxed">การกำหนดสิทธิ์ช่วยควบคุมการเข้าถึงข้อมูลผลแล็บคนคนไข้
                        กรุณาตรวจสอบให้แน่ใจก่อนมอบสิทธิ์ Admin ให้กับผู้ใช้รายใด</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: เพิ่มสิทธิ์ใหม่ -->
    <div id="addRoleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-40 backdrop-blur-[2px]"
                onclick="toggleModal('addRoleModal')"></div>
            <div
                class="inline-block bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-sm relative z-10">
                <form method="POST" action="{{ route('admin.roles.store') }}" class="p-8">
                    @csrf
                    <h3 class="text-2xl font-black text-gray-800 mb-6 font-kanit">เพิ่มสิทธิ์ใหม่</h3>
                    <div class="space-y-4 mb-8">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 font-kanit">ชื่อระดับสิทธิ์</label>
                            <input type="text" name="name" required
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-3 px-4 focus:ring-brand-500 focus:border-brand-500 font-bold text-gray-700 border transition font-kanit"
                                placeholder="ระบุชื่อสิทธิ์...">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-grow bg-brand-600 hover:bg-brand-700 text-white py-3 rounded-2xl font-bold shadow-lg shadow-brand-100 transition-all font-kanit">บันทึกข้อมูล</button>
                        <button type="button" onclick="toggleModal('addRoleModal')"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-500 px-6 py-3 rounded-2xl font-bold transition-all font-kanit">ยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('managementScript')
    <script>
        window.toggleModal = function(id) {
            const m = document.getElementById(id);
            if (m) m.classList.toggle('hidden');
        }

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
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
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
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
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
