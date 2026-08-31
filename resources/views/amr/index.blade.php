@extends('layout.app')

@section('title', 'รายการผู้ป่วย AMR')

@section('content')
    <div class="w-full space-y-6">
        <form method="GET" action="{{ route('amr.index') }}"
            x-data="{
                searchQuery: '{{ addslashes($filters['search']) }}',
                admitDate: '{{ addslashes($filters['admit_date']) }}',
                isFiltering: false
            }"
            @submit="isFiltering = true"
            class="bg-white p-4 shadow-sm ring-1 ring-gray-200 sm:p-5 rounded-xl">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">

                {{-- Search Input with Clear Button --}}
                <div class="xl:col-span-3">
                    <label for="search" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        <span>ค้นหาผู้ป่วย</span>
                    </label>
                    <div class="relative">
                        <input id="search" name="search" type="text"
                            x-model="searchQuery"
                            x-ref="searchInput"
                            placeholder="HN, AN หรือชื่อผู้ป่วย"
                            class="block w-full h-[42px] rounded-lg border border-gray-300 bg-white pl-3.5 pr-9 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">

                        {{-- Clear Search Input Button --}}
                        <button type="button"
                            x-show="searchQuery && searchQuery.length > 0"
                            @click="searchQuery = ''; $nextTick(() => $refs.searchInput.focus())"
                            class="absolute right-2 top-1/2 -translate-y-1/2 flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none"
                            title="ล้างข้อความค้นหา">
                            <i class="fa-solid fa-circle-xmark text-sm"></i>
                        </button>
                    </div>
                </div>

                {{-- Admit Date with Calendar Icon & Visual Indicator --}}
                <div class="xl:col-span-2">
                    <label for="admit_date" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <i class="fa-regular fa-calendar-days text-gray-400"></i>
                        <span>วันที่</span>
                    </label>
                    <div class="relative">
                        <i class="fa-regular fa-calendar-days text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 text-sm pointer-events-none z-10"></i>
                        <input id="admit_date" name="admit_date" type="text" value="{{ $filters['admit_date'] }}"
                            placeholder="เลือกวันที่"
                            class="block w-full h-[42px] rounded-lg border border-gray-300 bg-white pl-10 pr-9 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer">

                        {{-- Clear Date Button --}}
                        @if ($filters['admit_date'] !== '')
                            <button type="button" id="clear_admit_date"
                                class="absolute right-2 top-1/2 -translate-y-1/2 flex h-7 w-7 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none z-10"
                                title="ล้างวันที่">
                                <i class="fa-solid fa-circle-xmark text-sm"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Ward Select (Select2) --}}
                <div class="xl:col-span-3">
                    <label for="ward" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <i class="fa-solid fa-hospital text-gray-400"></i>
                        <span>หอผู้ป่วย</span>
                    </label>
                    <div class="relative">
                        <select id="ward" name="ward"
                            class="block w-full h-[42px] rounded-lg border border-gray-300 text-sm shadow-sm select2-ward">
                            <option value="">ทุกหอผู้ป่วย</option>
                            @foreach ($wards as $ward)
                                <option value="{{ trim($ward->ward_id) }}" @selected($filters['ward'] === trim($ward->ward_id))>
                                    {{ trim($ward->ward_id) }} — {{ trim($ward->ward_name ?? '') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Lab (M) Filter --}}
                <div class="xl:col-span-1">
                    <label for="m" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <i class="fa-solid fa-flask text-gray-400"></i>
                        <span>ผลแล็บ (M)</span>
                    </label>
                    <select id="m" name="m"
                        class="block w-full h-[42px] rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">ทั้งหมด</option>
                        <option value="Y" @selected($filters['m'] === 'Y')>มีผล</option>
                        <option value="N" @selected($filters['m'] === 'N')>ไม่มี</option>
                    </select>
                </div>

                {{-- New Result (RM) Filter --}}
                <div class="xl:col-span-1">
                    <label for="rm" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-600">
                        <i class="fa-solid fa-bell text-gray-400"></i>
                        <span>ออกใหม่ (RM)</span>
                    </label>
                    <select id="rm" name="rm"
                        class="block w-full h-[42px] rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">ทั้งหมด</option>
                        <option value="Y" @selected($filters['rm'] === 'Y')>ออกใหม่</option>
                        <option value="N" @selected($filters['rm'] === 'N')>ไม่มี</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex items-end gap-2 xl:col-span-2">
                    <button type="submit"
                        class="inline-flex h-[42px] flex-1 items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-4 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <i class="fa-solid fa-filter text-xs"></i>
                        <span>ค้นหา</span>
                    </button>
                    @if ($filters['search'] !== '' || $filters['admit_date'] !== '' || $filters['ward'] !== '' || $filters['m'] !== null || $filters['rm'] !== null)
                        <a href="{{ route('amr.index') }}"
                            @click="$dispatch('start-filtering')"
                            class="inline-flex h-[42px] items-center justify-center gap-1 rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300"
                            title="ล้างตัวกรองทั้งหมด">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            <span>ล้าง</span>
                        </a>
                    @endif
                </div>
            </div>

            @isset($loadError)
                <div class="mt-4 bg-red-50 px-4 py-4 text-sm text-red-800 ring-1 ring-inset ring-red-200 rounded-lg" role="alert">
                    <p class="font-medium">โหลดข้อมูลไม่สำเร็จ</p>
                    <p class="mt-1">{{ $loadError }}</p>
                </div>
            @endisset
        </form>

        <section class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-xl relative"
            :aria-busy="isFiltering ? 'true' : 'false'"
            aria-label="ตารางรายการผู้ป่วย AMR">
            <!-- Pure Skeleton Overlay Loading State (No Spinner) -->
            <div x-data="{ isFiltering: false }"
                @submit.window="isFiltering = true"
                @start-filtering.window="isFiltering = true"
                x-show="isFiltering"
                style="display: none;"
                class="absolute inset-0 z-20 bg-white/95 backdrop-blur-sm flex flex-col pointer-events-none transition-opacity duration-150 overflow-hidden">
                <table class="w-full min-w-[1440px] border-collapse text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-400 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-5 py-3">ผู้ป่วย</th>
                            <th scope="col" class="px-5 py-3">หอผู้ป่วย</th>
                            <th scope="col" class="px-5 py-3">วันที่นอน</th>
                            <th scope="col" class="px-5 py-3">สัดส่วนร่างกาย</th>
                            <th scope="col" class="px-5 py-3">สถานะผล</th>
                            <th scope="col" class="px-5 py-3">CR/EGFR</th>
                            <th scope="col" class="px-5 py-3">CBC</th>
                            <th scope="col" class="px-5 py-3">LFT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @for($i = 0; $i < 8; $i++)
                        <tr class="animate-pulse align-top">
                            <td class="px-5 py-4">
                                <div class="h-4 bg-gray-200 rounded w-44"></div>
                                <div class="mt-2 flex gap-2">
                                    <div class="h-3 bg-gray-100 rounded w-16"></div>
                                    <div class="h-3 bg-gray-100 rounded w-20"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="h-4 bg-gray-200 rounded w-36"></div>
                                <div class="mt-2 h-3 bg-gray-100 rounded w-16"></div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="h-4 bg-gray-200 rounded w-24"></div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1.5">
                                    <div class="h-3.5 bg-gray-200 rounded w-20"></div>
                                    <div class="h-3.5 bg-gray-200 rounded w-20"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1.5">
                                    <div class="h-5 bg-sky-100/80 rounded-lg w-24"></div>
                                    <div class="h-5 bg-amber-100/80 rounded-lg w-28"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1.5">
                                    <div class="h-3.5 bg-gray-200 rounded w-16"></div>
                                    <div class="h-3.5 bg-gray-200 rounded w-16"></div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="h-8 bg-gray-100 rounded-lg w-48"></div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="h-8 bg-gray-100 rounded-lg w-48"></div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1440px] border-collapse text-left text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-5 py-3">ผู้ป่วย</th>
                            <th scope="col" class="px-5 py-3">หอผู้ป่วย</th>
                            <th scope="col" class="px-5 py-3">วันที่นอน</th>
                            <th scope="col" class="px-5 py-3">สัดส่วนร่างกาย</th>
                            <th scope="col" class="px-5 py-3 cursor-help" title="M = มีผลแล็บ · RM = มีผลออกใหม่ · เชื้อดื้อยาที่ตรวจพบ">สถานะเชื้อ & ผล</th>
                            <th scope="col" class="px-5 py-3 cursor-help" title="Creatinine และ eGFR (ประเมินการทำงานของไต)">CR/EGFR</th>
                            <th scope="col" class="px-5 py-3 cursor-help" title="Complete Blood Count (ตรวจความสมบูรณ์ของเม็ดเลือด)">CBC</th>
                            <th scope="col" class="px-5 py-3 cursor-help" title="Liver Function Test (ตรวจการทำงานของตับ)">LFT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @php
                            $patientOrganisms = $patientOrganisms ?? collect();
                            $masterOrganisms = $masterOrganisms ?? collect();
                        @endphp
                        @forelse ($patients as $patient)
                            @php
                                $m = strtoupper(trim((string) $patient->M));
                                $rm = strtoupper(trim((string) $patient->RM));
                                $mStatusClass = $m === 'Y'
                                    ? 'bg-sky-100 text-sky-800 border border-sky-200'
                                    : 'bg-slate-100 text-slate-700 border border-slate-200';
                                $rmStatusClass = $rm === 'Y'
                                    ? 'bg-amber-100 text-amber-900 border border-amber-200'
                                    : 'bg-slate-100 text-slate-700 border border-slate-200';
                                $mLabel = match ($m) {
                                    'Y' => 'มีผลแล็บ',
                                    'N' => 'ยังไม่ตรวจ',
                                    default => 'ไม่ระบุสถานะ',
                                };
                                $rmLabel = match ($rm) {
                                    'Y' => 'มีผลออกใหม่',
                                    'N' => 'ไม่มีผลออกใหม่',
                                    default => 'ไม่ระบุสถานะ',
                                };

                                // Retrieve saved organisms for this patient with robust multi-key resolution
                                $cleanHn = trim((string) $patient->hn);
                                $cleanReg = trim((string) ($patient->regist_flag ?? ''));
                                $savedOrg = $patientOrganisms->get($cleanHn . '_' . $cleanReg) ?? $patientOrganisms->get($cleanHn);

                                // Parse eGFR for clinical risk triage
                                $rawEgfr = trim((string) $patient->egfr);
                                $numericEgfr = is_numeric($rawEgfr) ? (float) $rawEgfr : null;
                            @endphp
                            <tr data-amr-row data-hn="{{ trim($patient->hn) }}"
                                data-name="{{ trim($patient->name ?? '') ?: 'ไม่ระบุชื่อ' }}"
                                data-regist-flag="{{ trim($patient->regist_flag ?? '') }}"
                                data-ward-id="{{ trim($patient->ward_id ?? '') }}"
                                data-ward-name="{{ trim($patient->ward_name ?? '') }}"
                                class="align-top transition-colors duration-150 hover:bg-sky-50/50">
                                <td class="px-5 py-4">
                                    <p class="max-w-64 font-bold text-gray-900 leading-tight">
                                        {{ trim($patient->name ?? '') ?: 'ไม่ระบุชื่อ' }}
                                    </p>
                                    <div class="mt-1.5 flex flex-wrap gap-x-2.5 gap-y-1 text-xs text-gray-500">
                                        <span>HN <strong class="text-gray-700 font-semibold">{{ trim($patient->hn) . '-' . trim($patient->regist_flag) }}</strong></span>
                                        <span>AN <strong class="text-gray-700 font-semibold">{{ trim($patient->ladmit_n) }}</strong></span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        เพศ
                                        @if ($patient->sex == 'ช')
                                             ชาย
                                        @elseif ($patient->sex == 'ญ')
                                             หญิง
                                        @else
                                             ไม่ระบุเพศ
                                        @endif
                                        @if (filled(trim((string) $patient->age)))
                                            · อายุ {{ trim($patient->age) . ' ปี' }}
                                        @endif
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-900">
                                        {{ trim($patient->ward_name ?? '') ?: 'ไม่ระบุหอ' }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-500">รหัส {{ trim($patient->ward_id ?? '') ?: '-' }}</p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="whitespace-nowrap font-medium text-gray-900 text-xs">
                                        {{ filled(trim((string) $patient->admit_date)) ? \App\Helpers\DateHelper::formatThaiDate(trim($patient->admit_date), 'full') : '-' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-gray-700 text-xs">
                                    <dl class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1">
                                        <dt class="text-gray-500">น้ำหนัก</dt>
                                        <dd class="font-medium text-gray-800">{{ is_numeric($patient->Weight) ? number_format((float) $patient->Weight, 1).' กก.' : '-' }}</dd>
                                        <dt class="text-gray-500">ส่วนสูง</dt>
                                        <dd class="font-medium text-gray-800">{{ is_numeric($patient->Height) ? number_format((float) $patient->Height, 1).' ซม.' : '-' }}</dd>
                                    </dl>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex min-w-40 flex-col items-start gap-1.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-lg px-2 py-0.5 text-xs font-semibold {{ $mStatusClass }}"
                                                title="M: มีผลแล็บ">
                                                <i class="fa-solid fa-flask text-xs" aria-hidden="true"></i>
                                                {{ $mLabel }}
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-lg px-2 py-0.5 text-xs font-semibold {{ $rmStatusClass }}"
                                                title="RM: มีผลออกใหม่">
                                                <i class="fa-solid fa-bell text-xs" aria-hidden="true"></i>
                                                {{ $rmLabel }}
                                            </span>
                                        </div>

                                        {{-- Direct AMR organism chips from the normalized master list. --}}
                                        @php
                                            $selectedOrganisms = $savedOrg?->selectedOrganisms ?? collect();
                                        @endphp
                                        @if ($selectedOrganisms->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 mt-0.5">
                                                @foreach ($selectedOrganisms as $organism)
                                                    @php
                                                        $chipClass = match ($organism->severity) {
                                                            'critical' => 'bg-red-50 text-red-700 border-red-200',
                                                            'high' => 'bg-orange-50 text-orange-700 border-orange-200',
                                                            'medium' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                            default => 'bg-sky-50 text-sky-800 border-sky-200',
                                                        };
                                                        $chipTitle = collect([$organism->full_name, $organism->description])->filter()->join(' — ');
                                                    @endphp
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold border {{ $chipClass }}"
                                                        title="{{ $chipTitle }}">
                                                        <i class="fa-solid fa-shield-virus text-xs" aria-hidden="true"></i>
                                                        {{ $organism->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-gray-700 text-xs">
                                    <dl class="grid grid-cols-[auto_1fr] gap-x-2 gap-y-1.5">
                                        <dt class="text-gray-500">Cr</dt>
                                        <dd class="font-bold text-gray-900">{{ trim((string) $patient->cr) ?: '-' }}</dd>
                                        <dt class="text-gray-500">eGFR</dt>
                                        <dd>
                                            @if ($numericEgfr !== null)
                                                @if ($numericEgfr < 30)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-black bg-red-50 text-red-700 border border-red-200"
                                                        title="eGFR วิกฤต (< 30 ml/min/1.73m²) — ระวังขนาดยาฆ่าเชื้อ">
                                                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-xs"></i>
                                                        {{ $numericEgfr }}
                                                    </span>
                                                @elseif ($numericEgfr < 60)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200"
                                                        title="eGFR ปานกลาง (30-59 ml/min/1.73m²)">
                                                        {{ $numericEgfr }}
                                                    </span>
                                                @else
                                                    <span class="font-bold text-emerald-700">{{ $numericEgfr }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">{{ trim((string) $patient->egfr) ?: '-' }}</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </td>

                                <td class="max-w-72 px-5 py-4">
                                    @include('amr.partials.lab-popover', [
                                        'label' => 'CBC',
                                        'value' => $patient->CBC,
                                        'patientName' => trim($patient->name ?? '') ?: 'ไม่ระบุชื่อ',
                                        'patientHn' => trim($patient->hn ?? '') ?: '-',
                                        'patientAn' => trim($patient->ladmit_n ?? '') ?: '-',
                                        'admitDate' => filled(trim((string) $patient->admit_date))
                                            ? \App\Helpers\DateHelper::formatThaiDate(trim($patient->admit_date), 'full')
                                            : '-',
                                        'wardName' => trim($patient->ward_name ?? '') ?: 'ไม่ระบุหอ',
                                    ])
                                </td>

                                <td class="max-w-72 px-5 py-4">
                                    @include('amr.partials.lab-popover', [
                                        'label' => 'LFT',
                                        'value' => $patient->LFT,
                                        'patientName' => trim($patient->name ?? '') ?: 'ไม่ระบุชื่อ',
                                        'patientHn' => trim($patient->hn ?? '') ?: '-',
                                        'patientAn' => trim($patient->ladmit_n ?? '') ?: '-',
                                        'admitDate' => filled(trim((string) $patient->admit_date))
                                            ? \App\Helpers\DateHelper::formatThaiDate(trim($patient->admit_date), 'full')
                                            : '-',
                                        'wardName' => trim($patient->ward_name ?? '') ?: 'ไม่ระบุหอ',
                                    ])
                                </td>
                            </tr>
                        @empty
                            @unless (isset($loadError))
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <p class="font-medium text-gray-800">ไม่พบรายการผู้ป่วย AMR</p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            ลองเปลี่ยนคำค้นหาหรือล้างตัวกรองแล้วค้นหาอีกครั้ง
                                        </p>
                                    </td>
                                </tr>
                            @endunless
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($patients->hasPages())
                <div class="border-t border-gray-200 px-4 py-4 sm:px-5">
                    {{ $patients->onEachSide(1)->links() }}
                </div>
            @endif
        </section>


        @include('amr.partials.lab-history')
    </div>

    @push('indexScript')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Select2 with search for Ward selection and constrain dropdown to parent container
                $('.select2-ward').select2({
                    placeholder: "ทุกหอผู้ป่วย",
                    allowClear: true,
                    width: '100%',
                    theme: 'default',
                    dropdownParent: $('#ward').parent()
                });

                // Auto focus on select2 search input when opened without triggering window scroll
                $(document).on('select2:open', () => {
                    setTimeout(() => {
                        const searchField = document.querySelector('.select2-search__field');
                        if (searchField) {
                            searchField.focus();
                        }
                    }, 10);
                });

                // Initialize Flatpickr for date selection with Thai locale and identical Tailwind classes
                const fp = flatpickr("#admit_date", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y", // Example: 15 ต.ค. 2026
                    altInputClass: "block w-full h-[42px] rounded-lg border border-gray-300 bg-white pl-10 pr-9 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer",
                    locale: "th",
                    allowInput: false,
                    placeholder: "เลือกวันที่"
                });

                // Handle clear date button
                const clearDateBtn = document.getElementById('clear_admit_date');
                if (clearDateBtn) {
                    clearDateBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        fp.clear();
                        clearDateBtn.style.display = 'none';
                    });
                }

                // Trigger skeleton on pagination links click
                document.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function() {
                        window.dispatchEvent(new CustomEvent('start-filtering'));
                    });
                });

                // Auto Refresh Timer Handler
                let refreshTimer = null;
                function setupAutoRefresh() {
                    if (refreshTimer) clearInterval(refreshTimer);
                    const intervalSec = parseInt(localStorage.getItem('brh_auto_refresh') || '0');
                    if (intervalSec > 0) {
                        refreshTimer = setInterval(() => {
                            // Only auto refresh if no modal or swal is open
                            if (!document.querySelector('.swal2-container') && !document.querySelector('[x-show="settingsModal"]:not([style*="display: none"])')) {
                                window.location.reload();
                            }
                        }, intervalSec * 1000);
                    }
                }
                setupAutoRefresh();
                window.addEventListener('brh-auto-refresh-changed', setupAutoRefresh);
            });
        </script>
        <style>
            /* Ensure Select2 has 100% visual consistency with Tailwind input fields and zero horizontal overflow */
            .select2-container {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .select2-container .select2-selection--single {
                height: 42px !important;
                border: 1px solid var(--border-color-strong) !important;
                border-radius: 0.5rem !important;
                background-color: var(--input-bg) !important;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
                display: flex !important;
                align-items: center !important;
                padding-left: 0.25rem !important;
                font-family: inherit !important;
                font-size: 0.875rem !important;
                box-sizing: border-box !important;
                transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            }
            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: var(--focus-color) !important;
                outline: none !important;
                box-shadow: 0 0 0 2px var(--brand-soft) !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: var(--text-primary) !important;
                line-height: 40px !important;
                padding-left: 0.5rem !important;
                padding-right: 1.5rem !important;
                font-size: 0.875rem !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__placeholder {
                color: var(--text-muted) !important;
                font-size: 0.875rem !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 40px !important;
                right: 8px !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__clear {
                font-size: 1.1rem !important;
                color: var(--text-muted) !important;
                margin-right: 18px !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__clear:hover {
                color: var(--text-secondary) !important;
            }
            .select2-dropdown {
                border: 1px solid var(--border-color-strong) !important;
                border-radius: 0.5rem !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
                font-family: inherit !important;
                font-size: 0.875rem !important;
                overflow: hidden !important;
                z-index: 50 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .select2-container--default .select2-search--dropdown {
                padding: 6px !important;
                box-sizing: border-box !important;
            }
            .select2-container--default .select2-search--dropdown .select2-search__field {
                border: 1px solid var(--border-color-strong) !important;
                border-radius: 0.375rem !important;
                padding: 6px 10px !important;
                font-size: 0.875rem !important;
                outline: none !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .select2-container--default .select2-search--dropdown .select2-search__field:focus {
                border-color: var(--focus-color) !important;
                box-shadow: 0 0 0 2px var(--brand-soft) !important;
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: var(--brand-solid) !important;
                color: var(--brand-on-solid) !important;
            }
            .select2-container--default .select2-results__option[aria-selected=true] {
                background-color: var(--brand-soft) !important;
                color: var(--brand-text) !important;
                font-weight: 500 !important;
            }
        </style>
    @endpush
@endsection
