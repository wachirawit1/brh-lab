@extends('layout.app')
@section('title', 'รายชื่อผู้ป่วย')
@section('content')


    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
            <h4 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-hospital-user text-brand-600"></i>
                รายชื่อคนไข้
            </h4>
        </div>
        <div class="flex gap-2">
            <button id="addBotBtn"
                class="inline-flex items-center px-4 py-2 bg-white border border-brand-500 text-brand-600 rounded-lg shadow-sm hover:bg-brand-50 transition text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $telegram_status == 1 ? 'disabled' : '' }}>
                <i class="fa-solid fa-bell mr-2"></i>
                @if ($telegram_status == 0)
                    รับการแจ้งเตือน
                @elseif ($telegram_allowed == 1)
                    รับการแจ้งเตือนแล้ว
                @else
                    Admin ปิดชั่วคราว
                @endif
            </button>
            <button onclick="toggleModal('qrModal')"
                class="inline-flex items-center px-4 py-2 bg-white border border-brand-500 text-brand-600 rounded-lg shadow-sm hover:bg-brand-50 transition text-sm font-medium">
                <i class="fa-solid fa-qrcode mr-2"></i> QR Code
            </button>
        </div>
    </div>

    <!-- Search and Filter Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Search Input -->
            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search text-brand-500 mr-1"></i> ค้นหา
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="search" id="search"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 sm:text-sm shadow-sm"
                        placeholder="ชื่อ, HN, เบอร์โทร...">
                </div>
            </div>

            <!-- Date Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar text-brand-500 mr-1"></i> วันที่
                </label>
                <div class="relative flex">
                    <input type="text" id="dateInput" name="date"
                        class="block w-full rounded-l-lg border border-gray-300 focus:ring-brand-500 focus:border-brand-500 sm:text-sm shadow-sm"
                        placeholder="เลือกวันที่..." data-input>
                    <button type="button"
                        class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 bg-gray-50 text-gray-500 rounded-r-lg hover:bg-gray-100 transition shadow-sm"
                        data-clear>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Search Type -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-filter text-brand-500 mr-1"></i> ประเภท
                </label>
                <div class="flex rounded-md shadow-sm" role="group">
                    <div class="flex-1">
                        <input type="radio" name="searchBy" id="all" value="all" class="hidden peer" checked>
                        <label for="all"
                            class="h-full flex items-center justify-center cursor-pointer px-4 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-50 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:border-brand-500 z-10 transition">
                            <i class="fas fa-list-ul mr-2"></i> ทั้งหมด
                        </label>
                    </div>
                    <div class="flex-1">
                        <input type="radio" name="searchBy" id="ward" value="ward" class="hidden peer">
                        <label for="ward"
                            class="h-full flex items-center justify-center cursor-pointer px-4 py-1.5 text-sm font-medium text-gray-700 bg-white border border-l-0 border-gray-300 rounded-r-lg hover:bg-gray-50 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:border-brand-500 z-10 transition">
                            <i class="fas fa-hospital mr-2"></i> วอร์ด
                        </label>
                    </div>
                </div>
            </div>

            <!-- Ward Select -->
            <div class="md:col-span-3 hidden" id="wardSelectContainer">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-hospital text-brand-500 mr-1"></i> เลือกวอร์ด
                </label>
                <select id="wardSelect" class="w-full">
                    <option value="" selected disabled>กรุณาเลือกวอร์ด</option>
                    @foreach ($wards as $ward)
                        <option value="{{ $ward->ward_id }}">{{ $ward->ward_id }} - {{ $ward->ward_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Loading -->
        <div id="loading" class="text-center py-10 hidden">
            <i class="fas fa-circle-notch fa-spin text-3xl text-brand-500 mb-3"></i>
            <p class="text-gray-500">กำลังโหลดข้อมูล...</p>
        </div>

        <!-- Table Container -->
        <div id="tableContainer">
            @include('patients.table')
        </div>
    </div>

    <!-- Context Menu -->
    <div id="contextMenu" class="fixed z-50 bg-white shadow-xl rounded-lg border border-gray-100 py-1 w-48 hidden">
        <button id="addAllergy"
            class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600 transition flex items-center gap-2">
            <i class="fas fa-pills w-5 text-center text-red-500"></i> เพิ่มข้อมูลแพ้ยา
        </button>
        <button id="viewResult"
            class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-brand-600 transition flex items-center gap-2">
            <i class="fas fa-microscope w-5 text-center"></i> ดูผลแล็บ
        </button>
    </div>


    <!-- Floating Chat Button -->
    <button id="chatButton"
        class="fixed bottom-6 right-6 w-14 h-14 bg-brand-600 text-white rounded-full shadow-lg hover:bg-brand-700 flex items-center justify-center transition z-40">
        <i class="fa-solid fa-message text-xl"></i>
    </button>

    <!-- Chat Box -->
    <div id="chatBox"
        class="fixed bottom-24 right-6 w-80 bg-white shadow-2xl rounded-xl border border-gray-200 hidden z-40 overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h5 class="font-medium text-gray-800">ส่งข้อความทดสอบ</h5>
            <button type="button" id="closeChat" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">ผู้รับ</label>
                <select id="chatUser"
                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 p-2 border">
                    <option value="">-- เลือกผู้รับ --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->chat_id }}">{{ $user->pm }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">ข้อความ</label>
                <textarea id="chatMessage" rows="3"
                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-brand-500 focus:ring-brand-500 p-2 border"
                    placeholder="พิมพ์ข้อความ..."></textarea>
            </div>
            <button id="sendBtn"
                class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition text-sm font-medium">
                ส่งข้อความ
            </button>
        </div>
    </div>

    <!-- Lab Modal (Tailwind) -->
    <div id="labModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="toggleModal('labModal')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <!-- Header -->
                <div class="bg-brand-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center gap-2" id="modal-title">
                        <i class="fas fa-flask"></i> ผลการตรวจทางห้องปฏิบัติการ
                    </h3>
                    <button type="button" onclick="toggleModal('labModal')" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b border-gray-100">
                        <div>
                            <p class="text-sm text-gray-500">HN</p>
                            <p id="lab-modal-hn" class="text-lg font-bold text-brand-700"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">ชื่อ-สกุล</p>
                            <p id="lab-modal-name" class="text-lg font-bold text-gray-800"></p>
                        </div>
                    </div>

                    <div id="lab-results-container">
                        <!-- Lab Results Injected Here -->
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-brand-600 text-base font-medium text-white hover:bg-brand-700 focus:outline-none sm:w-auto sm:text-sm"
                        onclick="window.print()">
                        <i class="fas fa-print mr-2"></i> พิมพ์
                    </button>
                    <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                        onclick="toggleModal('labModal')">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Modal (Tailwind) -->
    <div id="qrModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                onclick="toggleModal('qrModal')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-center">
                    <div
                        class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-brand-100 mb-4">
                        <i class="fa-solid fa-qrcode text-brand-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Telegram QR Code</h3>
                    <div class="mt-4 flex justify-center py-4">
                        {!! $qr !!}
                    </div>
                    <p class="text-sm text-gray-500 mt-2">สแกนเพื่อรับการแจ้งเตือน</p>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm"
                        onclick="toggleModal('qrModal')">
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('indexScript')
    <script>
        // Use Global toggleModal
        window.toggleModal = function(modalID) {
            const modal = document.getElementById(modalID);
            if (modal) {
                modal.classList.toggle('hidden');
            }
        }

        // Context Menu Logic
        const contextMenu = document.getElementById("contextMenu");
        let selectedHN = null;
        let selectedRow = null;

        function addContextMenuToRows() {
            document.querySelectorAll("#userTable tbody tr").forEach((row) => {
                row.addEventListener("contextmenu", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    selectedHN = this.dataset.hn;
                    selectedRow = this;

                    // Calc position
                    const menuWidth = 192; // w-48
                    const menuHeight = 100;
                    let x = e.clientX;
                    let y = e.clientY;

                    if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
                    if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;

                    contextMenu.style.left = `${x}px`;
                    contextMenu.style.top = `${y}px`;
                    contextMenu.classList.remove('hidden');

                    // Highlight
                    document.querySelectorAll("#userTable tbody tr").forEach((r) => r.classList.remove(
                        "bg-brand-50"));
                    this.classList.add("bg-brand-50");
                });
            });
        }
        addContextMenuToRows();

        // Close Context Menu
        document.addEventListener("click", function(e) {
            if (!contextMenu.contains(e.target)) {
                contextMenu.classList.add('hidden');
                document.querySelectorAll("#userTable tbody tr").forEach((r) => r.classList.remove("bg-brand-50"));
            }
        });
        window.addEventListener("scroll", function() {
            contextMenu.classList.add('hidden');
        });

        // View Lab Result JS
        document.getElementById("viewResult").addEventListener("click", function() {
            if (selectedHN && selectedRow) {
                const hn = selectedRow.dataset.hn;
                const fullname = selectedRow.dataset.name;

                document.getElementById("lab-modal-hn").textContent = hn;
                document.getElementById("lab-modal-name").textContent = fullname;

                const container = document.getElementById("lab-results-container");
                if (!container) return;

                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-circle-notch fa-spin text-3xl text-brand-500 mb-3"></i>
                        <p class="text-gray-500">กำลังโหลดผลแล็บ...</p>
                    </div>`;

                // Open Modal (Tailwind)
                document.getElementById("labModal").classList.remove("hidden");

                // Fetch
                fetchLabResults(hn);

                // Close Menu
                contextMenu.classList.add("hidden");
            }
        });
        // Add Allergy JS (Redirect to new page)
        document.getElementById("addAllergy").addEventListener("click", function() {
            if (selectedHN && selectedRow) {
                const hn = selectedRow.dataset.hn;
                const fullname = selectedRow.dataset.name;

                // Close Menu
                contextMenu.classList.add("hidden");

                // Redirect to new page with HN and Name as parameters
                window.location.href = `/patients/${hn}/allergy?name=${encodeURIComponent(fullname)}`;
            }
        });

        function fetchLabResults(hn) {
            fetch(`/lab-results/${hn}`, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                .then((response) => response.json())
                .then((data) => {
                    displayLabResults(data.labResults);
                })
                .catch((error) => {
                    console.error("Error:", error);
                    document.getElementById("lab-results-container").innerHTML = `
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">เกิดข้อผิดพลาดในการโหลดผลแลป</span>
                        </div>`;
                });
        }

        function displayLabResults(labResults) {
            const container = document.getElementById("lab-results-container");

            if (!labResults || labResults.length === 0) {
                container.innerHTML = `
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-6 py-8 rounded-lg text-center">
                    <i class="fas fa-flask text-3xl mb-3 text-blue-400"></i>
                    <h5 class="font-medium">ไม่พบผลแลป</h5>
                    <p class="text-sm mt-1">ไม่มีผลการตรวจทางห้องปฏิบัติการสำหรับ HN นี้</p>
                </div>`;
                return;
            }

            let html = `
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                <i class="fas fa-list text-brand-500"></i>
                <h6 class="font-medium text-gray-700">รายการตรวจทั้งหมด</h6>
                <span class="bg-brand-100 text-brand-700 text-xs px-2 py-0.5 rounded-full ml-auto">${labResults.length} รายการ</span>
            </div>
            <div class="space-y-2">`;

            labResults.forEach((lab, index) => {
                const isOpen = index === 0 ? '' : 'hidden';
                const id = `lab-content-${index}`;
                const icon = index === 0 ? 'fa-chevron-up' : 'fa-chevron-down';

                html += `
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button type="button" class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex justify-between items-center transition"
                        onclick="toggleLab('${id}', this)">
                        <div class="flex items-center gap-3">
                             <span class="bg-white border border-gray-200 text-gray-500 text-xs w-6 h-6 flex items-center justify-center rounded-full">${index + 1}</span>
                             <div class="text-sm font-medium text-gray-700">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> ${lab.res_date}
                            </div>
                        </div>
                        <i class="fas ${icon} text-gray-400 text-xs transition-transform transform"></i>
                    </button>
                    <div id="${id}" class="${isOpen} px-4 py-3 bg-white border-t border-gray-200">
                        <pre class="bg-gray-50 p-4 rounded-md text-sm text-gray-800 font-mono whitespace-pre-wrap leading-relaxed shadow-inner border border-gray-100">${lab.resText}</pre>
                    </div>
                </div>`;
            });

            html += `</div>`;
            container.innerHTML = html;
        }

        // Helper for simple accordion
        window.toggleLab = function(id, btn) {
            const content = document.getElementById(id);
            const icon = btn.querySelector('.fa-chevron-down, .fa-chevron-up');

            content.classList.toggle('hidden');
            if (content.classList.contains('hidden')) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }

        document.getElementById('addBotBtn').addEventListener('click', function() {
            const botUsername = 'brh_test_bot';
            const startParam = "{{ session('user.username') }}";
            window.open(`https://t.me/${botUsername}?start=${startParam}`, '_blank');
            alert("กรุณากด Start ใน Telegram Bot เพื่อเปิดการแจ้งเตือน");
            this.classList.add('opacity-50', 'cursor-not-allowed');
            this.disabled = true;
        });

        // Search JS (Adapted)
        $(document).ready(function() {
            // Flatpickr
            const fpicker = flatpickr("#dateInput", {
                locale: "th",
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
                altInput: true,
                allowInput: true,
                monthSelectorType: 'static',
                yearSelectorType: 'static',
                disableMobile: true,
                maxDate: "today",
                theme: "material_blue"
            });

            // Select2 (Standard Theme)
            $('#wardSelect').select2({
                width: '100%',
                placeholder: 'ค้นหาวอร์ด...',
                allowClear: true
            });

            // Show/Hide Ward Select
            $('input[name="searchBy"]').on("change", function() {
                if ($(this).val() === "ward") {
                    $("#wardSelectContainer").removeClass("hidden");
                    $("#dateInput").val("");
                    fpicker.clear();
                } else {
                    $("#wardSelectContainer").addClass("hidden");
                    $("#wardSelect").val(null).trigger('change');
                }
                doSearch(1);
            });

            // Fix Select2 Search Focus when manually opened
            $(document).on('select2:open', () => {
                setTimeout(() => {
                    const searchField = document.querySelector('.select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 10);
            });

            // Re-bind actions
            function doSearch(page = 1) {
                $("#loading").removeClass("hidden");
                $("#tableContainer").addClass("hidden");

                // ... keep existing ajax logic but careful with selectors ...
                // For brevity, assuming doSearch logic in original code uses IDs mostly, which I preserved (#search, #dateInput, #wardSelect)

                let requestData = {
                    page: page
                };
                const searchTerm = $("#search").val().trim();
                const wardValue = $("#wardSelect").val();
                const searchBy = $('input[name="searchBy"]:checked').val();
                const dateValue = $("#dateInput").val();

                if (searchTerm.length >= 2) requestData.search = searchTerm;
                if (searchBy === 'ward' && wardValue) requestData.ward = wardValue;
                if (searchBy === 'all' && dateValue) requestData.date = dateValue;

                $.ajax({
                    url: window.location.pathname,
                    type: "GET",
                    data: requestData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    success: function(response) {
                        if (response.html) $("#tableContainer").html(response.html);
                        addContextMenuToRows();
                        // Update URL...
                    },
                    complete: function() {
                        $("#loading").addClass("hidden");
                        $("#tableContainer").removeClass("hidden");
                    }
                });
            }

            $("#search").on("input", function() {
                setTimeout(() => {
                    if ($(this).val().length === 0 || $(this).val().length >= 2) doSearch(1);
                }, 600);
            });
            $("#dateInput").on("change", () => doSearch(1));
            $("#wardSelect").on("change", () => doSearch(1));
            $("[data-clear]").on("click", () => {
                fpicker.clear();
                doSearch(1);
            });
            $(document).on("click", ".pagination a", function(e) {
                e.preventDefault();
                doSearch(new URLSearchParams($(this).attr("href").split("?")[1]).get("page"));
            });
        });

        // Chat Toggle
        document.getElementById('chatButton').addEventListener('click', function() {
            const box = document.getElementById('chatBox');
            box.classList.toggle('hidden');
        });
        document.getElementById('closeChat').addEventListener('click', function() {
            document.getElementById('chatBox').classList.add('hidden');
        });

        document.getElementById('sendBtn').addEventListener('click', async function() {
            const chatUser = document.getElementById('chatUser').value;
            const chatMessage = document.getElementById('chatMessage').value;
            if (!chatUser || !chatMessage.trim()) return alert('กรุณาระบุข้อมูลให้ครบ');

            // ... fetch logic ...
            const res = await fetch('/telegram/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    chat_id: chatUser,
                    message: chatMessage
                })
            });
            const data = await res.json();
            if (data.status === 'ok') {
                alert('ส่งข้อความสำเร็จ');
                document.getElementById('chatMessage').value = '';
            } else alert('ส่งไม่สำเร็จ');
        });
    </script>
@endpush
