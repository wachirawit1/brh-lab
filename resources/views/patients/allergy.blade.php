@extends('layout.app')
@section('title', 'บันทึกข้อมูลการแพ้ยา')
@section('content')
    <div class="max-w-2xl mx-auto py-8 px-4">
        <!-- Back Button & Title -->
        <div class="mb-6">
            <a href="{{ route('index') }}" class="inline-flex items-center text-sm font-medium text-brand-600 hover:text-brand-700 transition gap-1.5 mb-2">
                <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
            </a>
            <h4 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-pills text-brand-600"></i>
                บันทึกข้อมูลการแพ้ยา
            </h4>
            <p class="text-sm text-gray-500 mt-1">กรอกข้อมูลประวัติการแพ้ยาของคนไข้ในระบบ</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-brand-600 px-6 py-4 text-white">
                <h5 class="font-medium">ฟอร์มบันทึกการแพ้ยา</h5>
            </div>
            
            <form action="{{ route('patients.allergy.store', $hn) }}" method="POST" class="p-6 space-y-6">
                @csrf
                <!-- Patient Info Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">HN คนไข้</label>
                        <p class="text-base font-bold text-brand-700 mt-1">{{ $hn }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">ชื่อ-นามสกุล</label>
                        <p class="text-base font-bold text-gray-800 mt-1">{{ $name }}</p>
                    </div>
                </div>

                <!-- Drug Name -->
                <div>
                    <label for="drugName" class="block text-sm font-medium text-gray-700 mb-2">
                        ชื่อยาที่แพ้ <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="text" id="drugName" name="drug_name" required
                            class="block w-full text-sm border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 p-2.5 border transition"
                            placeholder="ระบุชื่อยา เช่น Amoxicillin, Penicillin...">
                    </div>
                </div>

                <!-- Symptoms -->
                <div>
                    <label for="allergySymptoms" class="block text-sm font-medium text-gray-700 mb-2">
                        อาการที่แพ้
                    </label>
                    <textarea id="allergySymptoms" name="symptoms" rows="4"
                        class="block w-full text-sm border-gray-300 rounded-lg focus:ring-brand-500 focus:ring-brand-500 p-2.5 border transition"
                        placeholder="ระบุอาการแพ้ เช่น ผื่นคันตามร่างกาย, หายใจติดขัด, บวมบริเวณใบหน้า..."></textarea>
                </div>

                <!-- Severity Level -->
                <div>
                    <label for="allergySeverity" class="block text-sm font-medium text-gray-700 mb-2">
                        ระดับความรุนแรง
                    </label>
                    <select id="allergySeverity" name="severity"
                        class="block w-full text-sm border-gray-300 rounded-lg focus:ring-brand-500 focus:ring-brand-500 p-2.5 border transition">
                        <option value="low">น้อย (Mild)</option>
                        <option value="moderate" selected>ปานกลาง (Moderate)</option>
                        <option value="severe">รุนแรง (Severe / Anaphylaxis)</option>
                    </select>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('index') }}" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                        ยกเลิก
                    </a>
                    <button type="submit"
                        class="px-5 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none transition">
                        บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
