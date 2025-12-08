<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
    {{-- select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    {{-- flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

    <style>
        .lab-names {
            max-width: 300px;
        }

        .badge {
            font-size: 0.85em;
            font-weight: normal;
        }

        tr.cursor-pointer {
            cursor: pointer;
        }

        /* ปุ่มลอย */
        #chatButton {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        /* กล่องแชท */
        #chatBox {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 320px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1050;
            display: none;
        }
    </style>
</head>

<body>
    @include('layout.navbar')

    {{-- Toast Container - วางไว้ด้านล่างขวาของหน้าจอ --}}
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <!-- Toast สำหรับข้อความสำเร็จ -->
        @if (session('success'))
            <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Toast สำหรับข้อผิดพลาด -->
        @if (session('error'))
            <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Toast สำหรับข้อความแจ้งเตือน -->
        @if (session('warning'))
            <div class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('warning') }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Toast สำหรับข้อความทั่วไป -->
        @if (session('info'))
            <div class="toast align-items-center text-bg-info border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <div class="container-fluid my-4">
        @yield('content')
    </div>

    <!-- Footer: เครดิตและเวอร์ชัน -->
    <footer class="bg-light text-center text-muted py-3 mt-4 mt-auto">
        <div class="container">
            <small>
                © {{ date('Y') }} Project Name — เวอร์ชัน {{ env('APP_VERSION', '1.0.0') }}.
                พัฒนาโดย <a href="http://192.168.10.11:8080" target="_blank" class="text-decoration-none">นาย วชิรวิทย์ กุลสุทธิชัย</a>
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://kit.fontawesome.com/1b13c5849c.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
    <script>
        // แสดง Toast notifications อัตโนมัติเมื่อหน้าโหลด
        document.addEventListener('DOMContentLoaded', function() {
            // เลือก toast ทั้งหมด
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));

            // แสดง toast แต่ละอัน
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show(); // แสดง toast
                return toast;
            });
        });

        // ตั้งให้ยิงทุกๆ 10 วินาที (เปลี่ยนได้ตามต้องการ)
        setInterval(() => {
            fetch("{{ route('get.chatids') }}")
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // console.log("อัปเดตแล้ว:", data);

                        // ถ้ามี user ใหม่ ให้ popup บอก
                        if (data.new > 0) {
                            alert(
                                `✅ พบผู้ใช้ใหม่ ${data.new} คน\nChat ID: ${data.saved.map(c => c.chat_id).join(", ")}`
                            );
                        }
                    } else {
                        console.error("Error:", data.error);
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        }, 10000); // 10 วิ
    </script>
    @stack('indexScript')
    @stack('managementScript')
    @stack('notifySettings')
</body>

</html>
