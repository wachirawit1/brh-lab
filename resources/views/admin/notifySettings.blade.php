@extends('layout.app')
@section('title', 'จัดการการแจ้งเตือน - ระบบแจ้งเตือนผลแล็บ')

@section('content')
    @php
        $totalSubscribers = $subscribers->count();
        $activeSubscribers = $subscribers->where('allowed', 1)->count();
        $pausedSubscribers = $subscribers->where('allowed', 0)->count();
    @endphp

    <div class="px-2 md:px-6">
        <!-- Breadcrumb -->
        <nav class="flex mb-4 text-xs text-gray-400 font-kanit" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li><a href="/" class="hover:text-brand-600">หน้าแรก</a></li>
                <li class="flex items-center">
                    <i class="fa-solid fa-chevron-right mx-2 text-[8px]"></i>
                    <span class="text-gray-500">จัดการแจ้งเตือน Telegram</span>
                </li>
            </ol>
        </nav>

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 font-kanit">
            <div class="flex items-center gap-4">
                <div class="bg-brand-600 p-3 rounded-2xl shadow-lg shadow-brand-100">
                    <i class="fa-solid fa-bell-circle-check text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Notification Management</h1>
                    <p class="text-gray-500 text-sm">จัดการผู้รับแจ้งเตือนผ่าน Telegram Bot</p>
                </div>
            </div>
            <div
                class="mt-4 md:mt-0 bg-white px-5 py-2.5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-sm font-bold text-gray-600">Bot Connection: <span
                        class="text-emerald-600 uppercase">Active</span></span>
            </div>
        </div>

        <!-- 📊 Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 font-kanit">
            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">ผู้ติดตามทั้งหมด</p>
                    <p class="text-2xl font-black text-gray-800">{{ $totalSubscribers }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-square-check"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">เปิดรับแจ้งเตือน</p>
                    <p class="text-2xl font-black text-gray-800">{{ $activeSubscribers }}</p>
                </div>
            </div>

            <div
                class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-bell-slash"></i>
                </div>
                <div>
                    <p class="text-gray-400 text-xs font-semibold uppercase">ระงับชั่วคราว</p>
                    <p class="text-2xl font-black text-gray-800">{{ $pausedSubscribers }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start font-kanit">
            <!-- Left: Table (8/12) -->
            <div class="lg:col-span-8 bg-white rounded-[2.5rem] shadow-sm border border-gray-50 overflow-hidden">
                <div class="p-8 border-b border-gray-50">
                    <h2 class="text-xl font-bold text-gray-800">รายชื่อผู้ลงทะเบียนรับแจ้งเตือน</h2>
                </div>

                <div class="overflow-x-auto">
                    @if ($subscribers->isEmpty())
                        <div class="p-20 text-center text-gray-300">
                            <i class="fa-solid fa-face-dashed text-6xl mb-4 text-gray-100"></i>
                            <p class="font-bold italic">ยังไม่มีผู้ลงทะเบียนขอรับการแจ้งเตือน</p>
                        </div>
                    @else
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-4 text-left font-bold text-gray-400 uppercase tracking-wider">
                                        บัญชีผู้ใช้</th>
                                    <th class="px-6 py-4 text-left font-bold text-gray-400 uppercase tracking-wider">PM /
                                        Chat ID</th>
                                    <th class="px-6 py-4 text-center font-bold text-gray-400 uppercase tracking-wider">
                                        สถานะการรับ</th>
                                    <th class="px-8 py-4 text-center font-bold text-gray-400 uppercase tracking-wider">
                                        การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($subscribers as $subscriber)
                                    <tr id="row_{{ $subscriber->id }}" class="hover:bg-brand-50/30 transition-colors">
                                        <td class="px-8 py-5 flex items-center gap-4">
                                            <div
                                                class="h-10 w-10 flex-shrink-0 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100 flex items-center justify-center text-brand-700 font-bold border border-brand-200 uppercase">
                                                {{ mb_substr($subscriber->fullName, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 leading-none mb-1">
                                                    {{ $subscriber->fullName }}</div>
                                                <div class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">
                                                    {{ $subscriber->position }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="text-sm font-bold text-gray-600">PM: {{ $subscriber->pm }}</div>
                                            <div class="text-[10px] font-mono text-gray-400 uppercase tracking-tight">CID:
                                                {{ $subscriber->chat_id }}</div>
                                        </td>
                                        <td class="px-6 py-5 text-center status-cell">
                                            @if ($subscriber->allowed)
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    กำลังรับแจ้งเตือน
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100 uppercase">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> ระงับชั่วคราว
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-5 text-center action-cell">
                                            <div class="flex justify-center items-center gap-3">
                                                <button type="button"
                                                    class="toggle-notify-btn h-9 w-9 flex items-center justify-center rounded-xl transition-all shadow-sm border {{ $subscriber->allowed ? 'bg-amber-50 text-amber-600 border-amber-100 hover:bg-amber-600 hover:text-white' : 'bg-emerald-50 text-emerald-600 border-emerald-100 hover:bg-emerald-600 hover:text-white' }}"
                                                    data-id="{{ $subscriber->id }}"
                                                    title="{{ $subscriber->allowed ? 'ระงับชั่วคราว' : 'เปิดรับการแจ้งเตือน' }}">
                                                    <i
                                                        class="fa-solid {{ $subscriber->allowed ? 'fa-bell-slash' : 'fa-bell' }} text-xs"></i>
                                                </button>

                                                <button type="button"
                                                    class="del-notify-btn h-9 w-9 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white transition-all shadow-sm"
                                                    data-id="{{ $subscriber->id }}"
                                                    data-name="{{ $subscriber->fullName }}">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <!-- Right: Guide (4/12) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Instruction Card -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-50 p-8">
                    <h3 class="text-xl font-black text-gray-800 mb-4">การทำงานของ Bot</h3>
                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                1</div>
                            <p class="text-xs text-gray-500 leading-relaxed">ผู้ใช้ต้องแสกน QR Code
                                จากหน้าหลักเพื่อเริ่มการทำงานของ Telegram Bot</p>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                2</div>
                            <p class="text-xs text-gray-500 leading-relaxed">เมื่อกด Start ระบบจะผูกรหัสพนักงานเข้ากับ Chat
                                ID โดยอัตโนมัติ</p>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                3</div>
                            <p class="text-xs text-gray-500 leading-relaxed">Admin สามารถควบคุมการ "ปิด" หรือ "เปิด"
                                การแจ้งเตือนรายบุคคลได้จากหน้านี้</p>
                        </div>
                    </div>
                </div>

                <!-- Bot Status Card -->
                <div
                    class="bg-gradient-to-br from-brand-600 to-brand-800 rounded-[2.5rem] p-8 text-white shadow-xl shadow-brand-100 relative overflow-hidden">
                    <i class="fa-brands fa-telegram absolute -right-6 -bottom-6 text-9xl text-white/10 -rotate-12"></i>
                    <h4 class="text-lg font-bold mb-2">Telegram API</h4>
                    <p class="text-white/80 text-[10px] leading-relaxed uppercase tracking-widest font-bold">Secure Delivery
                        Service</p>
                    <div class="mt-4 pt-4 border-t border-white/10">
                        <div class="flex justify-between items-center text-[10px] font-bold">
                            <span>Messages Today:</span>
                            <span class="bg-white/20 px-2 py-0.5 rounded">Real-time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('notifySettings')
    <script>
        $(document).ready(function() {
            // Toggle notification status
            $(document).on('click', '.toggle-notify-btn', function() {
                let $btn = $(this);
                let id = $btn.data('id');
                if ($btn.prop('disabled')) return;

                const originalHtml = $btn.html();
                $btn.prop('disabled', true).addClass('opacity-50').html(
                    '<i class="fa-solid fa-circle-notch fa-spin text-xs"></i>');

                $.ajax({
                    url: '{{ route('admin.updateNotificationStatus') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    },
                    success: function(response) {
                        if (response.success) {
                            let isAllowed = response.allowed;
                            let $row = $('#row_' + id);

                            // Update Status Badge (Consistency Style)
                            let statusHtml = isAllowed ?
                                '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> กำลังรับแจ้งเตือน</span>' :
                                '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100 uppercase"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> ระงับชั่วคราว</span>';
                            $row.find('.status-cell').html(statusHtml);

                            // Update Buttons (Consistency Style)
                            let toggleBtnHtml = isAllowed ?
                                `<button type="button" class="toggle-notify-btn h-9 w-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 border border-amber-100 hover:bg-amber-600 hover:text-white transition-all shadow-sm" data-id="${id}" title="ระงับชั่วคราว"><i class="fa-solid fa-bell-slash text-xs"></i></button>` :
                                `<button type="button" class="toggle-notify-btn h-9 w-9 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" data-id="${id}" title="เปิดรับการแจ้งเตือน"><i class="fa-solid fa-bell text-xs"></i></button>`;

                            let delBtnHtml =
                                `<button type="button" class="del-notify-btn h-9 w-9 flex items-center justify-center rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white transition-all shadow-sm" data-id="${id}" data-name="${$row.find('div.font-bold').text()}"><i class="fa-solid fa-trash-can text-xs"></i></button>`;

                            $row.find('.action-cell div').html(toggleBtnHtml + ' ' +
                            delBtnHtml);
                            showToast(response.message, 'success');
                        }
                    },
                    error: function(err) {
                        const msg = err.responseJSON ? err.responseJSON.error :
                            'เกิดข้อผิดพลาดในการอัปเดต';
                        showToast(msg, 'danger');
                        $btn.prop('disabled', false).removeClass('opacity-50').html(
                            originalHtml);
                    }
                });
            });

            // Delete subscriber
            $(document).on('click', '.del-notify-btn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name').trim();
                let $row = $('#row_' + id);

                Swal.fire({
                    title: 'ยืนยันการลบผู้ติดตาม',
                    html: `ต้องการลบ <strong>${name}</strong> ออกจากระบบรับแจ้งเตือนหรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#9ca3af',
                    confirmButtonText: 'ลบออก',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'font-kanit px-6 py-2.5 rounded-xl',
                        cancelButton: 'font-kanit px-6 py-2.5 rounded-xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url('admin/notify-management/destroy') }}/' + id,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('ลบผู้ติดตามเรียบร้อยแล้ว', 'success');
                                    $row.fadeOut(300, function() {
                                        $(this).remove();
                                        if ($('tbody tr').length === 0) {
                                            location
                                        .reload(); // Reload to show empty state
                                        }
                                    });
                                }
                            },
                            error: function(err) {
                                const msg = err.responseJSON ? err.responseJSON.error :
                                    'ไม่สามารถลบข้อมูลได้';
                                showToast(msg, 'danger');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
