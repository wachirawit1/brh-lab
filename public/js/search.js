// // search.js
// $(document).ready(function () {
//     let searchTimeout;

//     // ค้นหาแบบ Real-time
//     $("#search").on("input", function () {
//         clearTimeout(searchTimeout);
//         const searchTerm = $(this).val();

//         searchTimeout = setTimeout(function () {
//             doSearch(searchTerm);
//         }, 300);
//     });

//     $("#dateInput").on("change input", function () {
//         console.log("Date input changed:", $(this).val()); // Debug line
//         const searchBy = $('input[name="searchBy"]:checked').attr("id");
//         console.log("Search by:", searchBy); // Debug line

//         if (searchBy === "all") {
//             doSearch();
//         }
//     });

//     // ฟังก์ชันล้างค่าในช่อง dateInput
//     $("[data-clear]").on("click", function () {
//         console.log("Clear date input"); // Debug line
//         $("#dateInput").val("");

//         if (typeof fpickr !== "undefined" && fpickr) {
//             fpickr.clear();
//         }
//         if ($('input[name="searchBy"]:checked').attr("id") === "all") {
//             doSearch();
//         }
//     });

//     // ward select
//     $("#wardSelect").on("change", function () {
//         console.log("Ward select changed:", $(this).val()); // Debug line
//         if ($('input[name="searchBy"]:checked').attr("id") === "ward") {
//             doSearch();
//         }
//     });

//     // event listener สำหรับ radio buttons
//     $('input[name="searchBy"]').on("change", function () {
//         const selectedMode = $(this).attr("id");
//         console.log("Search mode changed to:", selectedMode); // Debug line

//         if (selectedMode === "ward") {
//             // แสดง ward select และซ่อน date input
//             $("#wardSelect").parent().show();
//             $("#wardSelect").removeClass("d-none");
//             $("#dateInput").parent().parent().hide();

//             // เคลียร์ค่าใน date input
//             $("#dateInput").val("");
//             if (typeof fpickr !== "undefined") {
//                 fpickr.clear();
//             }
//         } else {
//             // แสดง date input และซ่อน ward select
//             $("#dateInput").parent().parent().show();
//             $("#wardSelect").parent().hide();
//             $("#wardSelect").addClass("d-none");
//             $("#wardSelect").val(null).trigger("change");
//         }

//         // เรียกค้นหาใหม่
//         doSearch();
//     });

//     function doSearch(page = 1) {
//         const searchTerm = $("#search").val();
//         const dateValue = $("#dateInput").val();
//         const wardValue = $("#wardSelect").val();
//         const searchBy = $('input[name="searchBy"]:checked').attr("id");

//         $("#loading").show();
//         $("#tableContainer").hide();

//         // สร้าง data object
//         let requestData = {
//             page: page,
//         };

//         // เพิ่ม search term ถ้ามี
//         if (searchTerm) {
//             requestData.search = searchTerm;
//         }

//         // เพิ่ม date ถ้าเลือกโหมด "ทั้งหมด" และมีการเลือกวันที่
//         if (searchBy === "all" && dateValue) {
//             requestData.date = dateValue;
//         }

//         // เพิ่ม ward ถ้าเลือกโหมด "วอร์ด" และมีการเลือกวอร์ด
//         if (searchBy === "ward" && wardValue) {
//             requestData.ward = wardValue;
//         }

//         $.ajax({
//             url: window.location.pathname,
//             type: "GET",
//             data: requestData,
//             headers: {
//                 "X-Requested-With": "XMLHttpRequest",
//             },
//             success: function (response) {
//                 // ตรวจสอบว่า response มีข้อมูลที่ต้องการ
//                 if (response.html) {
//                     $("#tableContainer").html(response.html);
//                 }

//                 // ตรวจสอบและอัพเดท pagination
//                 if (response.pagination) {
//                     $("#paginationContainer").html(response.pagination);
//                 } else {
//                     // ถ้าไม่มี pagination มา ให้ซ่อน container
//                     $("#paginationContainer").empty();
//                 }

//                 // เรียกใช้ฟังก์ชันเดิมที่มีอยู่
//                 if (typeof addContextMenuToRows === "function") {
//                     addContextMenuToRows();
//                 }

//                 $("#loading").hide();
//                 $("#tableContainer").show();

//                 // อัพเดท URL โดยไม่ reload หน้า
//                 updateURL(requestData);
//             },
//             error: function (xhr) {
//                 console.error("Search error:", xhr);

//                 let errorMessage = "เกิดข้อผิดพลาดในการค้นหา";
//                 if (xhr.responseJSON && xhr.responseJSON.message) {
//                     errorMessage = xhr.responseJSON.message;
//                 }

//                 alert(errorMessage);
//                 $("#loading").hide();
//                 $("#tableContainer").show();
//             },
//         });
//     }

//     // ฟังก์ชันอัพเดท URL
//     function updateURL(params) {
//         const newUrl = new URL(window.location.href);

//         // ลบ parameters ทั้งหมดก่อน
//         newUrl.searchParams.delete("search");
//         newUrl.searchParams.delete("date");
//         newUrl.searchParams.delete("ward");
//         newUrl.searchParams.delete("page");

//         // เพิ่ม parameters ใหม่
//         Object.keys(params).forEach((key) => {
//             if (params[key]) {
//                 newUrl.searchParams.set(key, params[key]);
//             }
//         });

//         window.history.pushState({}, "", newUrl);
//     }

//     // Pagination แบบ AJAX
//     $(document).on("click", ".pagination a", function (e) {
//         e.preventDefault();

//         const url = $(this).attr("href");

//         // ดึงหมายเลขหน้าจาก URL
//         const urlParams = new URLSearchParams(url.split("?")[1]);
//         const page = urlParams.get("page") || 1;

//         // เรียกใช้ doSearch พร้อมส่งหมายเลขหน้า
//         doSearch(page);

//         // เลื่อนขึ้นไปด้านบนของตาราง
//         $("html, body").animate(
//             {
//                 scrollTop: $("#tableContainer").offset().top - 100,
//             },
//             300
//         );
//     });

//     // ตั้งค่าเริ่มต้นให้ซ่อน ward select
//     $('input[name="searchBy"]:checked').trigger("change");
// });
