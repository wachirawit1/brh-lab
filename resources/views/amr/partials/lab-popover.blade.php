@php
    $labSegments = collect(explode('|', (string) $value))
        ->map(fn ($segment) => trim($segment))
        ->filter()
        ->values();
@endphp

@if ($labSegments->isNotEmpty())
    <div
        x-id="['lab-panel']"
        x-data="{
            open: false,
            top: 0,
            left: 0,
            closeTimer: null,
            currentRow: null,
            place(trigger) {
                window.clearTimeout(this.closeTimer);
                const rect = trigger.getBoundingClientRect();
                const panelWidth = Math.min(432, window.innerWidth - 32);
                const panelHeight = Math.min(this.$refs.panel.scrollHeight, window.innerHeight - 32);
                const spaceBelow = window.innerHeight - rect.bottom;
                this.left = Math.max(16, Math.min(rect.left, window.innerWidth - panelWidth - 16));
                const preferredTop = spaceBelow >= panelHeight + 10 || rect.top < panelHeight + 10
                    ? rect.bottom + 10
                    : rect.top - panelHeight - 10;
                this.top = Math.max(16, Math.min(preferredTop, window.innerHeight - panelHeight - 16));
            },
            show(trigger) {
                this.open = true;
                this.currentRow = trigger.closest('tr');
                if (this.currentRow) {
                    this.currentRow.classList.add('!bg-sky-100/70');
                }
                this.$nextTick(() => this.place(trigger));
            },
            close() {
                this.open = false;
                if (this.currentRow) {
                    this.currentRow.classList.remove('!bg-sky-100/70', '!bg-sky-100/75');
                    this.currentRow = null;
                }
                document.querySelectorAll('[data-amr-row]').forEach(r => {
                    r.classList.remove('!bg-sky-100/70', '!bg-sky-100/75');
                });
            },
            scheduleClose() {
                this.closeTimer = window.setTimeout(() => this.close(), 120);
            },
            keepOpen() {
                window.clearTimeout(this.closeTimer);
                if (this.currentRow) {
                    this.currentRow.classList.add('!bg-sky-100/70');
                }
            }
        }"
        @keydown.escape.window="close()"
        @scroll.window="close()"
        @resize.window="close()">
        <button type="button"
            class="group inline-flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-2 text-left text-brand-800 ring-1 ring-inset ring-brand-200 transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
            @mouseenter="if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) show($el)"
            @mouseleave="scheduleClose()"
            @focus="show($el)"
            @blur="close()"
            @click="show($el)"
            :aria-controls="$id('lab-panel')"
            :aria-expanded="open.toString()">
            <span class="font-semibold">{{ $label }}</span>
            <span class="text-xs text-brand-700">{{ $labSegments->count() }} ค่า</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-brand-500 transition-transform duration-200"
                :class="{ 'rotate-180': open }" aria-hidden="true"></i>
        </button>

        <template x-teleport="body">
            <section x-show="open"
                x-ref="panel"
                :id="$id('lab-panel')"
                style="display: none"
                :style="`top: ${top}px; left: ${left}px;`"
                x-transition:enter="transition duration-180 ease-out"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition duration-100 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @mouseenter="keepOpen()"
                @mouseleave="scheduleClose()"
                @click.outside="close()"
                class="fixed z-[100] flex max-h-[calc(100vh-2rem)] w-[calc(100vw-2rem)] max-w-[27rem] flex-col overflow-hidden rounded-xl bg-white shadow-2xl"
                role="region"
                aria-label="ผล {{ $label }} ของ {{ trim($patientName) }} HN {{ $patientHn }} AN {{ $patientAn }} วันที่รับไว้ {{ $admitDate }} {{ $wardName }}">
                <header class="flex shrink-0 items-start justify-between gap-4 bg-brand-700 px-4 py-3 text-white">
                    <div>
                        <p class="font-semibold">ผล {{ $label }} (ล่าสุด)</p>
                        <p class="mt-0.5 text-xs text-brand-100">
                            {{ trim($patientName) }} · {{ $labSegments->count() }} ค่า
                        </p>
                        <p class="mt-1 text-xs leading-5 text-brand-100">
                            HN {{ $patientHn }} · AN {{ $patientAn }} · วันที่ {{ $admitDate }}<br>
                            {{ $wardName }}
                        </p>
                    </div>

                </header>

                <ol class="min-h-0 flex-1 divide-y divide-gray-100 overflow-y-auto p-2">
                    @foreach ($labSegments as $segment)
                        <li class="flex gap-3 px-2 py-2.5">
                            <span
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-[11px] font-semibold text-brand-800">
                                {{ $loop->iteration }}
                            </span>
                            <span class="break-words text-sm leading-5 text-gray-800">{{ $segment }}</span>
                        </li>
                    @endforeach
                </ol>

                <p class="shrink-0 border-t border-gray-100 bg-gray-50 px-4 py-2 text-xs text-gray-500">
                    เลื่อนดูรายการทั้งหมดได้ · กด Esc เพื่อปิด
                </p>
            </section>
        </template>
    </div>
@else
    <span class="text-gray-400">ไม่มีข้อมูล</span>
@endif
