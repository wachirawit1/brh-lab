<script>
    function initNavbarState() {
        if (!window.Alpine) return;
        Alpine.data('navbarState', () => ({
            open: false,
            qrModal: false,
            settingsModal: false,
            currentTab: 'themes',
            selectedTheme: window.BRHTheme?.readTheme() || 'light',
            selectedFontSize: window.BRHTheme?.readFontSize() || 'normal',
            autoRefreshInterval: parseInt(localStorage.getItem('brh_auto_refresh') || '0'),
            qrSvg: '',
            telegramUrl: '',
            isSubscribed: false,
            isLoading: false,
            masterOrganisms: [],
            auditLogs: [],
            loadingOrganisms: false,
            loadingLogs: false,
            showAddOrganismForm: false,
            organismFormError: '',
            savingOrganism: false,
            savingOrganismOrder: false,
            draggingOrganismId: null,
            dragOverOrganismId: null,
            showInactiveOrganisms: false,
            organismListError: '',
            organismOrderMessage: '',
            newOrganism: { code: '', name: '', full_name: '', severity: 'critical', color: '#dc2626' },

            init() {
                window.openSettingsModal = () => this.openSettings();
                window.openTelegramQr = () => this.loadQr();
                this.$watch('qrModal', (open) => window.BRHModalScroll?.set('navbar-telegram-qr', open));
                this.$watch('settingsModal', (open) => window.BRHModalScroll?.set('navbar-settings', open));
            },

            loadQr() {
                this.qrModal = true;
                if (!this.qrSvg) {
                    this.isLoading = true;
                    fetch('{{ route('telegram.qr') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.qrSvg = data.qr_svg;
                            this.telegramUrl = data.telegram_url;
                            this.isSubscribed = data.is_subscribed;
                        }
                    })
                    .catch(err => console.error('Error loading QR:', err))
                    .finally(() => { this.isLoading = false; });
                }
            },

            openSettings() {
                this.settingsModal = true;
                this.selectedTheme = window.BRHTheme?.readTheme() || 'light';
                this.selectedFontSize = window.BRHTheme?.readFontSize() || 'normal';
                this.autoRefreshInterval = parseInt(localStorage.getItem('brh_auto_refresh') || '0');
                if (this.currentTab === 'organisms') {
                    this.loadMasterOrganisms();
                } else if (this.currentTab === 'logs') {
                    this.loadAuditLogs();
                }
            },

            setTheme(theme) {
                this.selectedTheme = window.BRHTheme?.applyTheme(theme) || 'light';
            },

            setFontSize(size) {
                this.selectedFontSize = window.BRHTheme?.applyFontSize(size) || 'normal';
            },

            setAutoRefresh(sec) {
                this.autoRefreshInterval = parseInt(sec);
                localStorage.setItem('brh_auto_refresh', sec);
                window.dispatchEvent(new CustomEvent('brh-auto-refresh-changed', { detail: { interval: parseInt(sec) } }));
            },

            async loadMasterOrganisms() {
                this.loadingOrganisms = true;
                this.organismListError = '';
                try {
                    const res = await fetch('{{ route('settings.organisms.index') }}', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    if (!res.ok || json.status !== 'success' || !Array.isArray(json.data)) {
                        throw new Error(json.message || 'โหลดรายการเชื้อไม่สำเร็จ');
                    }
                    this.masterOrganisms = json.data;
                } catch (error) {
                    this.organismListError = error.message || 'โหลดรายการเชื้อไม่สำเร็จ กรุณาลองอีกครั้ง';
                } finally {
                    this.loadingOrganisms = false;
                }
            },

            activeMasterOrganisms() {
                return this.masterOrganisms.filter(organism => organism.is_active);
            },

            inactiveMasterOrganisms() {
                return this.masterOrganisms.filter(organism => !organism.is_active);
            },

            replaceActiveOrganisms(activeOrganisms) {
                this.masterOrganisms = [...activeOrganisms, ...this.inactiveMasterOrganisms()];
            },

            startOrganismDrag(id, event) {
                if (this.savingOrganismOrder) return;
                this.draggingOrganismId = id;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', String(id));
            },

            finishOrganismDrag() {
                this.draggingOrganismId = null;
                this.dragOverOrganismId = null;
            },

            dropOrganism(targetId) {
                const sourceId = this.draggingOrganismId;
                const activeOrganisms = this.activeMasterOrganisms();
                const sourceIndex = activeOrganisms.findIndex(organism => organism.id === sourceId);
                const targetIndex = activeOrganisms.findIndex(organism => organism.id === targetId);

                this.finishOrganismDrag();
                if (sourceIndex < 0 || targetIndex < 0 || sourceIndex === targetIndex) return;

                const previousIds = activeOrganisms.map(organism => organism.id);
                const [movedOrganism] = activeOrganisms.splice(sourceIndex, 1);
                activeOrganisms.splice(targetIndex, 0, movedOrganism);
                this.replaceActiveOrganisms(activeOrganisms);
                this.persistOrganismOrder(previousIds);
            },

            moveOrganism(id, direction) {
                if (this.savingOrganismOrder) return;

                const activeOrganisms = this.activeMasterOrganisms();
                const currentIndex = activeOrganisms.findIndex(organism => organism.id === id);
                const destinationIndex = currentIndex + direction;
                if (currentIndex < 0 || destinationIndex < 0 || destinationIndex >= activeOrganisms.length) return;

                const previousIds = activeOrganisms.map(organism => organism.id);
                [activeOrganisms[currentIndex], activeOrganisms[destinationIndex]] = [activeOrganisms[destinationIndex], activeOrganisms[currentIndex]];
                this.replaceActiveOrganisms(activeOrganisms);
                this.persistOrganismOrder(previousIds);
            },

            async persistOrganismOrder(previousIds) {
                if (this.savingOrganismOrder) return;

                this.savingOrganismOrder = true;
                this.organismListError = '';
                this.organismOrderMessage = 'กำลังบันทึกลำดับ...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('{{ route('settings.organisms.reorder') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ organism_ids: this.activeMasterOrganisms().map(organism => organism.id) })
                    });
                    const json = await res.json();
                    if (!res.ok || json.status !== 'success') {
                        throw new Error(json.message || 'บันทึกลำดับไม่สำเร็จ');
                    }
                    this.organismOrderMessage = 'บันทึกลำดับแล้ว';
                } catch (error) {
                    const activeById = new Map(this.activeMasterOrganisms().map(organism => [organism.id, organism]));
                    const restored = previousIds.map(id => activeById.get(id)).filter(Boolean);
                    this.replaceActiveOrganisms(restored);
                    this.organismOrderMessage = '';
                    this.organismListError = error.message || 'บันทึกลำดับไม่สำเร็จ กรุณาลองอีกครั้ง';
                } finally {
                    this.savingOrganismOrder = false;
                }
            },

            async toggleOrganism(id) {
                this.organismListError = '';
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(`/settings/master-organisms/${id}/toggle`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok || json.status !== 'success') {
                        throw new Error(json.message || 'เปลี่ยนสถานะไม่สำเร็จ');
                    }
                    await this.loadMasterOrganisms();
                } catch (error) {
                    this.organismListError = error.message || 'เปลี่ยนสถานะไม่สำเร็จ กรุณาลองอีกครั้ง';
                }
            },
            async saveNewOrganism() {
                if (this.savingOrganism) return;

                this.newOrganism.code = this.newOrganism.code.trim();
                this.newOrganism.name = this.newOrganism.name.trim();

                if (!this.newOrganism.code || !this.newOrganism.name) {
                    this.organismFormError = 'กรุณากรอกรหัสย่อและชื่อที่แสดงให้ครบถ้วน';
                    this.$nextTick(() => {
                        const selector = this.newOrganism.code ? '[data-organism-name]' : '[data-organism-code]';
                        document.querySelector(selector)?.focus();
                    });
                    return;
                }

                this.organismFormError = '';
                this.savingOrganism = true;

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch('{{ route('settings.organisms.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newOrganism)
                    });
                    const json = await res.json();

                    if (res.ok && json.status === 'success') {
                        this.newOrganism = { code: '', name: '', full_name: '', severity: 'critical', color: '#dc2626' };
                        this.showAddOrganismForm = false;
                        this.loadMasterOrganisms();
                        Swal.fire({ icon: 'success', title: 'เพิ่มเชื้อใหม่เรียบร้อยแล้ว', timer: 1500, showConfirmButton: false });
                    } else {
                        this.organismFormError = Object.values(json.errors || {}).flat()[0]
                            || json.message
                            || 'กรุณาตรวจสอบข้อมูลแล้วลองอีกครั้ง';
                    }
                } catch (e) {
                    this.organismFormError = 'เชื่อมต่อระบบไม่ได้ กรุณาลองบันทึกอีกครั้ง';
                } finally {
                    this.savingOrganism = false;
                }
            },

            async loadAuditLogs() {
                this.loadingLogs = true;
                try {
                    const res = await fetch('{{ route('settings.audit.logs') }}');
                    const json = await res.json();
                    if (json.status === 'success') {
                        this.auditLogs = json.data;
                    }
                } catch (e) {
                    console.error('Failed to load audit logs:', e);
                } finally {
                    this.loadingLogs = false;
                }
            }
        }));
    }
    document.addEventListener('alpine:init', initNavbarState);
    if (window.Alpine) initNavbarState();
</script>

<nav x-data="navbarState"
    @open-settings-modal.window="openSettings()"
    @open-telegram-qr.window="loadQr()"
    class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="w-full px-4 md:px-8 mx-auto">
        <div class="flex justify-between h-16">
            <!-- Logo & Brand -->
            <div class="flex items-center gap-6">
                <a href="{{ route('amr.index') }}" class="flex-shrink-0 flex items-center gap-3 group">
                    <img class="h-10 w-auto transition-transform group-hover:scale-105"
                        src="{{ asset('assets/img/logo-brh.png') }}" alt="Logo">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-800 text-lg leading-tight">โรงพยาบาลบุรีรัมย์</span>
                        <span class="text-xs text-brand-600 font-medium">Lab Result Notification</span>
                    </div>
                </a>

                <!-- Navigation Tabs -->
                <div class="hidden md:flex md:items-center md:space-x-2 h-full">
                    <a href="{{ route('amr.index') }}"
                        class="{{ Route::is('amr.*') || Route::is('index') ? 'text-brand-700 bg-brand-50 font-bold ring-1 ring-brand-200/80' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-medium' }} inline-flex items-center gap-2 px-3.5 py-2 text-sm rounded-xl transition duration-150 ease-in-out">
                        <i class="fa-solid fa-clipboard-list text-sm"></i>
                        <span>รายการผู้ป่วย AMR</span>
                    </a>
                </div>
            </div>

            <!-- Desktop Menu Right Side -->
            <div class="hidden md:flex md:items-center md:space-x-3">
                @if (session()->has('user'))
                    <!-- User Dropdown (Independent Alpine Scope) -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                            class="flex items-center gap-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none transition duration-150 ease-in-out px-3 py-1.5 rounded-xl hover:bg-gray-100/80 border border-gray-200 shadow-2xs cursor-pointer">
                            <!-- Avatar Placeholder -->
                            <div class="h-7 w-7 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold">
                                <i class="fa-solid fa-user text-xs"></i>
                            </div>
                            <span class="max-w-[150px] truncate font-semibold">{{ session('user.fullname') ?: session('user.username') }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"
                                :class="{ 'rotate-180': open }"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-64 rounded-2xl shadow-xl bg-white ring-1 ring-gray-200 py-1.5 focus:outline-none z-50">

                            <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50/60 rounded-t-2xl">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">เข้าสู่ระบบโดย</p>
                                <p class="text-sm font-bold text-gray-900 truncate mt-0.5">{{ session('user.fullname') }}</p>
                                <p class="text-xs text-gray-500">Username: {{ session('user.username') }}</p>
                            </div>

                            <div class="py-1">
                                <button type="button" @click="open = false; $dispatch('open-settings-modal')"
                                    class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition flex items-center gap-2.5 font-medium cursor-pointer">
                                    <i class="fa-solid fa-gear w-5 text-center text-brand-600 text-base"></i>
                                    <span>ตั้งค่าระบบ (Settings)</span>
                                </button>

                                <button type="button" @click="open = false; $dispatch('open-telegram-qr')"
                                    class="w-full text-left px-4 py-2.5 text-sm text-sky-800 hover:bg-sky-50 hover:text-sky-950 transition flex items-center gap-2.5 font-medium cursor-pointer">
                                    <i class="fa-brands fa-telegram w-5 text-center text-sky-500 text-base"></i>
                                    <span>QR แจ้งเตือน Telegram</span>
                                </button>

                                <a href="{{ route('admin.notifySettings') }}"
                                    class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100 transition flex items-center gap-2.5 font-medium">
                                    <i class="fa-solid fa-bell w-5 text-center text-amber-500 text-base"></i>
                                    <span>จัดการแจ้งเตือน</span>
                                </a>
                                <a href="{{ route('admin.management') }}"
                                    class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-100 transition flex items-center gap-2.5 font-medium">
                                    <i class="fa-solid fa-users-gear w-5 text-center text-indigo-500 text-base"></i>
                                    <span>จัดการผู้ใช้</span>
                                </a>
                            </div>

                            <div class="border-t border-gray-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition font-medium flex items-center gap-2.5 cursor-pointer">
                                    <i class="fa-solid fa-right-from-bracket w-5 text-center text-red-500"></i>
                                    <span>ออกจากระบบ</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('loginForm') }}"
                        class="text-sm font-medium text-gray-500 hover:text-brand-600">เข้าสู่ระบบ</a>
                @endif
            </div>

            <!-- Mobile Menu Button -->
            <div class="-mr-2 flex items-center md:hidden gap-2">
                @if (session()->has('user'))
                    <button type="button" @click="$dispatch('open-settings-modal')"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200 transition"
                        title="ตั้งค่า">
                        <i class="fa-solid fa-gear text-lg"></i>
                    </button>
                    <button type="button" @click="$dispatch('open-telegram-qr')"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-sky-600 bg-sky-50 hover:bg-sky-100 transition"
                        title="QR แจ้งเตือน Telegram">
                        <i class="fa-brands fa-telegram text-xl"></i>
                    </button>
                @endif
                <button @click="open = !open" type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <i class="fa-solid fa-bars text-xl" x-show="!open"></i>
                    <i class="fa-solid fa-xmark text-xl" x-show="open" x-cloak></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" x-cloak class="md:hidden border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('amr.index') }}"
                class="bg-brand-50 text-brand-700 block px-4 py-2.5 text-base font-semibold transition duration-150 ease-in-out">
                <i class="fa-solid fa-clipboard-list mr-2 text-brand-600"></i>
                รายการผู้ป่วย AMR
            </a>
            @if (session()->has('user'))
                <button type="button" @click="open = false; $dispatch('open-settings-modal')"
                    class="w-full text-left flex items-center px-4 py-2.5 text-base font-medium text-gray-700 hover:bg-gray-100 transition">
                    <i class="fa-solid fa-gear mr-2.5 text-gray-500 text-lg"></i>
                    ตั้งค่าระบบ (Settings)
                </button>
                <button type="button" @click="open = false; $dispatch('open-telegram-qr')"
                    class="w-full text-left flex items-center px-4 py-2.5 text-base font-medium text-sky-700 bg-sky-50/70 hover:bg-sky-100 transition">
                    <i class="fa-brands fa-telegram mr-2.5 text-sky-500 text-lg"></i>
                    QR แจ้งเตือน Telegram
                </button>
            @endif
        </div>
        @if (session()->has('user'))
            <div class="pt-4 pb-4 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <div
                            class="h-10 w-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 text-lg font-bold">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-bold text-gray-800">{{ session('user.fullname') }}</div>
                        <div class="text-sm font-medium text-gray-500">Username: {{ session('user.username') }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('admin.notifySettings') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fa-solid fa-bell w-5 text-center text-gray-400 mr-2"></i> จัดการแจ้งเตือน
                    </a>
                    <a href="{{ route('admin.management') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fa-solid fa-users-gear w-5 text-center text-gray-400 mr-2"></i> จัดการผู้ใช้
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full text-left block px-4 py-2 text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50">
                            <i class="fa-solid fa-right-from-bracket w-5 text-center text-red-500 mr-2"></i> ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- Telegram QR Code Modal (Global Alpine Component) -->
    <div x-show="qrModal" x-cloak role="dialog" aria-modal="true" aria-label="Telegram Notification"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs"
        @keydown.escape.window="qrModal = false">
        <div class="relative w-full max-w-sm overflow-hidden bg-white rounded-2xl shadow-2xl ring-1 ring-gray-200"
            @click.away="qrModal = false">
            <!-- Modal Header -->
            <div class="bg-brand-600 px-5 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-lg bg-white/15 flex items-center justify-center text-white">
                        <i class="fa-brands fa-telegram text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight">Telegram Notification</h3>
                        <p class="text-xs text-white/80">รับแจ้งเตือนผลแล็บอัตโนมัติ</p>
                    </div>
                </div>
                <button type="button" @click="qrModal = false"
                    class="rounded-lg p-1 text-white/80 hover:bg-white/10 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 text-center">
                <!-- Loading State -->
                <div x-show="isLoading" class="py-12">
                    <i class="fa-solid fa-circle-notch fa-spin text-3xl text-brand-500"></i>
                    <p class="mt-3 text-xs text-gray-500 font-medium">กำลังโหลด QR Code...</p>
                </div>

                <!-- QR Display -->
                <div x-show="!isLoading">
                    <div class="mx-auto w-64 h-64 p-3 bg-white border border-gray-200 rounded-2xl shadow-xs flex items-center justify-center">
                        <div x-html="qrSvg" class="w-full h-full flex items-center justify-center [&>svg]:w-full [&>svg]:h-full [&>svg]:rounded-lg"></div>
                    </div>

                    <div class="mt-4 space-y-1">
                        <p class="text-sm font-bold text-gray-800">สแกนด้วย Telegram</p>
                        <p class="text-xs text-gray-500">เพื่อเริ่มรับการแจ้งเตือนผลแล็บของคนไข้</p>
                    </div>

                    <!-- Status indicator -->
                    <div class="mt-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                        :class="isSubscribed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'">
                        <i class="fa-solid" :class="isSubscribed ? 'fa-circle-check text-emerald-500' : 'fa-circle-exclamation text-amber-500'"></i>
                        <span x-text="isSubscribed ? 'สถานะ: เชื่อมต่อรับการแจ้งเตือนแล้ว' : 'สถานะ: ยังไม่ได้เริ่มใช้งาน'"></span>
                    </div>

                    <!-- Action Link -->
                    <div class="mt-5 pt-4 border-t border-gray-100 flex flex-col gap-2">
                        <a :href="telegramUrl" target="_blank"
                            class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium rounded-xl shadow-sm transition">
                            <i class="fa-brands fa-telegram text-base"></i>
                            <span>เปิดใน Telegram App</span>
                        </a>
                        <button type="button" @click="qrModal = false"
                            class="w-full py-2 px-4 text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition font-medium">
                            ปิดหน้าต่าง
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinical Settings Hub Modal (Impeccable Design) -->
    <div x-show="settingsModal" x-cloak role="dialog" aria-modal="true" aria-label="ตั้งค่าระบบ"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 bg-gray-900/60 backdrop-blur-xs"
        @keydown.escape.window="if (!window.Swal || !Swal.isVisible()) settingsModal = false">
        <div data-settings-modal class="relative flex h-screen h-[100dvh] w-full max-w-6xl flex-col overflow-hidden bg-white shadow-2xl ring-1 ring-gray-200 sm:h-auto sm:max-h-[92vh] sm:rounded-2xl"
            @click.away="if (!window.Swal || !Swal.isVisible()) settingsModal = false">

            <!-- Settings Modal Header -->
            <div data-settings-header class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 py-3 text-gray-900 sm:px-6 sm:py-4">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-brand-100 flex items-center justify-center text-brand-700 text-base">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base leading-tight">ตั้งค่าระบบ (Clinical Settings Hub)</h3>
                        <p class="text-xs text-gray-500">ปรับแต่งธีม การมองเห็น และการจัดการข้อมูล AMR</p>
                    </div>
                </div>
                <button type="button" @click="settingsModal = false" aria-label="ปิดหน้าตั้งค่าระบบ"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-brand-500">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Settings Content with Left Tab Nav -->
            <div class="flex min-h-0 flex-grow flex-col overflow-hidden md:flex-row">

                <!-- Left Nav Tabs -->
                <div class="flex w-full shrink-0 gap-2 overflow-x-auto border-b border-gray-200 bg-gray-50/80 p-2 md:w-56 md:flex-col md:gap-1 md:overflow-y-auto md:border-b-0 md:border-r md:p-3">
                    <button type="button" @click="currentTab = 'themes'"
                        :class="currentTab === 'themes' ? 'bg-white text-brand-700 shadow-xs font-semibold ring-1 ring-gray-200' : 'text-gray-600 hover:bg-gray-100/80'"
                        class="flex w-auto shrink-0 items-center gap-2.5 min-h-10 whitespace-nowrap rounded-xl px-3 py-2.5 text-left text-xs transition md:w-full">
                        <i class="fa-solid fa-palette text-sm w-4 text-center" :class="currentTab === 'themes' ? 'text-brand-600' : 'text-gray-400'"></i>
                        <span>ธีม & การมองเห็น</span>
                    </button>

                    <button type="button" @click="currentTab = 'refresh'"
                        :class="currentTab === 'refresh' ? 'bg-white text-brand-700 shadow-xs font-semibold ring-1 ring-gray-200' : 'text-gray-600 hover:bg-gray-100/80'"
                        class="flex w-auto shrink-0 items-center gap-2.5 min-h-10 whitespace-nowrap rounded-xl px-3 py-2.5 text-left text-xs transition md:w-full">
                        <i class="fa-solid fa-arrows-rotate text-sm w-4 text-center" :class="currentTab === 'refresh' ? 'text-brand-600' : 'text-gray-400'"></i>
                        <span>ดึงข้อมูลอัตโนมัติ</span>
                    </button>

                    <button type="button" @click="currentTab = 'organisms'; loadMasterOrganisms()"
                        :class="currentTab === 'organisms' ? 'bg-white text-brand-700 shadow-xs font-semibold ring-1 ring-gray-200' : 'text-gray-600 hover:bg-gray-100/80'"
                        class="flex w-auto shrink-0 items-center gap-2.5 min-h-10 whitespace-nowrap rounded-xl px-3 py-2.5 text-left text-xs transition md:w-full">
                        <i class="fa-solid fa-bacterium text-sm w-4 text-center" :class="currentTab === 'organisms' ? 'text-brand-600' : 'text-gray-400'"></i>
                        <span>จัดการเชื้อดื้อยา AMR</span>
                    </button>

                    <button type="button" @click="currentTab = 'logs'; loadAuditLogs()"
                        :class="currentTab === 'logs' ? 'bg-white text-brand-700 shadow-xs font-semibold ring-1 ring-gray-200' : 'text-gray-600 hover:bg-gray-100/80'"
                        class="flex w-auto shrink-0 items-center gap-2.5 min-h-10 whitespace-nowrap rounded-xl px-3 py-2.5 text-left text-xs transition md:w-full">
                        <i class="fa-solid fa-clock-rotate-left text-sm w-4 text-center" :class="currentTab === 'logs' ? 'text-brand-600' : 'text-gray-400'"></i>
                        <span>ประวัติการเติมเชื้อ (Logs)</span>
                    </button>
                </div>

                <!-- Right Tab Body -->
                <div class="min-w-0 flex-grow space-y-6 overflow-y-auto p-4 sm:p-6">

                    <!-- TAB 1: Themes & Accessibility -->
                    <div x-show="currentTab === 'themes'" class="space-y-6">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-1">เลือกธีมหน้าจอ (Themes)</h4>
                            <p class="text-xs text-gray-500 mb-4">ปรับบรรยากาศสีของหน้าจอตามความถนัดของสายตาและการใช้งาน</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- ☀️ Light Mode -->
                                <div @click="setTheme('light')"
                                    @keydown.enter.prevent="setTheme('light')"
                                    @keydown.space.prevent="setTheme('light')"
                                    role="button" tabindex="0" data-theme-preview="light"
                                    :aria-pressed="selectedTheme === 'light' ? 'true' : 'false'"
                                    :class="selectedTheme === 'light' ? 'ring-2 ring-brand-500 border-brand-500 bg-sky-50/40' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                    class="p-4 rounded-xl border cursor-pointer transition relative flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-7 w-7 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-sm">
                                                <i class="fa-solid fa-sun"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900">โหมดสว่าง (Light)</p>
                                                <p class="text-xs text-gray-500">มาตรฐานโรงพยาบาล</p>
                                            </div>
                                        </div>
                                        <i x-show="selectedTheme === 'light'" class="fa-solid fa-circle-check text-brand-600 text-base"></i>
                                    </div>
                                    <div class="h-10 rounded-lg bg-gray-100 border border-gray-200 p-1.5 flex gap-1 items-center">
                                        <div class="h-full w-8 bg-white rounded border border-gray-200"></div>
                                        <div class="h-2 w-16 bg-sky-500 rounded"></div>
                                    </div>
                                </div>

                                <!-- 🌙 Dark Mode -->
                                <div @click="setTheme('dark')"
                                    @keydown.enter.prevent="setTheme('dark')"
                                    @keydown.space.prevent="setTheme('dark')"
                                    role="button" tabindex="0" data-theme-preview="dark"
                                    :aria-pressed="selectedTheme === 'dark' ? 'true' : 'false'"
                                    :class="selectedTheme === 'dark' ? 'ring-2 ring-sky-400 border-sky-400 bg-slate-900 text-white' : 'border-gray-700 bg-slate-900 text-white hover:border-slate-600'"
                                    class="p-4 rounded-xl border cursor-pointer transition relative flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-7 w-7 rounded-lg bg-slate-800 text-sky-400 flex items-center justify-center text-sm">
                                                <i class="fa-solid fa-moon"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-white">โหมดมืด (Dark Mode)</p>
                                                <p class="text-xs text-slate-400">ถนอมสายตาเวรดึก</p>
                                            </div>
                                        </div>
                                        <i x-show="selectedTheme === 'dark'" class="fa-solid fa-circle-check text-sky-400 text-base"></i>
                                    </div>
                                    <div class="h-10 rounded-lg bg-slate-800 border border-slate-700 p-1.5 flex gap-1 items-center">
                                        <div class="h-full w-8 bg-slate-900 rounded border border-slate-700"></div>
                                        <div class="h-2 w-16 bg-sky-400 rounded"></div>
                                    </div>
                                </div>

                                <!-- 👁️ High Contrast -->
                                <div @click="setTheme('high-contrast')"
                                    @keydown.enter.prevent="setTheme('high-contrast')"
                                    @keydown.space.prevent="setTheme('high-contrast')"
                                    role="button" tabindex="0" data-theme-preview="high-contrast"
                                    :aria-pressed="selectedTheme === 'high-contrast' ? 'true' : 'false'"
                                    :class="selectedTheme === 'high-contrast' ? 'ring-2 ring-black border-black bg-gray-50' : 'border-gray-300 hover:border-black bg-white'"
                                    class="p-4 rounded-xl border cursor-pointer transition relative flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-7 w-7 rounded-lg bg-black text-white flex items-center justify-center text-sm">
                                                <i class="fa-solid fa-eye"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-black">คอนทราสต์สูง (High Contrast)</p>
                                                <p class="text-xs text-gray-600">ขอบชัด ตัวหนังสือคมกริบ</p>
                                            </div>
                                        </div>
                                        <i x-show="selectedTheme === 'high-contrast'" class="fa-solid fa-circle-check text-black text-base"></i>
                                    </div>
                                    <div class="h-10 rounded-lg bg-white border-2 border-black p-1.5 flex gap-1 items-center">
                                        <div class="h-full w-8 bg-black rounded"></div>
                                        <div class="h-2 w-16 bg-black rounded"></div>
                                    </div>
                                </div>

                                <!-- 🍃 Eye-Care Sage -->
                                <div @click="setTheme('eye-care')"
                                    @keydown.enter.prevent="setTheme('eye-care')"
                                    @keydown.space.prevent="setTheme('eye-care')"
                                    role="button" tabindex="0" data-theme-preview="eye-care"
                                    :aria-pressed="selectedTheme === 'eye-care' ? 'true' : 'false'"
                                    :class="selectedTheme === 'eye-care' ? 'ring-2 ring-emerald-500 border-emerald-500 bg-emerald-50/50' : 'border-emerald-200 hover:border-emerald-300 bg-white'"
                                    class="p-4 rounded-xl border cursor-pointer transition relative flex flex-col justify-between focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="h-7 w-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">
                                                <i class="fa-solid fa-leaf"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-emerald-950">ถนอมสายตา (Eye-Care Sage)</p>
                                                <p class="text-xs text-emerald-700">โทนเขียวธรรมชาติสบายตา</p>
                                            </div>
                                        </div>
                                        <i x-show="selectedTheme === 'eye-care'" class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                                    </div>
                                    <div class="h-10 rounded-lg bg-emerald-50 border border-emerald-200 p-1.5 flex gap-1 items-center">
                                        <div class="h-full w-8 bg-white rounded border border-emerald-200"></div>
                                        <div class="h-2 w-16 bg-emerald-600 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Font Size Scaling -->
                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-bold text-gray-900 mb-1">ขนาดตัวอักษร (Font Size)</h4>
                            <p class="text-xs text-gray-500 mb-3">ปรับขนาดตัวหนังสือในระบบให้อ่านผลแล็บได้สะดวกที่สุด</p>

                            <div class="grid grid-cols-3 gap-3">
                                <button type="button" @click="setFontSize('normal')"
                                    :class="selectedFontSize === 'normal' ? 'bg-brand-50 border-brand-500 text-brand-700 ring-2 ring-brand-500/20 font-bold' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                                    class="p-3 rounded-xl border text-center transition">
                                    <span class="text-sm block mb-1">A</span>
                                    <span class="text-xs">ปกติ (100%)</span>
                                </button>
                                <button type="button" @click="setFontSize('medium')"
                                    :class="selectedFontSize === 'medium' ? 'bg-brand-50 border-brand-500 text-brand-700 ring-2 ring-brand-500/20 font-bold' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                                    class="p-3 rounded-xl border text-center transition">
                                    <span class="text-base font-semibold block mb-1">A</span>
                                    <span class="text-xs">ใหญ่ (110%)</span>
                                </button>
                                <button type="button" @click="setFontSize('large')"
                                    :class="selectedFontSize === 'large' ? 'bg-brand-50 border-brand-500 text-brand-700 ring-2 ring-brand-500/20 font-bold' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
                                    class="p-3 rounded-xl border text-center transition">
                                    <span class="text-lg font-bold block mb-1">A</span>
                                    <span class="text-xs">ใหญ่พิเศษ (125%)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Auto Refresh -->
                    <div x-show="currentTab === 'refresh'" class="space-y-4">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 mb-1">ดึงข้อมูลอัตโนมัติ (Auto-Refresh)</h4>
                            <p class="text-xs text-gray-500 mb-4">ตั้งค่าให้หน้ารายการ AMR ดึงผลแล็บและรายชื่อคนไข้ใหม่อัตโนมัติ โดยไม่ต้องกด F5</p>

                            <div class="space-y-2.5">
                                <label class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="auto_refresh" value="0" :checked="autoRefreshInterval === 0" @change="setAutoRefresh(0)" class="text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <span class="text-xs font-bold text-gray-800">ปิดการทำงาน (Manual)</span>
                                            <span class="block text-xs text-gray-400">กดค้นหาหรือรีเฟรชด้วยตนเอง</span>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400 font-medium">แนะนำสำหรับการใช้งานทั่วไป</span>
                                </label>

                                <label class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="auto_refresh" value="60" :checked="autoRefreshInterval === 60" @change="setAutoRefresh(60)" class="text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <span class="text-xs font-bold text-gray-800">ทุกๆ 1 นาที (60 วินาที)</span>
                                            <span class="block text-xs text-gray-400">อัปเดตต่อเนื่องแบบ Real-time</span>
                                        </div>
                                    </div>
                                    <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-md font-semibold">Live Monitor</span>
                                </label>

                                <label class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="auto_refresh" value="180" :checked="autoRefreshInterval === 180" @change="setAutoRefresh(180)" class="text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <span class="text-xs font-bold text-gray-800">ทุกๆ 3 นาที (180 วินาที)</span>
                                            <span class="block text-xs text-gray-400">สมดุลระหว่างความสดของข้อมูลและภาระเครื่อง</span>
                                        </div>
                                    </div>
                                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-md font-semibold">แนะนำสำหรับเวร</span>
                                </label>

                                <label class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 cursor-pointer transition">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="auto_refresh" value="300" :checked="autoRefreshInterval === 300" @change="setAutoRefresh(300)" class="text-brand-600 focus:ring-brand-500">
                                        <div>
                                            <span class="text-xs font-bold text-gray-800">ทุกๆ 5 นาที (300 วินาที)</span>
                                            <span class="block text-xs text-gray-400">เหมาะสำหรับจอแสดงผลในห้องตรวจ</span>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400 font-medium">Standard Dashboard</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Master AMR Organisms -->
                    <div x-show="currentTab === 'organisms'" class="space-y-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h4 class="text-base font-bold text-gray-900">จัดการรายการเชื้อดื้อยา (AMR Organisms)</h4>
                                <p class="text-xs text-gray-500">เพิ่มเชื้อตัวใหม่ หรือเปิด/ปิดการแสดงผลในฟอร์มเติมเชื้อ</p>
                            </div>
                            <button type="button" @click="showAddOrganismForm = !showAddOrganismForm; organismFormError = ''"
                                class="inline-flex min-h-10 w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-xs transition hover:bg-brand-700 sm:w-auto">
                                <i class="fa-solid fa-plus"></i>
                                <span>เพิ่มเชื้อตัวใหม่</span>
                            </button>
                        </div>

                        <!-- Add Organism Form Form -->
                        <div x-show="showAddOrganismForm" class="space-y-4 rounded-xl border border-sky-200 bg-sky-50/70 p-4 sm:p-5">
                            <p class="text-xs font-bold text-sky-900">เพิ่มรายการเชื้อดื้อยาตัวใหม่</p>
                            <div class="grid grid-cols-1 gap-3 text-xs lg:grid-cols-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">รหัสย่อ (Code)</label>
                                    <input type="text" x-model.trim="newOrganism.code" data-organism-code required maxlength="50" @input="organismFormError = ''" :aria-invalid="organismFormError ? 'true' : 'false'" aria-describedby="organism-form-error" placeholder="เช่น candida_auris" class="w-full px-2.5 py-1.5 border rounded-lg bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">ชื่อที่แสดง (Name)</label>
                                    <input type="text" x-model.trim="newOrganism.name" data-organism-name required maxlength="100" @input="organismFormError = ''" :aria-invalid="organismFormError ? 'true' : 'false'" aria-describedby="organism-form-error" placeholder="เช่น C. auris" class="w-full px-2.5 py-1.5 border rounded-lg bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">ระดับความรุนแรง</label>
                                    <select x-model="newOrganism.severity" class="w-full px-2.5 py-1.5 border rounded-lg bg-white">
                                        <option value="critical">Critical (สีแดงเข้ม)</option>
                                        <option value="high">High (สีส้ม)</option>
                                        <option value="medium">Medium (สีเหลืองอำพัน)</option>
                                        <option value="info">Info (สีฟ้า)</option>
                                    </select>
                                </div>
                                <div class="lg:col-span-3">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1">ชื่อเต็มทางวิทยาศาสตร์ (Full Name)</label>
                                    <input type="text" x-model.trim="newOrganism.full_name" maxlength="255" placeholder="เช่น Candida auris multi-drug resistant" class="w-full px-2.5 py-1.5 border rounded-lg bg-white">
                                </div>
                            </div>
                            <p id="organism-form-error" x-show="organismFormError" x-cloak x-text="organismFormError"
                                role="alert" aria-live="polite" class="text-xs font-medium text-red-700"></p>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" @click="showAddOrganismForm = false; organismFormError = ''" class="px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-200 rounded-lg">ยกเลิก</button>
                                <button type="button" @click="saveNewOrganism()" :disabled="savingOrganism" :aria-busy="savingOrganism" class="px-3 py-1.5 text-xs bg-brand-600 text-white font-medium rounded-lg hover:bg-brand-700 shadow-xs disabled:cursor-not-allowed disabled:opacity-60"><span x-text="savingOrganism ? 'กำลังบันทึก...' : 'บันทึกเชื้อใหม่'"></span></button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5 border-y border-gray-200 py-3 sm:flex-row sm:items-center sm:justify-between" aria-live="polite">
                            <p class="text-xs text-gray-600">
                                <span class="font-bold text-gray-900" x-text="activeMasterOrganisms().length"></span>
                                รายการเปิดใช้งาน · ลากที่จุดจับเพื่อจัดลำดับ หรือใช้ปุ่มขึ้น/ลง
                            </p>
                            <span x-show="organismOrderMessage" x-cloak x-text="organismOrderMessage"
                                :class="savingOrganismOrder ? 'text-amber-700' : 'text-emerald-700'"
                                class="shrink-0 text-xs font-semibold"></span>
                        </div>

                        <p x-show="organismListError" x-cloak x-text="organismListError"
                            role="alert" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-700"></p>

                        <div x-show="loadingOrganisms" class="flex min-h-32 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm text-gray-500">
                            <i class="fa-solid fa-spinner fa-spin mr-2" aria-hidden="true"></i>
                            กำลังโหลดรายการเชื้อ...
                        </div>

                        <!-- Active organisms: draggable and persisted -->
                        <div x-show="!loadingOrganisms" class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                            <div role="table" aria-label="รายการเชื้อดื้อยาที่เปิดใช้งาน" class="text-xs">
                                <div role="row" class="hidden min-h-10 grid-cols-[7rem_7rem_minmax(0,1fr)_6rem_6.5rem] items-center gap-x-3 border-b border-gray-200 bg-gray-50 px-3 font-semibold text-gray-600 md:grid">
                                    <div role="columnheader">ลำดับ</div>
                                    <div role="columnheader">ชื่อเชื้อ</div>
                                    <div role="columnheader">ชื่อเต็ม</div>
                                    <div role="columnheader" class="text-center">ระดับ</div>
                                    <div role="columnheader" class="text-center">สถานะ</div>
                                </div>
                                <div role="rowgroup" class="divide-y divide-gray-100">
                                    <template x-for="(org, idx) in activeMasterOrganisms()" :key="org.id">
                                        <div role="row"
                                            @dragover.prevent="dragOverOrganismId = org.id; $event.dataTransfer.dropEffect = 'move'"
                                            @dragleave="if (dragOverOrganismId === org.id) dragOverOrganismId = null"
                                            @drop.prevent="dropOrganism(org.id)"
                                            :class="{
                                                'opacity-45': draggingOrganismId === org.id,
                                                'bg-brand-50 ring-2 ring-inset ring-brand-400': dragOverOrganismId === org.id && draggingOrganismId !== org.id,
                                                'hover:bg-gray-50': dragOverOrganismId !== org.id
                                            }"
                                            class="group grid min-h-16 grid-cols-[6.5rem_minmax(0,1fr)_6.5rem] items-center gap-x-2 px-3 py-3 transition-colors md:grid-cols-[7rem_7rem_minmax(0,1fr)_6rem_6.5rem] md:gap-x-3 md:py-2.5">
                                            <div role="cell" class="flex items-center gap-1">
                                                <span data-organism-sort-handle :draggable="!savingOrganismOrder"
                                                    @dragstart="startOrganismDrag(org.id, $event)" @dragend="finishOrganismDrag()"
                                                    aria-hidden="true" title="ลากเพื่อจัดลำดับ"
                                                    class="inline-flex h-10 w-8 cursor-grab items-center justify-center rounded-lg text-gray-400 group-hover:bg-gray-100 group-hover:text-gray-700 active:cursor-grabbing">
                                                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                                                </span>
                                                <span class="w-5 text-center font-bold text-gray-700" x-text="idx + 1"></span>
                                                <span class="flex flex-col gap-0.5">
                                                    <button type="button" @click="moveOrganism(org.id, -1)"
                                                        :disabled="savingOrganismOrder || idx === 0"
                                                        :aria-label="`เลื่อน ${org.name} ขึ้น`" title="เลื่อนขึ้น"
                                                        class="inline-flex h-5 w-7 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-brand-500 disabled:cursor-not-allowed disabled:opacity-25">
                                                        <i class="fa-solid fa-chevron-up text-[9px]" aria-hidden="true"></i>
                                                    </button>
                                                    <button type="button" @click="moveOrganism(org.id, 1)"
                                                        :disabled="savingOrganismOrder || idx === activeMasterOrganisms().length - 1"
                                                        :aria-label="`เลื่อน ${org.name} ลง`" title="เลื่อนลง"
                                                        class="inline-flex h-5 w-7 items-center justify-center rounded text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-brand-500 disabled:cursor-not-allowed disabled:opacity-25">
                                                        <i class="fa-solid fa-chevron-down text-[9px]" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                            </div>

                                            <div role="cell" class="min-w-0 md:hidden">
                                                <p class="font-bold text-gray-900 break-words" x-text="org.name"></p>
                                                <p class="mt-0.5 text-gray-600 break-words" x-text="org.full_name || '-'" :title="org.full_name || ''"></p>
                                                <span class="mt-1.5 inline-flex rounded-full px-2 py-0.5 text-xs font-bold"
                                                    :style="`background-color: ${org.color}15; color: ${org.color}; border: 1px solid ${org.color}40;`"
                                                    x-text="org.severity"></span>
                                            </div>

                                            <div role="cell" class="hidden min-w-0 font-bold text-gray-900 break-words md:block" x-text="org.name"></div>
                                            <div role="cell" class="hidden min-w-0 text-gray-600 break-words md:block" x-text="org.full_name || '-'" :title="org.full_name || ''"></div>
                                            <div role="cell" class="hidden text-center md:block">
                                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-bold"
                                                    :style="`background-color: ${org.color}15; color: ${org.color}; border: 1px solid ${org.color}40;`"
                                                    x-text="org.severity"></span>
                                            </div>
                                            <div role="cell" class="text-right md:text-center">
                                                <button type="button" @click="toggleOrganism(org.id)"
                                                    :aria-label="`ปิดการใช้งาน ${org.name}`"
                                                    class="inline-flex min-h-9 min-w-20 items-center justify-center rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-emerald-600">
                                                    เปิดใช้
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div role="row" x-show="activeMasterOrganisms().length === 0">
                                        <div role="cell" class="px-4 py-12 text-center text-sm text-gray-500">ยังไม่มีรายการเชื้อที่เปิดใช้งาน</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Inactive legacy organisms stay available without mixing into the active order. -->
                        <div x-show="!loadingOrganisms && inactiveMasterOrganisms().length > 0" class="rounded-xl border border-gray-200 bg-gray-50/70">
                            <button type="button" @click="showInactiveOrganisms = !showInactiveOrganisms"
                                :aria-expanded="showInactiveOrganisms"
                                class="flex min-h-11 w-full items-center justify-between gap-3 px-4 py-3 text-left text-xs font-semibold text-gray-700 hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-brand-500">
                                <span>รายการที่ปิดใช้งาน (<span x-text="inactiveMasterOrganisms().length"></span>)</span>
                                <i class="fa-solid fa-chevron-down transition-transform" :class="showInactiveOrganisms && 'rotate-180'" aria-hidden="true"></i>
                            </button>
                            <div x-show="showInactiveOrganisms" class="border-t border-gray-200 px-3 py-2">
                                <template x-for="org in inactiveMasterOrganisms()" :key="org.id">
                                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-1 py-2.5 last:border-b-0">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-gray-700" x-text="org.name"></span>
                                            <span class="ml-2 text-gray-500 break-words" x-text="org.full_name || '-' "></span>
                                        </div>
                                        <button type="button" @click="toggleOrganism(org.id)"
                                            :aria-label="`เปิดการใช้งาน ${org.name}`"
                                            class="min-h-9 shrink-0 rounded-full bg-slate-700 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-slate-700">
                                            เปิดใช้งานอีกครั้ง
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: Audit Logs -->
                    <div x-show="currentTab === 'logs'" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">ประวัติการบันทึกเชื้อดื้อยา (Audit Logs)</h4>
                                <p class="text-xs text-gray-500">ตรวจสอบประวัติ 30 รายการล่าสุดว่าใครเป็นผู้บันทึกเชื้อให้คนไข้รายใด</p>
                            </div>
                            <button type="button" @click="loadAuditLogs()"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                <i class="fa-solid fa-rotate text-xs"></i>
                                <span>รีเฟรช</span>
                            </button>
                        </div>

                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-xs">
                            <table class="min-w-full divide-y divide-gray-200 text-xs">
                                <thead class="bg-gray-50 text-gray-600 uppercase font-semibold text-xs">
                                    <tr>
                                        <th class="px-3.5 py-2.5 text-left">HN / RegNo</th>
                                        <th class="px-3.5 py-2.5 text-left">วอร์ด</th>
                                        <th class="px-3.5 py-2.5 text-left">เชื้อที่ตรวจพบ</th>
                                        <th class="px-3.5 py-2.5 text-left">บันทึกโดย</th>
                                        <th class="px-3.5 py-2.5 text-right">วันเวลา</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <template x-for="log in auditLogs" :key="log.id">
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-3.5 py-2.5">
                                                <span class="font-bold text-gray-900" x-text="log.hn"></span>
                                                <span class="text-xs text-gray-400 ml-1" x-text="log.regist_flag ? `(${log.regist_flag})` : ''"></span>
                                            </td>
                                            <td class="px-3.5 py-2.5 font-medium text-gray-700" x-text="log.ward_id || '-'"></td>
                                            <td class="px-3.5 py-2.5">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="org in log.organisms" :key="org">
                                                        <span class="px-1.5 py-0.5 bg-red-50 border border-red-200 text-red-700 rounded text-xs font-bold" x-text="org"></span>
                                                    </template>
                                                    <span x-show="log.organisms.length === 0" class="text-gray-400 text-xs">ไม่มีเชื้อดื้อยา</span>
                                                </div>
                                            </td>
                                            <td class="px-3.5 py-2.5 font-medium text-teal-800" x-text="log.created_by"></td>
                                            <td class="px-3.5 py-2.5 text-right text-gray-500" x-text="log.updated_at"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div data-settings-footer class="flex shrink-0 items-center justify-between border-t border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500 sm:px-6">
                <span>บันทึกการตั้งค่าลงเบราว์เซอร์อัตโนมัติ</span>
                <button type="button" @click="settingsModal = false"
                    class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-xl transition shadow-xs">
                    เสร็จสิ้น
                </button>
            </div>
        </div>
    </div>
</nav>
