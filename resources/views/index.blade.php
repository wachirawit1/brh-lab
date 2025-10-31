@extends('layout.app')
@section('title', 'รายชื่อผู้ป่วย')
@section('content')
    <div class="container-fluid my-4">
        <!-- Header Section -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h4 class="mb-0">
                    <i class="fas fa-hospital-user text-primary me-2"></i>
                    รายชื่อคนไข้
                </h4>
            </div>
            <div class="col-md-6 text-md-end">
                <button id="addBotBtn"
                    class="btn btn-outline-primary
               {{ $telegram_status == 1 ? 'disabled' : '' }}"
                    {{ $telegram_status == 1 ? 'disabled' : '' }}>
                    <i class="fa-solid fa-bell me-1"></i>
                    @if ($telegram_status == 0)
                        รับการแจ้งเตือน
                    @elseif ($telegram_allowed == 1)
                        รับการแจ้งเตือนแล้ว
                    @else
                        Admin ปิดการแจ้งเตือนชั่วคราว
                    @endif
                </button>
                <button class="btn btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#qrModal">
                    <i class="fa-solid fa-qrcode me-1"></i>QR Code
                </button>
            </div>
        </div>

        <!-- Search and Filter Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-search text-primary me-1"></i>ค้นหา
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="search" id="search" class="form-control border-start-0"
                                placeholder="ค้นหาด้วย ชื่อ, HN, เบอร์โทร...">
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary me-1"></i>วันที่
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="far fa-calendar text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="dateInput" name="date"
                                placeholder="เลือกวันที่..." data-input>
                            <button class="btn btn-outline-secondary" type="button" data-clear>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Search Type -->
                    <div class="col-md-2">
                        <label class="form-label">
                            <i class="fas fa-filter text-primary me-1"></i>ประเภท
                        </label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="searchBy" id="all" checked>
                            <label class="btn btn-outline-primary" for="all">
                                <i class="fas fa-list-ul me-1"></i>ทั้งหมด
                            </label>
                            <input type="radio" class="btn-check" name="searchBy" id="ward">
                            <label class="btn btn-outline-primary" for="ward">
                                <i class="fas fa-hospital me-1"></i>วอร์ด
                            </label>
                        </div>
                    </div>

                    <!-- Ward Select -->
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-hospital text-primary me-1"></i>เลือกวอร์ด
                        </label>
                        <select class="form-select d-none" id="wardSelect">
                            <option value="" selected disabled>กรุณาเลือกวอร์ด</option>
                            @foreach ($wards as $ward)
                                <option value="{{ $ward->ward_id }}">{{ $ward->ward_id }} - {{ $ward->ward_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card shadow-sm">
            <div class="card-body">
                <!-- Loading indicator -->
                <div id="loading" class="text-center my-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                </div>

                <!-- Table Container -->
                <div id="tableContainer">
                    @include('patients.table')
                </div>
            </div>
        </div>

        <!-- Context Menu -->
        <div class="dropdown-menu" id="contextMenu" style="position: fixed; z-index: 1050; display: none;">
            <button class="dropdown-item" id="viewResult">ดูผล</button>
            {{-- <button class="dropdown-item" id="editUser">แก้ไข</button>
            <button class="dropdown-item text-danger" id="deleteUser">ลบ</button> --}}
        </div>

        <!-- ปุ่มลอย -->
        <button id="chatButton" class="btn btn-primary">
            <i class="fa-solid fa-message" style="color: #fafafa;"></i>
        </button>

        <!-- กล่องแชท -->
        <div id="chatBox" class="border">
            <div class="d-flex justify-content-between align-items-center p-2 border-bottom bg-light">
                <strong>ทดสอบส่งข้อความ</strong>
                <button type="button" class="btn-close" aria-label="Close" id="closeChat"></button>
            </div>

            <div class="p-3">
                <div class="mb-2">
                    <label class="form-label">เลือกผู้รับ:</label>
                    <select id="chatUser" class="form-select">
                        <option value="">-- เลือกผู้รับ --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->chat_id }}">{{ $user->pm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <textarea id="chatMessage" class="form-control" rows="3" placeholder="พิมพ์ข้อความ..."></textarea>
                </div>

                <button id="sendBtn" class="btn btn-success w-100">
                    ส่งข้อความ
                </button>
            </div>
        </div>

        <!-- Modal สำหรับแสดงผลข้อมูล -->
        <!-- Lab Results Modal -->
        <div class="modal fade" id="labModal" tabindex="-1" aria-labelledby="labModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="labModalLabel">
                            <i class="fas fa-flask me-2"></i>ผลการตรวจทางห้องปฏิบัติการ
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-id-card text-muted me-2"></i>HN: <span id="lab-modal-hn"
                                        class="text-primary"></span></h6>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-user text-muted me-2"></i>ชื่อ: <span id="lab-modal-name"
                                        class="text-primary"></span></h6>
                            </div>
                        </div>
                        <hr>
                        <div id="lab-results-container">
                            <!-- ผลแลปจะแสดงที่นี่ -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>ปิด
                        </button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>พิมพ์
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- QR Code Modal -->
        <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="qrModalLabel">
                            <i class="fa-solid fa-qrcode me-2"></i>รับการแจ้งเตือนผ่าน Telegram (เฉพาะบุคคล)
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        {!! $qr !!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('indexScript')
    {{-- cotext --}}
    <script>
        // Context Menu
        const contextMenu = document.getElementById("contextMenu");
        let selectedHN = null;
        let selectedRow = null;

        // เพิ่ม event listener ให้กับแถวในตาราง
        function addContextMenuToRows() {
            document.querySelectorAll("#userTable tbody tr").forEach((row) => {
                row.addEventListener("contextmenu", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    selectedHN = this.dataset.hn;
                    selectedRow = this;

                    // คำนวณตำแหน่งของเมนู
                    const menuWidth = 120;
                    const menuHeight = 120;
                    const windowWidth = window.innerWidth;
                    const windowHeight = window.innerHeight;

                    let x = e.clientX;
                    let y = e.clientY;

                    // ปรับตำแหน่งไม่ให้เมนูออกนอกหน้าจอ
                    if (x + menuWidth > windowWidth) {
                        x = windowWidth - menuWidth - 10;
                    }

                    if (y + menuHeight > windowHeight) {
                        y = windowHeight - menuHeight - 10;
                    }

                    // แสดงเมนู
                    contextMenu.style.left = `${x}px`;
                    contextMenu.style.top = `${y}px`;
                    contextMenu.style.display = "block";

                    // เพิ่ม highlight ให้แถวที่เลือก
                    document
                        .querySelectorAll("#userTable tbody tr")
                        .forEach((r) => r.classList.remove("table-active"));
                    this.classList.add("table-active");
                });
            });
        }

        // เรียกใช้ function ครั้งแรก
        addContextMenuToRows();

        // ซ่อนเมนูเมื่อคลิกที่อื่น
        document.addEventListener("click", function(e) {
            if (!contextMenu.contains(e.target)) {
                contextMenu.style.display = "none";
                // ลบ highlight
                document
                    .querySelectorAll("#userTable tbody tr")
                    .forEach((r) => r.classList.remove("table-active"));
            }
        });

        // ซ่อนเมนูเมื่อ scroll
        window.addEventListener("scroll", function() {
            contextMenu.style.display = "none";
            document
                .querySelectorAll("#userTable tbody tr")
                .forEach((r) => r.classList.remove("table-active"));
        });

        // แก้ไข Event listener สำหรับปุ่ม "ดูผล" ให้แสดงผลแลป
        document.getElementById("viewResult").addEventListener("click", function() {
            if (selectedHN && selectedRow) {
                // ดึงข้อมูลพื้นฐานจาก data attributes
                const hn = selectedRow.dataset.hn;
                const fullname = selectedRow.dataset.name;

                // อัพเดทข้อมูลใน Modal Header (ตรวจสอบก่อน)
                const hnElement = document.getElementById("lab-modal-hn");
                const nameElement = document.getElementById("lab-modal-name");

                if (hnElement) hnElement.textContent = hn;
                if (nameElement) nameElement.textContent = fullname;

                // แสดง loading (ตรวจสอบ element ก่อน)
                const labResultsContainer = document.getElementById(
                    "lab-results-container"
                );
                if (!labResultsContainer) {
                    console.error("ไม่พบ element lab-results-container");
                    alert("เกิดข้อผิดพลาด: ไม่พบ Modal สำหรับแสดงผลแลป");
                    return;
                }

                labResultsContainer.innerHTML = `
    <div class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">กำลังโหลดผลแลป...</p>
    </div>
    `;

                // แสดง Modal (ตรวจสอบก่อน)
                const modalElement = document.getElementById("labModal");
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.error("ไม่พบ Modal labModal");
                    alert("เกิดข้อผิดพลาด: ไม่พบ Modal สำหรับแสดงผลแลป");
                    return;
                }

                // ดึงข้อมูลผลแลป
                fetchLabResults(hn);

                // ซ่อนเมนู
                contextMenu.style.display = "none";
                document
                    .querySelectorAll("#userTable tbody tr")
                    .forEach((r) => r.classList.remove("table-active"));
            }
        });

        // ฟังก์ชันดึงผลแลป (ใช้ข้อมูลที่มีอยู่แล้วใน Controller)
        function fetchLabResults(hn) {
            // ส่งข้อมูล HN ไปให้ Controller ประมวลผล
            fetch(`/lab-results/${hn}`, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                            .content,
                    },
                })
                .then((response) => response.json())
                .then((data) => {
                    displayLabResults(data.labResults);
                })
                .catch((error) => {
                    console.error("Error:", error);
                    document.getElementById("lab-results-container").innerHTML = `
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        เกิดข้อผิดพลาดในการโหลดผลแลป
    </div>
    `;
                });
        }

        // ฟังก์ชันแสดงผลแลป
        function displayLabResults(labResults) {
            const container = document.getElementById("lab-results-container");

            if (!labResults || labResults.length === 0) {
                container.innerHTML = `
            <div class="alert alert-info text-center">
                <i class="fas fa-flask"></i>
                <h5 class="mt-2">ไม่พบผลแลป</h5>
                <p class="mb-0">ไม่มีผลการตรวจทางห้องปฏิบัติการสำหรับ HN นี้</p>
            </div>
        `;
                return;
            }

            let html = `
        <div class="row mb-3">
            <div class="col-12">
                <h6 class="border-bottom pb-2">
                    <i class="fas fa-microscope text-primary"></i>
                    ผลการตรวจทางห้องปฏิบัติการ
                    <span class="badge bg-primary ms-2">${labResults.length} รายการ</span>
                </h6>
            </div>
        </div>
        <div class="accordion" id="labAccordion">
    `;

            // แสดงผลแล็บแต่ละรายการด้วย Accordion
            labResults.forEach((lab, index) => {
                html += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading${index}">
                    <button class="accordion-button ${index === 0 ? '' : 'collapsed'}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse${index}">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                วันที่: ${lab.res_date}
                            </div>
                            <span class="badge bg-secondary ms-2">#${index + 1}</span>
                        </div>
                    </button>
                </h2>
                <div id="collapse${index}"
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}"
                     data-bs-parent="#labAccordion">
                    <div class="accordion-body">
                        <pre class="bg-light p-3 rounded mb-0"
                             style="white-space: pre-wrap; font-family: monospace; font-size: 0.9em;">${lab.resText}</pre>
                    </div>
                </div>
            </div>
        `;
            });

            html += `</div>`;
            container.innerHTML = html;
        }

        // document.getElementById("editUser").addEventListener("click", function() {
        //     if (selectedHN) {
        //         alert("แก้ไข HN: " + selectedHN);
        //         contextMenu.style.display = "none";
        //     }
        // });

        // ตัวอย่างตอนกดปุ่ม "ลบผู้ป่วย"
        // document.getElementById("deleteUser").addEventListener("click", function() {
        //     if (selectedHN && selectedRow) {
        //         if (confirm("คุณต้องการลบผู้ใช้ HN: " + selectedHN + " หรือไม่?")) {
        //             // ลบแถวออกจากตาราง
        //             selectedRow.remove();

        //             // ส่งแจ้งเตือนไป Telegram
        //             fetch("{{ route('notify') }}", {
        //                 method: "POST",
        //                 headers: {
        //                     "Content-Type": "application/json",
        //                     "X-CSRF-TOKEN": "{{ csrf_token() }}",
        //                 },
        //                 body: JSON.stringify({
        //                     hn: selectedHN,
        //                     firstname: selectedRow.dataset.firstname,
        //                     lastname: selectedRow.dataset.lastname,
        //                     action: "ลบผู้ป่วย",
        //                 }),
        //             });

        //             alert("ลบ HN: " + selectedHN + " เรียบร้อยแล้ว");
        //         }

        //         contextMenu.style.display = "none";
        //         selectedRow.classList.remove("table-active");
        //     }
        // });

        // ป้องกันการแสดง context menu ของเบราว์เซอร์
        document.addEventListener("contextmenu", function(e) {
            if (e.target.closest("#userTable tbody tr")) {
                e.preventDefault();
            }
        });
    </script>
    {{-- end cotext --}}

    {{-- <script src="{{ asset('js/context.js') }}"></script> --}}
    <script>
        document.getElementById('addBotBtn').addEventListener('click', function() {
            const botUsername = 'brh_test_bot';
            const startParam = "{{ session('user.username') }}";

            window.open(`https://t.me/${botUsername}?start=${startParam}`, '_blank');

            alert("กรุณากด Start ใน Telegram Bot เพื่อเปิดการแจ้งเตือน");

            this.classList.add('disabled');
        });
    </script>
    {{-- <script src="{{ asset('js/search.js') }}"></script> --}}
    <script>
        // search.js
        $(document).ready(function() {
            let searchTimeout;
            let currentRequest = null;
            let isSearching = false;

            // ค้นหาแบบ Real-time
            $("#search").on("input", function() {
                clearTimeout(searchTimeout);
                const searchTerm = $(this).val();

                // ยกเลิก request เก่าถ้ามี
                if (currentRequest && currentRequest.readyState !== 4) {
                    currentRequest.abort();
                    currentRequest = null;
                }

                // รอให้พิมพ์เสร็จ 600ms
                searchTimeout = setTimeout(function() {
                    // ค้นหาต่อเมื่อ: ว่างเปล่า หรือ มีอักขระ >= 2 ตัว
                    if (searchTerm.length === 0 || searchTerm.trim().length >= 2) {
                        doSearch(1);
                    }
                }, 600);
            });

            $("#dateInput").on("change", function() {
                const searchBy = $('input[name="searchBy"]:checked').attr("id");

                if (searchBy === "all") {
                    doSearch(1);
                }
            });

            $("[data-clear]").on("click", function() {
                // ตรวจสอบว่า input มีค่าหรือไม่
                if ($("#dateInput").val() === "" || $("#dateInput").val() === null) {
                    return; // ถ้าไม่มีค่า ไม่ทำอะไร
                }
                $("#dateInput").val("");

                if (typeof fpicker !== "undefined" && fpicker) {
                    fpicker.clear();
                }

                if ($('input[name="searchBy"]:checked').attr("id") === "all") {
                    doSearch(1);
                }
            });

            $("#wardSelect").on("change", function() {
                if ($('input[name="searchBy"]:checked').attr("id") === "ward") {
                    doSearch(1);
                }
            });

            $('input[name="searchBy"]').on("change", function() {
                const selectedMode = $(this).attr("id");

                if (selectedMode === "ward") {
                    $("#wardSelect").parent().show();
                    $("#wardSelect").removeClass("d-none");

                    $("#dateInput").val("");
                    if (typeof fpicker !== "undefined") {
                        fpicker.clear();
                    }
                } else {
                    $("#wardSelect").parent().hide();
                    $("#wardSelect").addClass("d-none");
                    $("#wardSelect").val(null).trigger("change");
                }

                doSearch(1);
            });

            function doSearch(page = 1) {
                // ป้องกันการค้นหาซ้ำซ้อน
                if (isSearching) {
                    console.log('⏸️ Search in progress, skipping...');
                    return;
                }

                page = Math.max(1, parseInt(page) || 1);

                const searchTerm = $("#search").val().trim();
                const dateValue = $("#dateInput").val();
                const wardValue = $("#wardSelect").val();
                const searchBy = $('input[name="searchBy"]:checked').attr("id");

                // ยกเลิก request เก่า
                if (currentRequest && currentRequest.readyState !== 4) {
                    currentRequest.abort();
                    currentRequest = null;
                }

                isSearching = true;
                $("#loading").show();
                $("#tableContainer").hide();

                let requestData = {
                    page: page,
                };

                // ต้องมีอย่างน้อย 2 ตัวอักษรถึงจะค้นหา
                if (searchTerm && searchTerm.length >= 2) {
                    requestData.search = searchTerm;
                }

                if (searchBy === "all" && dateValue) {
                    requestData.date = dateValue;
                }

                if (searchBy === "ward" && wardValue) {
                    requestData.ward = wardValue;
                }

                console.log('🔍 Searching:', requestData);

                currentRequest = $.ajax({
                    url: window.location.pathname,
                    type: "GET",
                    data: requestData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    beforeSend: function(xhr) {
                        // เก็บ request object
                        currentRequest = xhr;
                    },
                    success: function(response) {
                        console.log('✅ Success');

                        if (response.html) {
                            $("#tableContainer").html(response.html);
                        } else {
                            console.warn('No HTML in response');
                        }

                        if (response.pagination) {
                            $("#paginationContainer").html(response.pagination);
                        } else {
                            $("#paginationContainer").empty();
                        }

                        if (typeof addContextMenuToRows === "function") {
                            addContextMenuToRows();
                        }

                        updateURL(requestData);
                    },
                    error: function(xhr, status, error) {
                        // ไม่แสดง error ถ้าถูก abort
                        if (status === 'abort') {
                            console.log('⏹️ Request aborted');
                            return;
                        }

                        console.error('❌ Error:', {
                            status: status,
                            error: error,
                            response: xhr.responseJSON
                        });

                        let errorMessage = "เกิดข้อผิดพลาดในการค้นหา";

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        alert(errorMessage);
                    },
                    complete: function() {
                        // เรียกเสมอไม่ว่าจะสำเร็จหรือไม่
                        isSearching = false;
                        currentRequest = null;
                        $("#loading").hide();
                        $("#tableContainer").show();
                    }
                });
            }

            // window.doSearch = doSearch;

            function updateURL(params) {
                const newUrl = new URL(window.location.href);

                newUrl.searchParams.delete("search");
                newUrl.searchParams.delete("date");
                newUrl.searchParams.delete("ward");
                newUrl.searchParams.delete("page");

                Object.keys(params).forEach((key) => {
                    if (params[key]) {
                        newUrl.searchParams.set(key, params[key]);
                    }
                });

                window.history.pushState({}, "", newUrl);
            }

            $(document).on("click", ".pagination a", function(e) {
                e.preventDefault();

                const url = $(this).attr("href");
                if (!url) return;

                const urlParams = new URLSearchParams(url.split("?")[1]);
                const page = Math.max(1, parseInt(urlParams.get("page")) || 1);

                console.log('📄 Go to page:', page);

                doSearch(page);

                $("html, body").animate({
                    scrollTop: $("#tableContainer").offset().top - 100,
                }, 300);
            });

            $('input[name="searchBy"]:checked').trigger("change");
        });
    </script>
    <script>
        let fpicker;

        $(document).ready(function() {
            // Initialize Flatpickr
            fpicker = flatpickr("#dateInput", {
                locale: "th",
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
                altInput: true,
                allowInput: true,
                monthSelectorType: 'static',
                yearSelectorType: 'static',
                disableMobile: true,
                maxDate: "today",
                theme: "material_blue",
                plugins: [],
                onChange: function(selectedDates, dateStr, instance) {
                    console.log("📅 Date selected:", dateStr);

                    const searchBy = $('input[name="searchBy"]:checked').attr("id");

                    if (searchBy === "all" && typeof window.doSearch === 'function') {
                        window.doSearch();
                    } else {
                        console.warn("doSearch not available or not in 'all' mode");
                    }
                }
            });

            // Initialize Select2
            $('#wardSelect').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'ค้นหาวอร์ด...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "ไม่พบวอร์ดที่ค้นหา";
                    }
                }
            });

            // จัดการการเปลี่ยนแปลงของ radio buttons
            document.querySelectorAll('input[name="searchBy"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const wardSelect = $('#wardSelect');
                    const dateInput = $('#dateInput');

                    if (this.id === 'ward') {
                        // แสดง ward select และซ่อน date input
                        wardSelect.parent().show();
                        wardSelect.removeClass('d-none');
                        dateInput.val(''); // รีเซ็ตค่าวันที่
                    } else {
                        // ซ่อน ward select
                        wardSelect.parent().hide();
                        wardSelect.addClass('d-none');
                        wardSelect.val(null).trigger('change'); // รีเซ็ตค่า ward
                    }
                });
            });

            // เรียกใช้ event change ครั้งแรกเพื่อตั้งค่าเริ่มต้น
            document.querySelector('input[name="searchBy"]:checked').dispatchEvent(new Event('change'));
        });

        // ปุ่มแชท
        document.getElementById('chatButton').addEventListener('click', function() {
            const box = document.getElementById('chatBox');
            box.style.display = (box.style.display === 'block') ? 'none' : 'block';
        });

        document.getElementById('closeChat').addEventListener('click', function() {
            document.getElementById('chatBox').style.display = 'none';
        });

        document.getElementById('sendBtn').addEventListener('click', async function() {
            const chatUser = document.getElementById('chatUser').value;
            const chatMessage = document.getElementById('chatMessage').value;

            if (!chatUser || !chatMessage.trim()) {
                alert('⚠️ กรุณาเลือกผู้รับและพิมพ์ข้อความ');
                return;
            }

            const res = await fetch('/telegram/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    chat_id: chatUser,
                    message: chatMessage
                })
            });

            const data = await res.json();
            if (data.status === 'ok') {
                alert('✅ ส่งข้อความสำเร็จ');
                document.getElementById('chatMessage').value = '';
            } else {
                alert('❌ ส่งไม่สำเร็จ: ' + (data.message || 'ไม่ทราบสาเหตุ'));
            }
        });
    </script>
@endpush
