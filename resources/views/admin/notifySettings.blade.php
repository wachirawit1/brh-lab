@extends('layout.app')
@section('title', 'แก้ไขการแจ้งเตือน')
@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">การตั้งค่าการแจ้งเตือน</h2>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">ผู้ติดตามการแจ้งเตือน</h5>
            </div>
            <div class="card-body">
                @if ($subscribers->isEmpty())
                    <p class="text-muted">ไม่มีผู้ติดตามการแจ้งเตือน</p>
                @else
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Chat ID</th>
                                <th>PM</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>ชื่อ-สกุล</th>
                                <th>ตำแหน่ง</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscribers as $subscriber)
                                <tr id="row_{{ $subscriber->id }}">
                                    <td>{{ $subscriber->chat_id }}</td>
                                    <td>{{ $subscriber->pm }}</td>
                                    <td>{{ $subscriber->username ?? 'ไม่ระบุ' }}</td>
                                    <td>{{ $subscriber->fullName }}</td>
                                    <td>{{ $subscriber->position }}</td>
                                    <td class="status-cell">
                                        @if ($subscriber->allowed)
                                            <span style="color: #198754; font-size: 24px; cursor: pointer;"
                                                title="ใช้งาน">●</span>
                                        @else
                                            <span style="color: #dc3545; font-size: 24px; cursor: pointer;"
                                                title="ปิดใช้งาน">●</span>
                                        @endif
                                    </td>
                                    <td class="action-cell">
                                        @if ($subscriber->allowed)
                                            <button type="button" class="btn btn-sm btn-danger toggle-notify-btn"
                                                data-id="{{ $subscriber->id }}" title="ปิดแจ้งเตือน">
                                                <i class="fa-solid fa-bell-slash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success toggle-notify-btn"
                                                data-id="{{ $subscriber->id }}" title="เปิดแจ้งเตือน">
                                                <i class="fa-solid fa-bell"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-sm btn-danger del-notify-btn"
                                            data-id="{{ $subscriber->id }}"
                                            data-name="{{ $subscriber->fullName }}"
                                            title="ลบผู้ติดตาม">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection
@push('notifySettings')
    <script>
        /**
         * ฟังก์ชันสำหรับสร้างและแสดง Bootstrap Toast
         * @param {string} message ข้อความที่ต้องการแสดง
         * @param {string} type ประเภทของ Toast (success, danger, warning, info)
         */
        function showToast(message, type) {
            let bgColor = '';
            let iconClass = '';
            let btnCloseClass = 'btn-close';

            // กำหนดสีและไอคอนตามประเภท (อ้างอิงจากโค้ด HTML ของคุณ)
            switch (type) {
                case 'success':
                    bgColor = 'text-bg-success';
                    iconClass = 'fas fa-check-circle';
                    btnCloseClass += ' btn-close-white';
                    break;
                case 'danger':
                    bgColor = 'text-bg-danger';
                    iconClass = 'fas fa-exclamation-triangle';
                    btnCloseClass += ' btn-close-white';
                    break;
                case 'warning':
                    bgColor = 'text-bg-warning';
                    iconClass = 'fas fa-exclamation-circle';
                    break;
                case 'info':
                    bgColor = 'text-bg-info';
                    iconClass = 'fas fa-info-circle';
                    btnCloseClass += ' btn-close-white';
                    break;
                default:
                    bgColor = 'text-bg-secondary';
                    iconClass = 'fas fa-bell';
                    btnCloseClass += ' btn-close-white';
            }

            // 1. สร้าง HTML ของ Toast ใหม่
            const toastHtml = `
                <div class="toast align-items-center ${bgColor} border-0" role="alert" aria-live="assertive"
                    aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="${iconClass} me-2"></i>${message}
                        </div>
                        <button type="button" class="${btnCloseClass} me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            `;

            // 2. เพิ่ม Toast เข้าไปใน container ที่มีอยู่แล้ว
            const $container = $('.toast-container');
            if ($container.length === 0) {
                // **หมายเหตุ:** ถ้าไม่พบ container อาจต้องแจ้ง error หรือสร้าง container ขึ้นมา
                console.error("Toast container not found!");
                return;
            }
            $container.append(toastHtml);

            // 3. หา Toast ที่เพิ่งถูกสร้าง และสั่งให้แสดงผลด้วย Bootstrap JS
            const toastEl = $container.find('.toast:last');
            const toast = new bootstrap.Toast(toastEl[0]);
            toast.show();

            // 4. ลบ Toast ออกจาก DOM เมื่อซ่อนเสร็จแล้ว เพื่อไม่ให้ HTML ค้าง
            toastEl.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        // --- ส่วนจัดการ AJAX Call ---
        $(document).ready(function() {
            // Toggle notification status
            $(document).on('click', '.toggle-notify-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                let $btn = $(this);
                let subscriberId = $btn.data('id');
                let $row = $('#row_' + subscriberId);

                if ($btn.prop('disabled')) {
                    return false;
                }

                $btn.prop('disabled', true);

                $.ajax({
                    url: '{{ route('admin.updateNotificationStatus') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: subscriberId
                    },
                    success: function(response) {
                        $btn.prop('disabled', false);

                        if (response.success) {
                            let isAllowed = response.allowed;

                            // อัพเดท UI
                            let statusHtml = isAllowed ?
                                '<span style="color: #198754; font-size: 24px; cursor: pointer;" title="ใช้งาน">●</span>' :
                                '<span style="color: #dc3545; font-size: 24px; cursor: pointer;" title="ปิดใช้งาน">●</span>';
                            $row.find('.status-cell').html(statusHtml);

                            let buttonHtml = isAllowed ?
                                '<button type="button" class="btn btn-sm btn-danger toggle-notify-btn" data-id="' +
                                subscriberId +
                                '" title="ปิดแจ้งเตือน"><i class="fa-solid fa-bell-slash"></i></button> ' :
                                '<button type="button" class="btn btn-sm btn-success toggle-notify-btn" data-id="' +
                                subscriberId +
                                '" title="เปิดแจ้งเตือน"><i class="fa-solid fa-bell"></i></button> ';

                            // เพิ่มปุ่มลบกลับเข้าไป
                            buttonHtml += '<button type="button" class="btn btn-sm btn-danger del-notify-btn" data-id="' +
                                subscriberId + '" data-name="' + $row.find('td:eq(3)').text() +
                                '" title="ลบผู้ติดตาม"><i class="fa-solid fa-trash"></i></button>';

                            $row.find('.action-cell').html(buttonHtml);

                            showToast(response.message, 'success');

                        } else {
                            showToast(response.error || 'ไม่สามารถดำเนินการได้', 'danger');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false);

                        let errorDetail = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                        try {
                            let response = xhr.responseJSON;
                            if (response && response.error) {
                                errorDetail = response.error;
                            } else if (response && response.message) {
                                errorDetail = response.message;
                            }
                        } catch (e) {}

                        showToast(errorDetail, 'danger');
                    }
                });
            });

            // Delete subscriber with SweetAlert2 confirmation
            $(document).on('click', '.del-notify-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('Delete button clicked!'); // Debug

                let $btn = $(this);
                let subscriberId = $btn.data('id');
                let subscriberName = $btn.data('name');
                let $row = $('#row_' + subscriberId);

                console.log('Subscriber ID:', subscriberId); // Debug
                console.log('Subscriber Name:', subscriberName); // Debug

                // ตรวจสอบว่า Swal มีหรือไม่
                if (typeof Swal === 'undefined') {
                    alert('SweetAlert2 ไม่ได้โหลด กรุณาเพิ่ม CDN ใน layout');
                    return;
                }

                // แสดง SweetAlert2 confirmation
                Swal.fire({
                    title: 'ยืนยันการลบ',
                    html: `คุณต้องการลบผู้ติดตาม<br><strong>${subscriberName}</strong><br>ออกจากระบบใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> ลบ',
                    cancelButtonText: '<i class="fa-solid fa-times me-1"></i> ยกเลิก',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ปิดการใช้งานปุ่มระหว่างทำงาน
                        $btn.prop('disabled', true);

                        // ส่ง AJAX request
                        $.ajax({
                            url: '{{ url('admin/notify-management/destroy') }}/' + subscriberId,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // แสดง success message
                                    Swal.fire({
                                        title: 'สำเร็จ!',
                                        text: response.message || 'ลบผู้ติดตามสำเร็จ',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });

                                    // ลบแถวออกจากตารางด้วย animation
                                    $row.fadeOut(400, function() {
                                        $(this).remove();

                                        // ตรวจสอบว่ายังมีข้อมูลเหลืออยู่หรือไม่
                                        if ($('tbody tr').length === 0) {
                                            location.reload();
                                        }
                                    });

                                } else {
                                    $btn.prop('disabled', false);
                                    Swal.fire({
                                        title: 'เกิดข้อผิดพลาด!',
                                        text: response.error || 'ไม่สามารถลบผู้ติดตามได้',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr) {
                                $btn.prop('disabled', false);

                                let errorDetail = 'เกิดข้อผิดพลาดในการลบข้อมูล';
                                try {
                                    let response = xhr.responseJSON;
                                    if (response && response.error) {
                                        errorDetail = response.error;
                                    } else if (response && response.message) {
                                        errorDetail = response.message;
                                    }
                                } catch (e) {}

                                Swal.fire({
                                    title: 'เกิดข้อผิดพลาด!',
                                    text: errorDetail,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
