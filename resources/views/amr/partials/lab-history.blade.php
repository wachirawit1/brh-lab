<div id="amr-context-menu"
    class="fixed z-[70] hidden w-48 overflow-hidden rounded-xl bg-white py-1 shadow-xl ring-1 ring-gray-200"
    role="menu" aria-label="เมนูผู้ป่วย">
    <button id="amr-view-lab-history" type="button" role="menuitem"
        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 focus:bg-brand-50 focus:text-brand-700 focus:outline-none">
        <i class="fa-solid fa-microscope w-5 text-center text-brand-600" aria-hidden="true"></i>
        ดูผลแล็บ
    </button>
    <button id="amr-add-organism" type="button" role="menuitem"
        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-gray-700 transition hover:bg-brand-50 hover:text-brand-700 focus:bg-brand-50 focus:text-brand-700 focus:outline-none">
        <i class="fa-solid fa-bacterium w-5 text-center text-teal-600" aria-hidden="true"></i>
        เติมเชื้อ
    </button>
</div>

<div id="amr-lab-modal" data-endpoint="{{ url('/lab-results') }}"
    class="fixed inset-0 z-[80] hidden overflow-y-auto" role="dialog" aria-modal="true"
    aria-labelledby="amr-lab-modal-title">
    <div class="fixed inset-0 bg-gray-900/60" data-amr-lab-close aria-hidden="true"></div>

    <div class="relative flex min-h-full items-end justify-center p-4 sm:items-center sm:p-6">
        <section class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            tabindex="-1">
            <header class="flex shrink-0 items-start justify-between gap-6 bg-brand-700 px-5 py-4 text-white sm:px-6">
                <div>
                    <h2 id="amr-lab-modal-title" class="flex items-center gap-2 text-lg font-semibold">
                        <i class="fa-solid fa-flask" aria-hidden="true"></i>
                        ประวัติผลการตรวจ
                    </h2>
                    <p class="mt-1 text-sm text-brand-100">
                        <span id="amr-lab-patient-name"></span>
                        <span aria-hidden="true"> · </span>
                        HN <span id="amr-lab-patient-hn"></span>
                    </p>
                </div>
                <button type="button" data-amr-lab-close
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-brand-100 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-brand-700">
                    <span class="sr-only">ปิดหน้าต่างผลแล็บ</span>
                    <i class="fa-solid fa-xmark text-xl" aria-hidden="true"></i>
                </button>
            </header>

            <div id="amr-lab-results" class="min-h-48 flex-1 overflow-y-auto px-5 py-5 sm:px-6" aria-live="polite"></div>

            <footer class="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" data-amr-lab-close
                    class="inline-flex min-h-10 items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    ปิด
                </button>
                {{-- <button id="amr-print-lab-history" type="button"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <i class="fa-solid fa-print" aria-hidden="true"></i>
                    พิมพ์
                </button> --}}
            </footer>
        </section>
    </div>
</div>

@push('indexScript')
    <script>
        (() => {
            const rows = document.querySelectorAll('[data-amr-row]');
            const contextMenu = document.getElementById('amr-context-menu');
            const viewButton = document.getElementById('amr-view-lab-history');
            const modal = document.getElementById('amr-lab-modal');
            const results = document.getElementById('amr-lab-results');
            const patientName = document.getElementById('amr-lab-patient-name');
            const patientHn = document.getElementById('amr-lab-patient-hn');
            let selectedRow = null;
            let returnFocus = null;
            const organismScrollLockKey = 'amr-organism';

            if (!rows.length || !contextMenu || !viewButton || !modal || !results) return;

            const escapeHtml = (value) => {
                const element = document.createElement('div');
                element.textContent = value == null ? '' : String(value);
                return element.innerHTML;
            };

            const setOrganismScrollLock = (locked) => {
                window.BRHModalScroll?.set(organismScrollLockKey, locked);
            };
            const clearSelectedRow = () => {
                rows.forEach((row) => row.classList.remove('bg-brand-50'));
            };

            const closeContextMenu = ({ restoreFocus = false } = {}) => {
                contextMenu.classList.add('hidden');
                clearSelectedRow();
                if (restoreFocus && selectedRow) selectedRow.focus();
            };

            const openContextMenu = (row, x, y, focusMenu = false) => {
                selectedRow = row;
                clearSelectedRow();
                row.classList.add('bg-brand-50');
                contextMenu.classList.remove('hidden');

                const margin = 12;
                const width = contextMenu.offsetWidth;
                const height = contextMenu.offsetHeight;
                contextMenu.style.left = `${Math.max(margin, Math.min(x, window.innerWidth - width - margin))}px`;
                contextMenu.style.top = `${Math.max(margin, Math.min(y, window.innerHeight - height - margin))}px`;

                if (focusMenu) viewButton.focus();
            };

            rows.forEach((row) => {
                row.addEventListener('contextmenu', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    openContextMenu(row, event.clientX, event.clientY);
                });

                row.addEventListener('keydown', (event) => {
                    if (event.key === 'ContextMenu' || (event.shiftKey && event.key === 'F10')) {
                        event.preventDefault();
                        const rect = row.getBoundingClientRect();
                        openContextMenu(row, rect.left + 24, Math.min(rect.bottom, window.innerHeight - 24), true);
                    }
                });
            });

            const showLoading = () => {
                results.innerHTML = `
                    <div class="flex min-h-48 flex-col items-center justify-center text-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-brand-500" aria-hidden="true"></i>
                        <p class="mt-3 text-sm text-gray-600">กำลังโหลดประวัติผลแล็บ...</p>
                    </div>`;
            };

            const showError = () => {
                results.innerHTML = `
                    <div class="bg-red-50 px-5 py-6 text-center text-red-800 ring-1 ring-inset ring-red-200" role="alert">
                        <i class="fa-solid fa-triangle-exclamation text-2xl text-red-500" aria-hidden="true"></i>
                        <p class="mt-2 font-medium">โหลดประวัติผลแล็บไม่สำเร็จ</p>
                        <p class="mt-1 text-sm">กรุณาปิดหน้าต่างแล้วลองใหม่อีกครั้ง</p>
                    </div>`;
            };

            const renderLabResults = (labResults) => {
                if (!Array.isArray(labResults) || labResults.length === 0) {
                    results.innerHTML = `
                        <div class="bg-sky-50 px-5 py-8 text-center text-sky-900 ring-1 ring-inset ring-sky-200">
                            <i class="fa-solid fa-flask text-3xl text-sky-500" aria-hidden="true"></i>
                            <p class="mt-3 font-medium">ไม่พบประวัติผลแล็บ</p>
                            <p class="mt-1 text-sm text-sky-800">ยังไม่มีผลการตรวจสำหรับ HN นี้</p>
                        </div>`;
                    return;
                }

                const cards = labResults.map((lab, index) => {
                    const panelId = `amr-lab-history-panel-${index}`;
                    const expanded = index === 0;

                    return `
                        <article class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                            <button type="button" data-lab-toggle="${panelId}" aria-controls="${panelId}"
                                aria-expanded="${expanded}"
                                class="flex w-full items-center justify-between gap-4 bg-gray-50 px-4 py-3 text-left transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-500">
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-xs font-semibold text-gray-600 ring-1 ring-gray-200">${index + 1}</span>
                                    <span class="truncate text-sm font-medium text-gray-800">${escapeHtml(lab.res_date)}</span>
                                </span>
                                <i class="fa-solid ${expanded ? 'fa-chevron-up' : 'fa-chevron-down'} text-xs text-gray-500" aria-hidden="true"></i>
                            </button>
                            <div id="${panelId}" class="border-t border-gray-100 bg-white px-4 py-4 ${expanded ? '' : 'hidden'}">
                                <pre class="whitespace-pre-wrap break-words rounded-lg bg-gray-50 p-4 text-sm leading-6 text-gray-800">${escapeHtml(lab.resText)}</pre>
                            </div>
                        </article>`;
                }).join('');

                results.innerHTML = `
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <p class="font-medium text-gray-800">รายการตรวจทั้งหมด</p>
                        <span class="rounded-lg bg-brand-100 px-2.5 py-1 text-xs font-semibold text-brand-800">${labResults.length} รายการ</span>
                    </div>
                    <div class="space-y-3">${cards}</div>`;
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                window.BRHModalScroll?.set('amr-lab-history', false);
                clearSelectedRow();
                if (returnFocus) returnFocus.focus();
            };

            const openLabHistory = async () => {
                if (!selectedRow) return;

                const hn = selectedRow.dataset.hn || '';
                const name = selectedRow.dataset.name || 'ไม่ระบุชื่อ';
                returnFocus = selectedRow;
                patientHn.textContent = hn;
                patientName.textContent = name;
                closeContextMenu();
                showLoading();
                modal.classList.remove('hidden');
                window.BRHModalScroll?.set('amr-lab-history', true);
                modal.querySelector('button[data-amr-lab-close]').focus();

                try {
                    const response = await fetch(`${modal.dataset.endpoint}/${encodeURIComponent(hn)}`, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                    });

                    if (response.redirected && new URL(response.url).pathname.endsWith('/login')) {
                        if (typeof window.showSessionExpiredAlert === 'function') window.showSessionExpiredAlert();
                        return;
                    }

                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    const payload = await response.json();
                    if (payload.status === 'error') throw new Error(payload.error || 'Unable to load lab history');
                    renderLabResults(payload.labResults);
                } catch (error) {
                    console.error('AMR lab history:', error);
                    showError();
                }
            };

            window.openLabHistoryForRow = (row) => {
                selectedRow = row;
                openLabHistory();
            };

            const handleOpenOrganism = async (row) => {
                if (!row) return;
                selectedRow = row;
                const hn = row.dataset.hn || '';
                const name = row.dataset.name || 'ไม่ระบุชื่อ';
                const registFlag = row.dataset.registFlag || '';
                const wardId = row.dataset.wardId || '';
                const wardName = row.dataset.wardName || '';
                const wardDisplay = (wardId || wardName) ? `${wardId ? wardId + ' ' : ''}${wardName}`.trim() : '-';
                closeContextMenu();
                setOrganismScrollLock(true);

                // Show brief loading
                Swal.fire({
                    title: 'กำลังโหลดข้อมูล...',
                    scrollbarPadding: false,
                    heightAuto: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                    // Fetch existing data if any
                    let currentData = {
                        organisms: [],
                        master_organisms: [],
                        updated_at: null,
                        created_by: null
                    };

                    try {
                        const res = await fetch(`/amr/organisms/${encodeURIComponent(hn)}?reg_no=${encodeURIComponent(registFlag)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            if (json.status === 'success' && json.data) {
                                currentData = Object.assign(currentData, json.data);
                            }
                        }
                    } catch (e) {
                        console.warn('Could not fetch existing organism data:', e);
                    }

                    Swal.fire({
                        title: 'เติมข้อมูลเชื้อดื้อยา (AMR)',
                        width: '760px',
                        scrollbarPadding: false,
                        heightAuto: false,
                        html: `
                            <div class="text-left text-sm space-y-4 pt-1">
                                <div class="bg-sky-50/80 border border-sky-100 p-3 rounded-xl flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-sky-600 font-semibold uppercase tracking-wider">ผู้ป่วย</p>
                                        <p class="font-bold text-gray-900 text-sm">${escapeHtml(name)}</p>
                                        <p class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <span>HN: <strong class="text-gray-700 font-semibold">${escapeHtml(hn)}</strong></span>
                                            ${registFlag ? `<span>· RegNo: <strong class="text-gray-700 font-semibold">${escapeHtml(registFlag)}</strong></span>` : ''}
                                            <span>· หอผู้ป่วย: <strong class="text-gray-700 font-semibold">${escapeHtml(wardDisplay)}</strong></span>
                                        </p>
                                    </div>
                                    <div class="h-9 w-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-base">
                                        <i class="fa-solid fa-bacterium"></i>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2.5 flex items-center justify-between">
                                        <span>เลือกเชื้อที่ตรวจพบ (${currentData.master_organisms.length} กลุ่มเฝ้าระวัง)</span>
                                        <span class="text-xs font-normal text-gray-400">ติ๊กได้มากกว่า 1 ตัว</span>
                                    </p>

                                    <div data-amr-organism-grid class="grid max-h-[50vh] grid-cols-1 gap-2 overflow-y-auto overscroll-contain pr-1 text-left sm:grid-cols-2">
                                        ${window.buildAmrOrganismOptions(currentData.master_organisms, currentData.organisms, 'org')}
                                    </div>
                                </div>

                                <div class="pt-2.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                    ${currentData.updated_at ? `
                                        <div class="flex items-center gap-1.5 text-teal-800 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200/60 font-medium">
                                            <i class="fa-solid fa-user-pen text-teal-600"></i>
                                            <span>เพิ่มโดย: <strong class="text-teal-900 font-bold">${escapeHtml(currentData.created_by || 'ไม่ระบุ')}</strong></span>
                                        </div>
                                        <div class="text-gray-500 flex items-center gap-1">
                                            <i class="fa-regular fa-clock text-gray-400"></i>
                                            <span>เมื่อ: ${escapeHtml(currentData.updated_at)}</span>
                                        </div>
                                    ` : `
                                        <div class="text-gray-400 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            <span>ยังไม่มีประวัติการบันทึกเชื้อ</span>
                                        </div>
                                        <div class="text-gray-600 flex items-center gap-1 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-200">
                                            <i class="fa-solid fa-user text-gray-400"></i>
                                            <span>ผู้บันทึก: <strong class="text-gray-800 font-semibold">{{ session('user.fullname') ?: (session('user.username') ?: 'เจ้าหน้าที่') }}</strong></span>
                                        </div>
                                    `}
                                </div>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i> บันทึกข้อมูล',
                        cancelButtonText: 'ยกเลิก',
                        confirmButtonColor: 'var(--brand-solid)',
                        cancelButtonColor: 'var(--neutral-solid)',
                        focusConfirm: false,
                        showLoaderOnConfirm: true,
                        preConfirm: async () => {
                            const payload = {
                                hn: hn,
                                regist_flag: registFlag,
                                ward_id: wardId,
                                organisms: window.getSelectedAmrOrganisms(Swal.getHtmlContainer())
                            };

                            try {
                                const response = await fetch('/amr/organisms', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify(payload)
                                });

                                if (!response.ok) {
                                    const errJson = await response.json();
                                    throw new Error(errJson.message || 'บันทึกไม่สำเร็จ');
                                }

                                return await response.json();
                            } catch (err) {
                                Swal.showValidationMessage(`เกิดข้อผิดพลาด: ${err.message}`);
                                return false;
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกสำเร็จ',
                                scrollbarPadding: false,
                                heightAuto: false,
                                text: `บันทึกข้อมูลเชื้อสำหรับ HN: ${hn} เรียบร้อยแล้ว`,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                setOrganismScrollLock(false);
                                window.location.reload();
                            });
                        } else {
                            setOrganismScrollLock(false);
                        }
                    });
            };

            window.openPatientOrganismModalByRow = (row) => handleOpenOrganism(row);

            const addOrganismBtn = document.getElementById('amr-add-organism');
            if (addOrganismBtn) {
                addOrganismBtn.addEventListener('click', () => handleOpenOrganism(selectedRow));
            }

            viewButton.addEventListener('click', openLabHistory);
            // document.getElementById('amr-print-lab-history').addEventListener('click', () => window.print());

            document.addEventListener('click', (event) => {
                if (!contextMenu.contains(event.target)) closeContextMenu();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Tab' && !modal.classList.contains('hidden')) {
                    const focusable = [...modal.querySelectorAll('button:not([disabled]), [href], [tabindex]:not([tabindex="-1"])')]
                        .filter((element) => !element.closest('.hidden'));
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];

                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                    return;
                }

                if (event.key !== 'Escape') return;
                if (!modal.classList.contains('hidden')) closeModal();
                else if (!contextMenu.classList.contains('hidden')) closeContextMenu({ restoreFocus: true });
            });

            window.addEventListener('scroll', () => closeContextMenu(), { passive: true });
            window.addEventListener('resize', () => closeContextMenu());
            modal.querySelectorAll('[data-amr-lab-close]').forEach((element) => element.addEventListener('click', closeModal));

            results.addEventListener('click', (event) => {
                const button = event.target.closest('[data-lab-toggle]');
                if (!button) return;

                const panel = document.getElementById(button.dataset.labToggle);
                const expanded = button.getAttribute('aria-expanded') === 'true';
                button.setAttribute('aria-expanded', String(!expanded));
                panel.classList.toggle('hidden', expanded);
                const icon = button.querySelector('.fa-chevron-down, .fa-chevron-up');
                icon.classList.toggle('fa-chevron-down', expanded);
                icon.classList.toggle('fa-chevron-up', !expanded);
            });
        })();
    </script>
@endpush
