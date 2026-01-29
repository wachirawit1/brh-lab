<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200" id="userTable">
        <thead class="bg-gray-800 text-white">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">HN</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ชื่อ-นามสกุล
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">วอร์ด</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">After Ward
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ชื่อ Lab
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Req Date
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($patients as $patient)
                <tr data-hn="{{ $patient->hn }}" data-name="{{ $patient->name }}"
                    class="cursor-pointer hover:bg-brand-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $patient->hn }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $patient->name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $patient->ward_name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{!! $patient->after_ward ?? '-' !!}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-brand-600 font-medium">
                        {{ $patient->lab_name ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \App\Helpers\DateHelper::formatThaiDate($patient->req_date, 'full') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fa-solid fa-search text-4xl mb-3 text-gray-300"></i>
                            <h5 class="text-lg font-medium text-gray-600">ไม่พบข้อมูล</h5>
                            <p class="text-sm text-gray-400">ไม่พบผู้ป่วยที่ตรงกับการค้นหา</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if ($patients->total() > 0)
    <div class="flex flex-col sm:flex-row justify-between items-center mt-4 px-2">
        <div class="text-sm text-gray-500 mb-2 sm:mb-0">
            แสดง <span class="font-medium">{{ $patients->firstItem() }}</span> ถึง <span
                class="font-medium">{{ $patients->lastItem() }}</span>
            จาก <span class="font-medium">{{ $patients->total() }}</span> รายการ
        </div>

        <div id="paginationContainer" class="flex justify-center">
            <!-- Ensure we use Tailwind Pagination View if available, or fallback to default -->
            <!-- If Laravel defaults to Tailwind (v8+), this is fine. If Bootstrap, classes might be ignored but structure works. -->
            {{ $patients->links() }}
        </div>

        <div class="text-xs text-gray-400 mt-2 sm:mt-0">
            หน้า {{ $patients->currentPage() }} / {{ $patients->lastPage() }}
        </div>
    </div>
@endif
