<!-- Table -->
<div class="table-responsive">
    <table class="table table-hover" id="userTable">
        <thead class="table-dark">
            <tr>
                <th>HN</th>
                <th>ชื่อ-นามสกุล</th>
                <th>วอร์ด</th>
                <th>after ward</th>
                <th>ชื่อ Lab</th>
                <th>req_date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patients as $patient)
                <tr data-hn="{{ $patient->hn }}" data-name="{{ $patient->name }}" class="cursor-pointer">
                    <td>{{ $patient->hn }}</td>
                    <td>{{ $patient->name ?? '-' }}</td>
                    <td>{{ $patient->ward_name ?? '-' }}</td>
                    <td>{!! $patient->after_ward ?? '-' !!}</td>
                    <td>{{ $patient->lab_name ?? '-' }}</td>
                    <td>{{ \App\Helpers\DateHelper::formatThaiDate($patient->req_date, 'full') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fa-solid fa-search fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">ไม่พบข้อมูล</h5>
                            <p class="text-muted">ไม่พบผู้ป่วยที่ตรงกับการค้นหา</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- แสดงจำนวนรายการ --}}
@if ($patients->total() > 0)
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            แสดง {{ $patients->firstItem() }} - {{ $patients->lastItem() }}
            จากทั้งหมด {{ $patients->total() }} รายการ
        </div>
        {{-- Pagination Container --}}
        <div id="paginationContainer" class="d-flex justify-content-center mt-3">
            {{ $patients->links() }}
        </div>
        <div class="text-muted">
            หน้า {{ $patients->currentPage() }} จาก {{ $patients->lastPage() }}
        </div>
    </div>
@endif
