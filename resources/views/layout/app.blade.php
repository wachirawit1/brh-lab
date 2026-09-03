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

    {{-- Instant Theme & Font Size Bootstrapper (No Flash) --}}
    <script>
        window.BRHTheme = {
            themes: ['light', 'dark', 'high-contrast', 'eye-care'],
            fontSizes: ['normal', 'medium', 'large'],
            read(key, fallback) {
                try { return localStorage.getItem(key) || fallback; } catch (_) { return fallback; }
            },
            readTheme() {
                const value = this.read('brh_theme', 'light');
                return this.themes.includes(value) ? value : 'light';
            },
            readFontSize() {
                const value = this.read('brh_font_size', 'normal');
                return this.fontSizes.includes(value) ? value : 'normal';
            },
            applyTheme(theme, persist = true) {
                const value = this.themes.includes(theme) ? theme : 'light';
                document.documentElement.setAttribute('data-theme', value);
                document.documentElement.style.colorScheme = value === 'dark' ? 'dark' : 'light';
                if (persist) { try { localStorage.setItem('brh_theme', value); } catch (_) {} }
                window.dispatchEvent(new CustomEvent('brh-theme-changed', { detail: { theme: value } }));
                return value;
            },
            applyFontSize(size, persist = true) {
                const value = this.fontSizes.includes(size) ? size : 'normal';
                document.documentElement.setAttribute('data-font-size', value);
                if (persist) { try { localStorage.setItem('brh_font_size', value); } catch (_) {} }
                return value;
            }
        };
        window.BRHTheme.applyTheme(window.BRHTheme.readTheme(), false);
        window.BRHTheme.applyFontSize(window.BRHTheme.readFontSize(), false);
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Kanit', sans-serif;
        }

        /* =========================================
           CLINICAL THEME SYSTEM (IMPECCABLE)
           ========================================= */
        :root {
            --bg-canvas: #f9fafb;
            --bg-surface: #ffffff;
            --bg-surface-elevated: #ffffff;
            --bg-surface-hover: #f0f9ff;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --border-color-strong: #d1d5db;
            --brand-primary: #0284c7;
            --brand-hover: #0369a1;
            --brand-light: #e0f2fe;
            --table-header-bg: #1f2937;
            --table-header-text: #ffffff;
            --table-row-hover: rgba(2, 132, 199, 0.08);
        }

        /* 🌙 Dark Mode */
        html[data-theme="dark"] {
            --bg-canvas: #0b1120;
            --bg-surface: #1e293b;
            --bg-surface-elevated: #334155;
            --bg-surface-hover: #1e3a5f;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-color: #334155;
            --border-color-strong: #475569;
            --brand-primary: #38bdf8;
            --brand-hover: #0ea5e9;
            --brand-light: rgba(56, 189, 248, 0.15);
            --table-header-bg: #0f172a;
            --table-header-text: #f8fafc;
            --table-row-hover: rgba(56, 189, 248, 0.10);
        }

        /* 👁️ High Contrast Mode */
        html[data-theme="high-contrast"] {
            --bg-canvas: #ffffff;
            --bg-surface: #ffffff;
            --bg-surface-elevated: #f4f4f5;
            --bg-surface-hover: #e4e4e7;
            --text-primary: #000000;
            --text-secondary: #18181b;
            --text-muted: #3f3f46;
            --border-color: #000000;
            --border-color-strong: #000000;
            --brand-primary: #000000;
            --brand-hover: #27272a;
            --brand-light: #e4e4e7;
            --table-header-bg: #000000;
            --table-header-text: #ffffff;
            --table-row-hover: #f4f4f5;
        }

        /* 🍃 Eye-Care Sage Mode */
        html[data-theme="eye-care"] {
            --bg-canvas: #f2f7f4;
            --bg-surface: #ffffff;
            --bg-surface-elevated: #e6f0e9;
            --bg-surface-hover: #dff0e4;
            --text-primary: #12331f;
            --text-secondary: #275237;
            --text-muted: #527c62;
            --border-color: #cbdcd0;
            --border-color-strong: #adc7b4;
            --brand-primary: #059669;
            --brand-hover: #047857;
            --brand-light: #d1fae5;
            --table-header-bg: #164e34;
            --table-header-text: #ffffff;
            --table-row-hover: rgba(16, 185, 129, 0.08);
        }

        /* Font Size Scaling */
        html[data-font-size="normal"] { font-size: 16px; }
        html[data-font-size="medium"] { font-size: 17.5px; }
        html[data-font-size="large"] { font-size: 19.5px; }

        /* Dynamic Theme Application */
        body {
            background-color: var(--bg-canvas) !important;
            color: var(--text-primary) !important;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        nav, footer, .bg-white {
            background-color: var(--bg-surface) !important;
            border-color: var(--border-color) !important;
        }

        .border-gray-100, .border-gray-200, .border-gray-300 {
            border-color: var(--border-color) !important;
        }

        /* 🌙 Dark Mode Comprehensive Overrides */
        html[data-theme="dark"] .bg-gray-50,
        html[data-theme="dark"] .bg-gray-100 {
            background-color: var(--bg-canvas) !important;
            color: var(--text-primary) !important;
        }

        html[data-theme="dark"] .text-gray-900,
        html[data-theme="dark"] .text-gray-800 {
            color: var(--text-primary) !important;
        }

        html[data-theme="dark"] .text-gray-700,
        html[data-theme="dark"] .text-gray-600,
        html[data-theme="dark"] .text-gray-500 {
            color: var(--text-secondary) !important;
        }

        html[data-theme="dark"] .text-gray-400,
        html[data-theme="dark"] .text-gray-300 {
            color: var(--text-muted) !important;
        }

        html[data-theme="dark"] .divide-gray-50 > :not([hidden]) ~ :not([hidden]),
        html[data-theme="dark"] .divide-gray-100 > :not([hidden]) ~ :not([hidden]),
        html[data-theme="dark"] .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: var(--border-color) !important;
        }

        html[data-theme="dark"] input:not([type="checkbox"]):not([type="radio"]),
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea {
            background-color: #0f172a !important;
            border-color: var(--border-color) !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .select2-container .select2-selection--single {
            background-color: #0f172a !important;
            border-color: var(--border-color) !important;
        }

        html[data-theme="dark"] .select2-container .select2-selection--single .select2-selection__rendered {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .select2-dropdown {
            background-color: #1e293b !important;
            border-color: var(--border-color) !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .select2-results__option {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .select2-results__option--highlighted {
            background-color: #0284c7 !important;
            color: #ffffff !important;
        }

        /* 👁️ High Contrast Mode Overrides */
        html[data-theme="high-contrast"] .bg-gray-50,
        html[data-theme="high-contrast"] .bg-gray-100 {
            background-color: #f4f4f5 !important;
        }
        html[data-theme="high-contrast"] [data-amr-row],
        html[data-theme="high-contrast"] tr {
            border-bottom: 1.5px solid #000000 !important;
        }
        html[data-theme="high-contrast"] button,
        html[data-theme="high-contrast"] input,
        html[data-theme="high-contrast"] select,
        html[data-theme="high-contrast"] .rounded-2xl,
        html[data-theme="high-contrast"] .rounded-3xl {
            border-color: #000000 !important;
        }

        /* 🍃 Eye-Care Sage Mode Overrides */
        html[data-theme="eye-care"] .bg-gray-50,
        html[data-theme="eye-care"] .bg-gray-100 {
            background-color: #eaf2ec !important;
        }
        html[data-theme="eye-care"] input:not([type="checkbox"]):not([type="radio"]),
        html[data-theme="eye-care"] select {
            background-color: #ffffff !important;
            border-color: var(--border-color) !important;
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

        html {
            scrollbar-gutter: stable;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        html.brh-modal-open,
        body.brh-modal-open,
        html.swal2-shown,
        body.swal2-shown {
            overflow: hidden !important;
            overscroll-behavior: none;
        }

        body.swal2-shown {
            padding-right: 0 !important;
        }

        [aria-modal="true"],
        .swal2-container {
            overscroll-behavior: contain;
        }

        /* Prevent Select2 from creating offscreen horizontal overflow */
        .select2-hidden-accessible {
            border: 0 !important;
            clip: rect(0 0 0 0) !important;
            -webkit-clip-path: inset(50%) !important;
            clip-path: inset(50%) !important;
            height: 1px !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            width: 1px !important;
            white-space: nowrap !important;
            left: 0 !important;
            top: 0 !important;
        }
    </style>
    @include('layout.theme-styles')
</head>

<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col overflow-x-hidden">

    @include('layout.navbar')

    <main class="flex-grow w-full px-4 md:px-8 py-8 mx-auto">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 mt-auto">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            © {{ date('Y') }} ระบบแจ้งเตือนผลแล็บ — เวอร์ชัน {{ config('app.version') }} (ปล่อยวันที่ {{ config('app.release_date') }})
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
        window.BRHModalScroll = (() => {
            const locks = new Set();
            const className = 'brh-modal-open';

            const sync = () => {
                const locked = locks.size > 0;
                document.documentElement.classList.toggle(className, locked);
                document.body.classList.toggle(className, locked);
            };

            return {
                set(key, locked) {
                    if (locked) locks.add(key);
                    else locks.delete(key);
                    sync();
                },
                isLocked() {
                    return locks.size > 0;
                }
            };
        })();

        window.buildAmrOrganismOptions = function(masterOrganisms, selectedCodes, idPrefix) {
            const escapeHtml = (value) => {
                const element = document.createElement('div');
                element.textContent = value == null ? '' : String(value);
                return element.innerHTML;
            };
            const selected = new Set(Array.isArray(selectedCodes) ? selectedCodes : []);
            const organisms = Array.isArray(masterOrganisms) ? masterOrganisms : [];

            if (!organisms.length) {
                return '<p class="col-span-full rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">ไม่พบรายการเชื้อที่เปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ</p>';
            }

            return organisms.map((organism, index) => {
                const code = escapeHtml(organism.code);
                const details = [organism.full_name, organism.description].filter(Boolean).join(' — ');

                return `
                    <label class="flex min-w-0 items-start gap-2.5 rounded-xl border border-gray-200 bg-white p-2.5 text-left shadow-xs transition hover:border-sky-300 hover:bg-sky-50/60 cursor-pointer select-none">
                        <input type="checkbox" data-amr-organism id="${escapeHtml(idPrefix)}_${code}" value="${code}" ${selected.has(organism.code) ? 'checked' : ''} class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="min-w-0">
                            <span class="block text-xs font-bold text-gray-800">${index + 1}. ${escapeHtml(organism.name)}</span>
                            <span class="mt-0.5 block break-words text-[10px] leading-4 text-gray-500">${escapeHtml(details || organism.name)}</span>
                        </span>
                    </label>`;
            }).join('');
        };

        window.getSelectedAmrOrganisms = function(container) {
            return Array.from(container.querySelectorAll('[data-amr-organism]:checked')).map((input) => input.value);
        };

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
                background: 'var(--bg-surface)',
                color: 'var(--text-primary)',
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
                showToast(@json(session('success')), 'success');
            @endif
            @if (session('error'))
                showToast(@json(session('error')), 'danger');
            @endif
            @if (session('warning'))
                showToast(@json(session('warning')), 'warning');
            @endif
            @if (session('info'))
                showToast(@json(session('info')), 'info');
            @endif
        });


        // ===== SESSION GUARD =====
        let sessionAlertShown = false; // กันไม่ให้ Alert ขึ้นซ้ำ

        // ฟังก์ชันแสดง Alert แล้วเด้งไป Login
        function showSessionExpiredAlert() {
            if (sessionAlertShown) return;
            sessionAlertShown = true;
            Swal.fire({
                title: 'Session หมดอายุ',
                text: 'คุณไม่ได้ใช้งานระบบเป็นเวลานาน กรุณาเข้าสู่ระบบใหม่',
                icon: 'warning',
                confirmButtonText: 'เข้าสู่ระบบ',
                confirmButtonColor: 'var(--brand-solid)',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(() => {
                window.location.href = "{{ route('loginForm') }}";
            });
        }

        // ชั้นที่ 1: Polling เช็ค Session ทุก 5 นาที
        setInterval(() => {
            fetch("{{ url('/check-session') }}")
                .then(res => {
                    if (res.status === 401) {
                        showSessionExpiredAlert();
                    }
                })
                .catch(() => {}); // ถ้า network error ก็ข้ามไป
        }, 5 * 60 * 1000); // 5 นาที

        // ชั้นที่ 2: ดักจับ Error 419 จาก jQuery AJAX ทุกตัว
        $(document).ajaxError(function(event, jqXHR) {
            if (jqXHR.status === 419) {
                showSessionExpiredAlert();
            }
        });
    </script>

    @stack('indexScript')
    @stack('managementScript')
    @stack('notifySettings')
</body>

</html>
