<nav x-data="{ open: false, userDropdown: false }" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="flex justify-between h-16">
            <!-- Logo & Brand -->
            <div class="flex">
                <a href="{{ route('index') }}" class="flex-shrink-0 flex items-center gap-3 group">
                    <img class="h-10 w-auto transition-transform group-hover:scale-105"
                        src="{{ asset('assets/img/logo-brh.png') }}" alt="Logo">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-800 text-lg leading-tight">โรงพยาบาลบุรีรัมย์</span>
                        <span class="text-xs text-brand-600 font-medium">Lab Result Notification</span>
                    </div>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex md:items-center md:space-x-8">
                <a href="{{ route('index') }}"
                    class="{{ Route::is('index') ? 'text-brand-600 border-b-2 border-brand-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent' }} inline-flex items-center px-1 pt-1 text-sm font-medium h-full transition duration-150 ease-in-out">
                    หน้าแรก
                </a>

                @if (session()->has('user'))
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open"
                            class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                            <span>{{ session('user.fullname') }}</span>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform"
                                :class="{ 'rotate-180': open }"></i>
                            <!-- Avatar Placeholder -->
                            <div
                                class="h-8 w-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 focus:outline-none"
                            style="display: none;">

                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs text-gray-500">เข้าสู่ระบบโดย</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ session('user.username') }}</p>
                            </div>

                            <a href="{{ route('admin.notifySettings') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <i class="fa-solid fa-bell w-5 text-center text-gray-400 mr-2"></i> จัดการแจ้งเตือน
                            </a>
                            <a href="{{ route('admin.management') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <i class="fa-solid fa-users-gear w-5 text-center text-gray-400 mr-2"></i> จัดการผู้ใช้
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('logout') }}"
                                class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                <i class="fa-solid fa-right-from-bracket w-5 text-center text-red-500 mr-2"></i>
                                ออกจากระบบ
                            </a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-medium text-gray-500 hover:text-brand-600">เข้าสู่ระบบ</a>
                @endif
            </div>

            <!-- Mobile Menu Button -->
            <div class="-mr-2 flex items-center md:hidden">
                <button @click="open = !open" type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <i class="fa-solid fa-bars text-xl" x-show="!open"></i>
                    <i class="fa-solid fa-xmark text-xl" x-show="open" style="display: none;"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" class="md:hidden border-t border-gray-200" style="display: none;">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('index') }}"
                class="{{ Route::is('index') ? 'bg-brand-50 border-brand-500 text-brand-700' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out">
                หน้าแรก
            </a>
        </div>
        @if (session()->has('user'))
            <div class="pt-4 pb-4 border-t border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <div
                            class="h-10 w-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 text-lg">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ session('user.fullname') }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ session('user.username') }}</div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('admin.notifySettings') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        จัดการแจ้งเตือน
                    </a>
                    <a href="{{ route('admin.management') }}"
                        class="block px-4 py-2 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        จัดการผู้ใช้
                    </a>
                    <a href="{{ route('logout') }}"
                        class="block px-4 py-2 text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50">
                        ออกจากระบบ
                    </a>
                </div>
            </div>
        @endif
    </div>
</nav>
