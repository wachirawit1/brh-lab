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
        <nav class="flex mb-4 text-xs text-gray-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li><a href="{{ route('amr.index') }}" class="hover:text-brand-600 font-medium">หน้าแรก</a></li>
                <li class="flex items-center">
                    <i class="fa-solid fa-chevron-right mx-1.5 text-xs text-gray-400"></i>
                    <span class="text-gray-700 font-semibold">จัดการแจ้งเตือน Telegram</span>
                </li>
            </ol>
        </nav>

        <!-- Header Area -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-2xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-xl shadow-2xs">
                    <i class="fa-solid fa-bell-circle-check"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">การจัดการแจ้งเตือน (Notification Management)</h1>
                    <p class="text-xs text-gray-500 mt-0.5">ตรวจสอบและควบคุมสถานะการรับแจ้งเตือนผลแล็บผ่าน Telegram Bot</p>
                </div>
            </div>
            <div class="bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-2xs flex items-center gap-2.5">
                <span class="flex h-2.5 w-2.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-gray-700">Bot Connection: <span class="text-emerald-700 uppercase font-black">Active</span></span>
            </div>
        </div>

        <!-- 📊 Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-sky-50 text-sky-600 rounded-xl flex items-center justify-center text-xl border border-sky-100">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">ผู้ติดตามทั้งหมด</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalSubscribers }}</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl border border-emerald-100">
                    <i class="fa-solid fa-square-check"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">เปิดรับแจ้งเตือน</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $activeSubscribers }}</p>
                </div>
            </div>

            <div class="p-5 rounded-2xl shadow-2xs border border-gray-200 bg-white flex items-center gap-4 hover:shadow-xs transition">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl border border-amber-100">
                    <i class="fa-solid fa-bell-slash"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">ระงับชั่วคราว</p>
                    <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $pausedSubscribers }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content Area (Full Width Layout) -->
        <div class="space-y-6">
            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-2xs border border-gray-200 overflow-hidden">
                <div class="p-5 md:p-6 border-b border-gray-200 bg-gray-50/40">
                    <h2 class="text-lg font-bold text-gray-900">รายชื่อผู้ลงทะเบียนรับแจ้งเตือน</h2>
                    <p class="text-xs text-gray-500">ควบคุมการอนุญาตส่งผลแล็บอัตโนมัติไปยังบัญชี Telegram แต่ละท่าน</p>
                </div>

                <div class="overflow-x-auto">
                    @if ($subscribers->isEmpty())
                        <div class="p-16 text-center text-gray-400">
                            <i class="fa-solid fa-bell-slash text-4xl mb-3 text-gray-300 block"></i>
                            <p class="font-bold text-sm text-gray-500">ยังไม่มีผู้ลงทะเบียนขอรับการแจ้งเตือน</p>
                        </div>
                    @else
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3.5 text-left uppercase tracking-wider">บัญชีผู้ใช้</th>
                                    <th class="px-6 py-3.5 text-left uppercase tracking-wider">PM / Chat ID</th>
                                    <th class="px-6 py-3.5 text-center uppercase tracking-wider">สถานะการรับ</th>
                                    <th class="px-6 py-3.5 text-center uppercase tracking-wider">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($subscribers as $subscriber)
                                    <tr id="row_{{ $subscriber->id }}" class="hover:bg-sky-50/40 transition-colors">
                                        <td class="px-6 py-4 flex items-center gap-3">
                                            <div class="h-9 w-9 flex-shrink-0 rounded-xl bg-gray-100 flex items-center justify-center text-gray-700 font-bold border border-gray-200 uppercase shadow-2xs text-xs">
                                                {{ mb_substr($subscriber->fullName, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 leading-tight">
                                                    {{ $subscriber->fullName }}</div>
                                                <div class="text-xs text-gray-500 font-medium tracking-wide mt-0.5">
                                                    {{ $subscriber->position }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-xs font-bold text-gray-800">PM: {{ $subscriber->pm }}</div>
                                            <div class="text-xs font-mono text-gray-500 tracking-tight mt-0.5">CID: {{ $subscriber->chat_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center status-cell">
                                            @if ($subscriber->allowed)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    กำลังรับแจ้งเตือน
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 uppercase">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                    ระงับชั่วคราว
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center action-cell">
                                            <div class="flex justify-center items-center gap-2">
                                                <button type="button"
                                                    class="toggle-notify-btn min-h-[38px] min-w-[38px] px-3 py-1.5 flex items-center justify-center rounded-xl transition-all shadow-2xs border cursor-pointer {{ $subscriber->allowed ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-600 hover:text-white' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-600 hover:text-white' }}"
                                                    data-id="{{ $subscriber->id }}"
                                                    aria-label="{{ $subscriber->allowed ? 'ระงับชั่วคราว' : 'เปิดรับการแจ้งเตือน' }}"
                                                    title="{{ $subscriber->allowed ? 'ระงับชั่วคราว' : 'เปิดรับการแจ้งเตือน' }}">
                                                    <i class="fa-solid {{ $subscriber->allowed ? 'fa-bell-slash' : 'fa-bell' }} text-xs"></i>
                                                </button>

                                                <button type="button"
                                                    class="del-notify-btn min-h-[38px] min-w-[38px] px-3 py-1.5 flex items-center justify-center rounded-xl bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-all shadow-2xs cursor-pointer"
                                                    data-id="{{ $subscriber->id }}"
                                                    data-name="{{ $subscriber->fullName }}"
                                                    aria-label="ลบผู้ติดตาม {{ $subscriber->fullName }}"
                                                    title="ลบผู้ติดตาม">
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

                            // Update Status Badge (Design System Compliant)
                            let statusHtml = isAllowed ?
                                '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> กำลังรับแจ้งเตือน</span>' :
                                '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200 uppercase"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> ระงับชั่วคราว</span>';
                            $row.find('.status-cell').html(statusHtml);

                            // Update Buttons (Accessible & Design System Compliant)
                            let toggleBtnHtml = isAllowed ?
                                `<button type="button" class="toggle-notify-btn min-h-[38px] min-w-[38px] px-3 py-1.5 flex items-center justify-center rounded-xl bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-600 hover:text-white transition-all shadow-2xs cursor-pointer" data-id="${id}" aria-label="ระงับชั่วคราว" title="ระงับชั่วคราว"><i class="fa-solid fa-bell-slash text-xs"></i></button>` :
                                `<button type="button" class="toggle-notify-btn min-h-[38px] min-w-[38px] px-3 py-1.5 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white transition-all shadow-2xs cursor-pointer" data-id="${id}" aria-label="เปิดรับการแจ้งเตือน" title="เปิดรับการแจ้งเตือน"><i class="fa-solid fa-bell text-xs"></i></button>`;

                            let delBtnHtml =
                                `<button type="button" class="del-notify-btn min-h-[38px] min-w-[38px] px-3 py-1.5 flex items-center justify-center rounded-xl bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-all shadow-2xs cursor-pointer" data-id="${id}" data-name="${$row.find('div.font-bold').text()}" aria-label="ลบผู้ติดตาม" title="ลบผู้ติดตาม"><i class="fa-solid fa-trash-can text-xs"></i></button>`;

                            $row.find('.action-cell div').html(toggleBtnHtml + ' ' + delBtnHtml);
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
                    confirmButtonColor: 'var(--status-danger-solid)',
                    cancelButtonColor: 'var(--neutral-solid)',
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
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                if (response.success) {
                                    showToast('ลบผู้ติดตามเรียบร้อยแล้ว', 'success');
                                    $row.fadeOut(300, function() {
                                        $(this).remove();
                                        if ($('tbody tr').length === 0) {
                                            location.reload();
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
