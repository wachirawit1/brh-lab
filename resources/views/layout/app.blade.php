<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <title>@yield('title')</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        kanit: ['Kanit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9', // Sky 500
                            600: '#0284c7', // Sky 600
                            700: '#0369a1', // Sky 700
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    {{-- Font Awesome --}}
    <script src="https://kit.fontawesome.com/1b13c5849c.js" crossorigin="anonymous"></script>

    {{-- Select2 (Default Theme) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">

    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }

        /* Select2 Tailwind Fixes */
        .select2-container .select2-selection--single {
            height: 42px !important;
            border-color: #d1d5db !important;
            border-radius: 0.5rem !important;
            padding-top: 5px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 8px !important;
        }

        /* Pagination Shim */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            gap: 0.25rem;
        }

        .page-item .page-link {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            color: #0284c7;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #0284c7;
            border-color: #0284c7;
        }

        .page-item.disabled .page-link {
            color: #9ca3af;
            pointer-events: none;
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }

        .page-item:not(.active) .page-link:hover {
            background-color: #f0f9ff;
            color: #0369a1;
            border-color: #bae6fd;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col">

    @include('layout.navbar')

    <main class="flex-grow w-full px-4 md:px-8 py-8 mx-auto">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            © {{ date('Y') }} ระบบแจ้งเตือนผลแล็บ — เวอร์ชัน {{ env('APP_VERSION', '1.0.0') }}
            <br>
            พัฒนาโดย <span class="text-brand-600">นาย วชิรวิทย์ กุลสุทธิชัย</span>
        </div>
    </footer>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>

    <script>
        /**
         * Global Toast Notification using SweetAlert2
         */
        window.showToast = function(message, type = 'success') {
            // Map Bootstrap types to SweetAlert types
            const iconMap = {
                'success': 'success',
                'danger': 'error',
                'warning': 'warning',
                'info': 'info'
            };
            const icon = iconMap[type] || 'info';

            const Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#fff',
                color: '#1f2937',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: icon,
                title: message
            });
        }

        // Show Session Flash Messages on Load
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            @if (session('error'))
                showToast("{{ session('error') }}", 'danger');
            @endif
            @if (session('warning'))
                showToast("{{ session('warning') }}", 'warning');
            @endif
            @if (session('info'))
                showToast("{{ session('info') }}", 'info');
            @endif
        });

        // Polling for Telegram Chat IDs
        setInterval(() => {
            fetch("{{ route('get.chatids') }}")
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.new > 0) {
                        Swal.fire({
                            title: 'พบผู้ใช้ใหม่!',
                            text: `พบผู้ใช้ใหม่ ${data.new} คน\nChat ID: ${data.saved.map(c => c.chat_id).join(", ")}`,
                            icon: 'info',
                            confirmButtonText: 'รับทราบ',
                            confirmButtonColor: '#0ea5e9'
                        });
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        }, 10000);
    </script>

    @stack('indexScript')
    @stack('managementScript')
    @stack('notifySettings')
</body>

</html>
