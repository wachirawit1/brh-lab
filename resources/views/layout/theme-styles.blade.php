<style>
    /* Runtime theme completion layer: semantic tokens mapped onto incumbent Tailwind utilities. */
    :root {
        --bg-subtle: #f9fafb;
        --bg-muted: #f3f4f6;
        --bg-inverse: #1f2937;
        --text-on-inverse: #ffffff;
        --input-bg: #ffffff;
        --brand-solid: #0284c7;
        --brand-solid-hover: #0369a1;
        --brand-on-solid: #ffffff;
        --neutral-solid: #6b7280;
        --accent-purple-bg: #faf5ff;
        --accent-purple-text: #7e22ce;
        --accent-purple-border: #e9d5ff;
        --brand-text: #0369a1;
        --brand-soft: #e0f2fe;
        --brand-soft-hover: #bae6fd;
        --focus-color: #0ea5e9;
        --status-danger-bg: #fee2e2;
        --status-danger-text: #991b1b;
        --status-danger-border: #fecaca;
        --status-danger-solid: #dc2626;
        --status-warning-bg: #fef3c7;
        --status-warning-text: #92400e;
        --status-warning-border: #fde68a;
        --status-warning-solid: #b45309;
        --status-success-bg: #d1fae5;
        --status-success-text: #065f46;
        --status-success-border: #a7f3d0;
        --status-success-solid: #047857;
        --status-info-bg: #e0f2fe;
        --status-info-text: #075985;
        --status-info-border: #bae6fd;
        --overlay-color: rgb(15 23 42 / 0.62);
    }

    html[data-theme="dark"] {
        --bg-canvas: #0b1120;
        --bg-surface: #172033;
        --bg-surface-elevated: #1e293b;
        --bg-surface-hover: #24334a;
        --bg-subtle: #111a2c;
        --bg-muted: #243147;
        --bg-inverse: #0f172a;
        --text-primary: #f8fafc;
        --text-secondary: #cbd5e1;
        --text-muted: #94a3b8;
        --text-on-inverse: #f8fafc;
        --border-color: #334155;
        --border-color-strong: #64748b;
        --input-bg: #0f172a;
        --brand-solid: #0284c7;
        --brand-solid-hover: #0ea5e9;
        --brand-on-solid: #ffffff;
        --neutral-solid: #475569;
        --accent-purple-bg: #2e1065;
        --accent-purple-text: #e9d5ff;
        --accent-purple-border: #6b21a8;
        --brand-text: #7dd3fc;
        --brand-soft: #16324b;
        --brand-soft-hover: #1d4667;
        --focus-color: #38bdf8;
        --status-danger-bg: #3b1820;
        --status-danger-text: #fecaca;
        --status-danger-border: #7f1d1d;
        --status-danger-solid: #b91c1c;
        --status-warning-bg: #3a2812;
        --status-warning-text: #fde68a;
        --status-warning-border: #78350f;
        --status-warning-solid: #b45309;
        --status-success-bg: #0d3329;
        --status-success-text: #a7f3d0;
        --status-success-border: #065f46;
        --status-success-solid: #047857;
        --status-info-bg: #102f49;
        --status-info-text: #bae6fd;
        --status-info-border: #075985;
        --overlay-color: rgb(2 6 23 / 0.76);
        color-scheme: dark;
    }

    html[data-theme="high-contrast"] {
        --bg-canvas: #ffffff;
        --bg-surface: #ffffff;
        --bg-surface-elevated: #ffffff;
        --bg-surface-hover: #e5e5e5;
        --bg-subtle: #ffffff;
        --bg-muted: #f0f0f0;
        --bg-inverse: #000000;
        --text-primary: #000000;
        --text-secondary: #000000;
        --text-muted: #262626;
        --text-on-inverse: #ffffff;
        --border-color: #000000;
        --border-color-strong: #000000;
        --input-bg: #ffffff;
        --brand-solid: #000000;
        --brand-solid-hover: #262626;
        --brand-on-solid: #ffffff;
        --neutral-solid: #000000;
        --accent-purple-bg: #ffffff;
        --accent-purple-text: #000000;
        --accent-purple-border: #000000;
        --brand-text: #000000;
        --brand-soft: #ffffff;
        --brand-soft-hover: #e5e5e5;
        --focus-color: #000000;
        --status-danger-bg: #ffffff;
        --status-danger-text: #000000;
        --status-danger-border: #000000;
        --status-danger-solid: #000000;
        --status-warning-bg: #ffffff;
        --status-warning-text: #000000;
        --status-warning-border: #000000;
        --status-warning-solid: #000000;
        --status-success-bg: #ffffff;
        --status-success-text: #000000;
        --status-success-border: #000000;
        --status-success-solid: #000000;
        --status-info-bg: #ffffff;
        --status-info-text: #000000;
        --status-info-border: #000000;
        --overlay-color: rgb(0 0 0 / 0.72);
        color-scheme: light;
    }

    html[data-theme="eye-care"] {
        --bg-canvas: #eef5f0;
        --bg-surface: #fbfefc;
        --bg-surface-elevated: #ffffff;
        --bg-surface-hover: #dff0e4;
        --bg-subtle: #e8f2eb;
        --bg-muted: #dcebe1;
        --bg-inverse: #164e34;
        --text-primary: #12331f;
        --text-secondary: #275237;
        --text-muted: #466b53;
        --text-on-inverse: #ffffff;
        --border-color: #b9d0c0;
        --border-color-strong: #8fb49a;
        --input-bg: #ffffff;
        --brand-solid: #047857;
        --brand-solid-hover: #065f46;
        --brand-on-solid: #ffffff;
        --neutral-solid: #466b53;
        --accent-purple-bg: #f3e8ff;
        --accent-purple-text: #6b21a8;
        --accent-purple-border: #c4b5fd;
        --brand-text: #047857;
        --brand-soft: #d1fae5;
        --brand-soft-hover: #a7f3d0;
        --focus-color: #059669;
        --status-danger-bg: #fee2e2;
        --status-danger-text: #991b1b;
        --status-danger-border: #fca5a5;
        --status-danger-solid: #b91c1c;
        --status-warning-bg: #fef3c7;
        --status-warning-text: #78350f;
        --status-warning-border: #fcd34d;
        --status-warning-solid: #92400e;
        --status-success-bg: #d1fae5;
        --status-success-text: #065f46;
        --status-success-border: #6ee7b7;
        --status-success-solid: #047857;
        --status-info-bg: #dcfce7;
        --status-info-text: #166534;
        --status-info-border: #86efac;
        --overlay-color: rgb(18 51 31 / 0.62);
        color-scheme: light;
    }

    html[data-theme="light"] { color-scheme: light; }

    /* Core surfaces, including opacity utility variants used by the settings and AMR screens. */
    html[data-theme] body,
    html[data-theme] .theme-canvas { background-color: var(--bg-canvas) !important; color: var(--text-primary) !important; }
    html[data-theme] nav,
    html[data-theme] footer,
    html[data-theme] .bg-white,
    html[data-theme] .theme-surface { background-color: var(--bg-surface) !important; }
    html[data-theme] .bg-gray-50,
    html[data-theme] [class~="bg-gray-50/40"],
    html[data-theme] [class~="bg-gray-50/50"],
    html[data-theme] [class~="bg-gray-50/70"],
    html[data-theme] [class~="bg-gray-50/80"],
    html[data-theme] .bg-slate-50,
    html[data-theme] [class~="bg-slate-50/70"],
    html[data-theme] [class~="bg-slate-50/80"] { background-color: var(--bg-subtle) !important; }
    html[data-theme] .bg-gray-100,
    html[data-theme] .bg-gray-200,
    html[data-theme] .bg-slate-100,
    html[data-theme] .bg-slate-200 { background-color: var(--bg-muted) !important; }
    html[data-theme] .bg-gray-800,
    html[data-theme] .bg-gray-900,
    html[data-theme] .bg-slate-700,
    html[data-theme] .bg-slate-800,
    html[data-theme] .bg-slate-900 { background-color: var(--bg-inverse) !important; color: var(--text-on-inverse) !important; }
    html[data-theme] [class~="hover:bg-gray-50"]:hover,
    html[data-theme] [class~="hover:bg-gray-100"]:hover,
    html[data-theme] [class~="hover:bg-gray-100/80"]:hover,
    html[data-theme] [class~="hover:bg-slate-50"]:hover,
    html[data-theme] [class~="hover:bg-slate-100"]:hover { background-color: var(--bg-surface-hover) !important; }

    /* Neutral typography. */
    html[data-theme] .text-gray-950,
    html[data-theme] .text-gray-900,
    html[data-theme] .text-gray-800,
    html[data-theme] .text-slate-950,
    html[data-theme] .text-slate-900,
    html[data-theme] .text-slate-800,
    html[data-theme] .text-slate-700 { color: var(--text-primary) !important; }
    html[data-theme] .text-gray-700,
    html[data-theme] .text-gray-600,
    html[data-theme] .text-gray-500,
    html[data-theme] .text-slate-600,
    html[data-theme] .text-slate-500 { color: var(--text-secondary) !important; }
    html[data-theme] .text-gray-400,
    html[data-theme] .text-gray-300,
    html[data-theme] .text-slate-400,
    html[data-theme] .text-slate-300 { color: var(--text-muted) !important; }
    html[data-theme] [class~="hover:text-gray-900"]:hover,
    html[data-theme] [class~="hover:text-gray-800"]:hover,
    html[data-theme] [class~="hover:text-slate-900"]:hover { color: var(--text-primary) !important; }

    /* Borders, rings and dividers. */
    html[data-theme] .border-gray-100,
    html[data-theme] .border-gray-200,
    html[data-theme] .border-gray-300,
    html[data-theme] .border-slate-100,
    html[data-theme] .border-slate-200,
    html[data-theme] .border-slate-300,
    html[data-theme] .border-slate-600,
    html[data-theme] .border-slate-700 { border-color: var(--border-color) !important; }
    html[data-theme] .ring-gray-100,
    html[data-theme] .ring-gray-200,
    html[data-theme] .ring-gray-300,
    html[data-theme] .ring-slate-200,
    html[data-theme] .ring-slate-700 { --tw-ring-color: var(--border-color) !important; }
    html[data-theme] .divide-gray-50 > :not([hidden]) ~ :not([hidden]),
    html[data-theme] .divide-gray-100 > :not([hidden]) ~ :not([hidden]),
    html[data-theme] .divide-gray-200 > :not([hidden]) ~ :not([hidden]),
    html[data-theme] .divide-slate-100 > :not([hidden]) ~ :not([hidden]) { border-color: var(--border-color) !important; }

    /* Brand utilities now follow the selected theme. */
    html[data-theme] .bg-brand-500,
    html[data-theme] .bg-brand-600,
    html[data-theme] .bg-brand-700,
    html[data-theme] .bg-sky-500,
    html[data-theme] .bg-sky-600 { background-color: var(--brand-solid) !important; color: var(--brand-on-solid) !important; }
    html[data-theme] .bg-brand-50,
    html[data-theme] .bg-brand-100,
    html[data-theme] [class~="bg-brand-50/40"],
    html[data-theme] [class~="bg-brand-50/50"],
    html[data-theme] [class~="bg-brand-50/60"],
    html[data-theme] [class~="bg-brand-50/70"],
    html[data-theme] [class~="bg-sky-50/40"],
    html[data-theme] [class~="bg-sky-50/50"],
    html[data-theme] [class~="bg-sky-50/60"],
    html[data-theme] [class~="bg-sky-50/70"],
    html[data-theme] [class~="bg-sky-50/80"],
    html[data-theme] [class~="!bg-sky-100/70"],
    html[data-theme] [class~="!bg-sky-100/75"] { background-color: var(--brand-soft) !important; }
    html[data-theme] .text-brand-500,
    html[data-theme] .text-brand-600,
    html[data-theme] .text-brand-700,
    html[data-theme] .text-brand-800,
    html[data-theme] .text-sky-500,
    html[data-theme] .text-sky-600,
    html[data-theme] .text-sky-700,
    html[data-theme] .text-sky-800,
    html[data-theme] .text-sky-900,
    html[data-theme] .text-sky-950 { color: var(--brand-text) !important; }
    html[data-theme] .border-brand-200,
    html[data-theme] .border-brand-300,
    html[data-theme] .border-brand-500,
    html[data-theme] .border-sky-100,
    html[data-theme] .border-sky-200,
    html[data-theme] .border-sky-300,
    html[data-theme] .border-sky-400 { border-color: var(--focus-color) !important; }
    html[data-theme] .ring-brand-200,
    html[data-theme] .ring-brand-500,
    html[data-theme] .ring-sky-400,
    html[data-theme] .ring-sky-500 { --tw-ring-color: var(--focus-color) !important; }
    html[data-theme] [class~="hover:bg-brand-50"]:hover,
    html[data-theme] [class~="hover:bg-brand-100"]:hover,
    html[data-theme] [class~="hover:bg-sky-50"]:hover,
    html[data-theme] [class~="hover:bg-sky-50/60"]:hover,
    html[data-theme] [class~="hover:bg-sky-100"]:hover { background-color: var(--brand-soft-hover) !important; }
    html[data-theme] [class~="hover:bg-brand-700"]:hover,
    html[data-theme] [class~="hover:bg-sky-600"]:hover,
    html[data-theme] [class~="hover:bg-sky-700"]:hover { background-color: var(--brand-solid-hover) !important; color: var(--brand-on-solid) !important; }
    html[data-theme] [class~="hover:text-brand-600"]:hover,
    html[data-theme] [class~="hover:text-brand-700"]:hover,
    html[data-theme] [class~="hover:text-brand-800"]:hover { color: var(--brand-text) !important; }

    /* Clinical status colors stay semantically distinct in every theme. */
    html[data-theme] .bg-red-50,
    html[data-theme] .bg-red-100,
    html[data-theme] [class~="bg-red-50/50"] { background-color: var(--status-danger-bg) !important; }
    html[data-theme] .text-red-500,
    html[data-theme] .text-red-600,
    html[data-theme] .text-red-700,
    html[data-theme] .text-red-800,
    html[data-theme] .text-red-900 { color: var(--status-danger-text) !important; }
    html[data-theme] .border-red-100,
    html[data-theme] .border-red-200,
    html[data-theme] .border-red-300,
    html[data-theme] .ring-red-200 { border-color: var(--status-danger-border) !important; --tw-ring-color: var(--status-danger-border) !important; }
    html[data-theme] .bg-red-500,
    html[data-theme] .bg-red-600,
    html[data-theme] .bg-red-700 { background-color: var(--status-danger-solid) !important; color: #ffffff !important; }

    html[data-theme] .bg-amber-50,
    html[data-theme] .bg-amber-100,
    html[data-theme] [class~="bg-amber-50/50"],
    html[data-theme] [class~="bg-amber-100/80"] { background-color: var(--status-warning-bg) !important; }
    html[data-theme] .text-amber-500,
    html[data-theme] .text-amber-600,
    html[data-theme] .text-amber-700,
    html[data-theme] .text-amber-800,
    html[data-theme] .text-amber-900 { color: var(--status-warning-text) !important; }
    html[data-theme] .border-amber-100,
    html[data-theme] .border-amber-200,
    html[data-theme] .border-amber-300 { border-color: var(--status-warning-border) !important; }
    html[data-theme] .bg-amber-500,
    html[data-theme] .bg-amber-600,
    html[data-theme] .bg-amber-700 { background-color: var(--status-warning-solid) !important; color: #ffffff !important; }

    html[data-theme] .bg-emerald-50,
    html[data-theme] .bg-emerald-100,
    html[data-theme] [class~="bg-emerald-50/50"] { background-color: var(--status-success-bg) !important; }
    html[data-theme] .text-emerald-500,
    html[data-theme] .text-emerald-600,
    html[data-theme] .text-emerald-700,
    html[data-theme] .text-emerald-800,
    html[data-theme] .text-emerald-900,
    html[data-theme] .text-emerald-950 { color: var(--status-success-text) !important; }
    html[data-theme] .border-emerald-100,
    html[data-theme] .border-emerald-200,
    html[data-theme] .border-emerald-300,
    html[data-theme] .ring-emerald-500 { border-color: var(--status-success-border) !important; --tw-ring-color: var(--status-success-border) !important; }
    html[data-theme] .bg-emerald-500,
    html[data-theme] .bg-emerald-600,
    html[data-theme] .bg-emerald-700 { background-color: var(--status-success-solid) !important; color: #ffffff !important; }

    html[data-theme] .bg-sky-50,
    html[data-theme] .bg-sky-100 { background-color: var(--status-info-bg) !important; }
    html[data-theme] .border-sky-100,
    html[data-theme] .border-sky-200 { border-color: var(--status-info-border) !important; }

    /* Extended palette coverage for clinical chips and administrative metadata. */
    html[data-theme] .bg-gray-300 { background-color: var(--bg-muted) !important; }
    html[data-theme] .bg-gray-500 { background-color: var(--overlay-color) !important; }
    html[data-theme] [class~="bg-gray-50/60"] { background-color: var(--bg-subtle) !important; }
    html[data-theme] .text-gray-200 { color: var(--brand-on-solid) !important; }

    html[data-theme] .text-brand-50,
    html[data-theme] .text-brand-100 { color: var(--brand-on-solid) !important; }
    html[data-theme] .text-brand-400 { color: var(--focus-color) !important; }
    html[data-theme] .border-brand-50,
    html[data-theme] .border-brand-100 { border-color: var(--focus-color) !important; }
    html[data-theme] [class~="ring-brand-200/80"],
    html[data-theme] [class~="ring-brand-500/20"],
    html[data-theme] .ring-sky-200 { --tw-ring-color: var(--focus-color) !important; }
    html[data-theme] [class~="bg-sky-100/80"] { background-color: var(--status-info-bg) !important; }

    html[data-theme] .bg-blue-50,
    html[data-theme] .bg-indigo-50 { background-color: var(--status-info-bg) !important; }
    html[data-theme] .text-blue-400,
    html[data-theme] .text-blue-500,
    html[data-theme] .text-blue-600,
    html[data-theme] .text-blue-700,
    html[data-theme] .text-blue-800,
    html[data-theme] .text-indigo-500,
    html[data-theme] .text-indigo-600,
    html[data-theme] .text-indigo-700 { color: var(--status-info-text) !important; }
    html[data-theme] .border-blue-50,
    html[data-theme] .border-blue-200,
    html[data-theme] .border-indigo-100,
    html[data-theme] .border-indigo-200 { border-color: var(--status-info-border) !important; }

    html[data-theme] .bg-teal-50,
    html[data-theme] .bg-teal-100 { background-color: var(--status-success-bg) !important; }
    html[data-theme] .text-teal-600,
    html[data-theme] .text-teal-700,
    html[data-theme] .text-teal-800,
    html[data-theme] .text-teal-900 { color: var(--status-success-text) !important; }
    html[data-theme] .border-teal-100,
    html[data-theme] [class~="border-teal-200/60"] { border-color: var(--status-success-border) !important; }
    html[data-theme] .bg-emerald-400 { background-color: var(--status-success-solid) !important; }
    html[data-theme] .border-emerald-50 { border-color: var(--status-success-border) !important; }

    html[data-theme] .bg-orange-50 { background-color: var(--status-warning-bg) !important; }
    html[data-theme] .text-orange-500,
    html[data-theme] .text-orange-600,
    html[data-theme] .text-orange-700 { color: var(--status-warning-text) !important; }
    html[data-theme] .border-orange-200 { border-color: var(--status-warning-border) !important; }

    html[data-theme] .bg-rose-50,
    html[data-theme] .bg-pink-50,
    html[data-theme] [class~="bg-red-50/70"] { background-color: var(--status-danger-bg) !important; }
    html[data-theme] .text-rose-500,
    html[data-theme] .text-rose-600,
    html[data-theme] .text-rose-700,
    html[data-theme] .text-pink-500,
    html[data-theme] .text-pink-600,
    html[data-theme] .text-pink-700 { color: var(--status-danger-text) !important; }
    html[data-theme] .border-rose-200,
    html[data-theme] .border-pink-200 { border-color: var(--status-danger-border) !important; }

    html[data-theme] .bg-purple-50 { background-color: var(--accent-purple-bg) !important; }
    html[data-theme] .text-purple-500,
    html[data-theme] .text-purple-600,
    html[data-theme] .text-purple-700 { color: var(--accent-purple-text) !important; }
    html[data-theme] .border-purple-200 { border-color: var(--accent-purple-border) !important; }
    /* Forms and browser-native controls. */
    html[data-theme] input:not([type="checkbox"]):not([type="radio"]),
    html[data-theme] select,
    html[data-theme] textarea {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color-strong) !important;
        color: var(--text-primary) !important;
    }
    html[data-theme] input::placeholder,
    html[data-theme] textarea::placeholder { color: var(--text-muted) !important; opacity: 1; }
    html[data-theme] input[type="checkbox"],
    html[data-theme] input[type="radio"] { accent-color: var(--brand-solid); }
    html[data-theme] :focus-visible { outline-color: var(--focus-color); }

    /* Select2. */
    html[data-theme] .select2-container .select2-selection--single,
    html[data-theme] .select2-dropdown,
    html[data-theme] .select2-search__field {
        background-color: var(--input-bg) !important;
        border-color: var(--border-color-strong) !important;
        color: var(--text-primary) !important;
    }
    html[data-theme] .select2-selection__rendered,
    html[data-theme] .select2-results__option { color: var(--text-primary) !important; }
    html[data-theme] .select2-results__option--highlighted {
        background-color: var(--brand-solid) !important;
        color: var(--brand-on-solid) !important;
    }

    /* Flatpickr. */
    html[data-theme] .flatpickr-calendar,
    html[data-theme] .flatpickr-months,
    html[data-theme] .flatpickr-weekdays,
    html[data-theme] span.flatpickr-weekday {
        background: var(--bg-surface-elevated) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    html[data-theme] .flatpickr-current-month,
    html[data-theme] .flatpickr-current-month input.cur-year,
    html[data-theme] .flatpickr-monthDropdown-months,
    html[data-theme] .flatpickr-day { color: var(--text-primary) !important; }
    html[data-theme] .flatpickr-day:hover,
    html[data-theme] .flatpickr-day:focus { background: var(--bg-surface-hover) !important; border-color: var(--border-color-strong) !important; }
    html[data-theme] .flatpickr-day.selected,
    html[data-theme] .flatpickr-day.startRange,
    html[data-theme] .flatpickr-day.endRange { background: var(--brand-solid) !important; border-color: var(--brand-solid) !important; color: var(--brand-on-solid) !important; }
    html[data-theme] .flatpickr-day.prevMonthDay,
    html[data-theme] .flatpickr-day.nextMonthDay { color: var(--text-muted) !important; }
    html[data-theme] .flatpickr-prev-month svg,
    html[data-theme] .flatpickr-next-month svg { fill: var(--text-primary) !important; }

    /* Pagination. */
    html[data-theme] .page-item .page-link {
        color: var(--brand-text) !important;
        background-color: var(--bg-surface) !important;
        border-color: var(--border-color) !important;
    }
    html[data-theme] .page-item.active .page-link {
        color: var(--brand-on-solid) !important;
        background-color: var(--brand-solid) !important;
        border-color: var(--brand-solid) !important;
    }
    html[data-theme] .page-item.disabled .page-link {
        color: var(--text-muted) !important;
        background-color: var(--bg-muted) !important;
        border-color: var(--border-color) !important;
    }
    html[data-theme] .page-item:not(.active) .page-link:hover {
        color: var(--brand-text) !important;
        background-color: var(--brand-soft) !important;
        border-color: var(--focus-color) !important;
    }

    /* SweetAlert2 and overlays. */
    html[data-theme] .swal2-popup,
    html[data-theme] .swal2-toast {
        background: var(--bg-surface-elevated) !important;
        color: var(--text-primary) !important;
    }
    html[data-theme] .swal2-title,
    html[data-theme] .swal2-html-container { color: var(--text-primary) !important; }
    html[data-theme] .swal2-timer-progress-bar { background: var(--brand-solid) !important; }
    html[data-theme] [class~="bg-gray-900/60"] { background-color: var(--overlay-color) !important; }

    /* Windows forced-colors keeps controls and selected states visible. */
    @media (forced-colors: active) {
        [aria-pressed="true"], [aria-selected="true"], button:focus-visible, a:focus-visible {
            outline: 2px solid CanvasText !important;
            outline-offset: 2px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        body { transition-duration: 0.01ms !important; }
    }

    /* Theme previews must show their target palette, not inherit the currently active theme. */
    [data-theme-preview="light"] { background: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    [data-theme-preview="light"] .bg-white { background: #ffffff !important; }
    [data-theme-preview="light"] .bg-gray-100 { background: #f3f4f6 !important; }
    [data-theme-preview="light"] .bg-sky-100 { background: #e0f2fe !important; }
    [data-theme-preview="light"] .bg-sky-500 { background: #0ea5e9 !important; }
    [data-theme-preview="light"] .text-gray-900 { color: #111827 !important; }
    [data-theme-preview="light"] .text-gray-500 { color: #6b7280 !important; }
    [data-theme-preview="light"] .text-sky-600 { color: #0284c7 !important; }

    [data-theme-preview="dark"] { background: #0f172a !important; color: #ffffff !important; border-color: #475569 !important; }
    [data-theme-preview="dark"] .bg-slate-800 { background: #1e293b !important; }
    [data-theme-preview="dark"] .bg-slate-900 { background: #0f172a !important; }
    [data-theme-preview="dark"] .text-slate-400 { color: #94a3b8 !important; }
    [data-theme-preview="dark"] .text-sky-400 { color: #38bdf8 !important; }

    [data-theme-preview="high-contrast"] { background: #ffffff !important; color: #000000 !important; border-color: #000000 !important; }
    [data-theme-preview="high-contrast"] .bg-white { background: #ffffff !important; }
    [data-theme-preview="high-contrast"] .bg-black { background: #000000 !important; }
    [data-theme-preview="high-contrast"] .text-black { color: #000000 !important; }
    [data-theme-preview="high-contrast"] .text-gray-600 { color: #262626 !important; }

    [data-theme-preview="eye-care"] { background: #f0fdf4 !important; color: #12331f !important; border-color: #a7f3d0 !important; }
    [data-theme-preview="eye-care"] .bg-white { background: #ffffff !important; }
    [data-theme-preview="eye-care"] .bg-emerald-50 { background: #ecfdf5 !important; }
    [data-theme-preview="eye-care"] .bg-emerald-100 { background: #d1fae5 !important; }
    [data-theme-preview="eye-care"] .bg-emerald-600 { background: #059669 !important; }
    [data-theme-preview="eye-care"] .text-emerald-700 { color: #047857 !important; }
    [data-theme-preview="eye-care"] .text-emerald-950 { color: #022c22 !important; }
</style>