<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmrController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => mb_substr(trim((string) $request->query('search', '')), 0, 100),
            'admit_date' => $this->normalizeDate($request->query('admit_date')),
            'ward' => trim((string) $request->query('ward', '')),
            'm' => $this->normalizeStatus($request->query('m')),
            'rm' => $this->normalizeStatus($request->query('rm')),
        ];

        try {
            $query = DB::connection('sqlsrv')
                ->table('dbo.View_AMR as amr')
                ->select([
                    'amr.hn',
                    'amr.regist_flag',
                    'amr.ladmit_n',
                    'amr.admit_date',
                    'amr.name',
                    'amr.sex',
                    'amr.age',
                    'amr.Weight',
                    'amr.Height',
                    'amr.ward_id',
                    'amr.ward_name',
                    'amr.M',
                    'amr.RM',
                    'amr.cr',
                    'amr.egfr',
                    'amr.CBC',
                    'amr.LFT',
                ]);

            if ($filters['search'] !== '') {
                $search = '%'.$filters['search'].'%';
                $query->where(function ($query) use ($search) {
                    $query->where('amr.hn', 'like', $search)
                        ->orWhere('amr.ladmit_n', 'like', $search)
                        ->orWhere('amr.name', 'like', $search);
                });
            }

            if ($filters['admit_date'] !== '') {
                [$year, $month, $day] = explode('-', $filters['admit_date']);
                $query->where('amr.admit_date', ((int) $year + 543).$month.$day);
            }

            if ($filters['ward'] !== '') {
                $query->where('amr.ward_id', $filters['ward']);
            }

            if ($filters['m'] !== null) {
                $query->where('amr.M', $filters['m']);
            }

            if ($filters['rm'] !== null) {
                $query->where('amr.RM', $filters['rm']);
            }

            $patients = $query
                ->orderByDesc('amr.admit_date')
                ->orderBy('amr.ward_id')
                ->orderBy('amr.hn')
                ->orderBy('amr.ladmit_n')
                ->orderBy('amr.regist_flag')
                ->paginate(50)
                ->withQueryString();

            $wards = DB::connection('sqlsrv')
                ->table('dbo.View_AMR')
                ->select('ward_id', 'ward_name')
                ->whereNotNull('ward_id')
                ->distinct()
                ->orderBy('ward_id')
                ->get();

            // Load saved patient organisms for current page with robust whitespace trimming
            $hns = $patients->map(function ($patient) {
                return trim((string) $patient->hn);
            })->filter()->unique()->values()->all();

            $rawOrganisms = \App\Models\PatientAmrOrganism::with('selectedOrganisms')->whereIn('hn', $hns)->get();

            $patientOrganisms = collect();
            foreach ($rawOrganisms as $org) {
                $cleanHn = trim((string) $org->hn);
                $cleanReg = trim((string) ($org->regist_flag ?? ''));
                if ($cleanReg !== '') {
                    $patientOrganisms->put($cleanHn.'_'.$cleanReg, $org);
                }
                // Also store key by HN alone as fallback
                if (! $patientOrganisms->has($cleanHn)) {
                    $patientOrganisms->put($cleanHn, $org);
                }
            }

            $masterOrganisms = \App\Models\AmrOrganismMaster::where('is_active', true)->orderBy('sort_order')->get();

            return view('amr.index', compact('patients', 'wards', 'filters', 'patientOrganisms', 'masterOrganisms'));
        } catch (\Throwable $exception) {
            Log::error('Unable to load AMR patient list.', [
                'exception' => $exception,
                'filters' => $filters,
            ]);

            $patients = new LengthAwarePaginator(
                [],
                0,
                50,
                max(1, (int) $request->query('page', 1)),
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $wards = collect();
            $patientOrganisms = collect();
            $masterOrganisms = collect();
            $loadError = 'ไม่สามารถโหลดข้อมูล AMR ได้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ';

            return view('amr.index', compact('patients', 'wards', 'filters', 'patientOrganisms', 'masterOrganisms', 'loadError'));
        }
    }

    private function normalizeStatus(mixed $status): ?string
    {
        $status = strtoupper(trim((string) $status));

        return in_array($status, ['Y', 'N'], true) ? $status : null;
    }

    private function normalizeDate(mixed $date): string
    {
        $date = trim((string) $date);

        if (! preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/', $date, $matches)) {
            return '';
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]) ? $date : '';
    }

    public function getOrganisms(Request $request, $hn)
    {
        try {
            $trimmedHn = trim((string) $hn);
            $regNo = $request->query('reg_no');

            $query = \App\Models\PatientAmrOrganism::where('hn', $trimmedHn);
            if ($regNo) {
                $query->where('regist_flag', $regNo);
            }

            $record = $query->with('selectedOrganisms')->latest()->first();
            $masterOrganisms = \App\Models\AmrOrganismMaster::active()->get();

            return response()->json([
                'status' => 'success',
                'data' => array_merge($record ? [
                    'organisms' => $record->selectedOrganisms->pluck('code')->values()->all(),

                    'updated_at' => $record->updated_at ? \App\Helpers\DateHelper::formatThaiDate($record->updated_at->format('Y-m-d'), 'short').' '.$record->updated_at->format('H:i').' น.' : null,
                    'created_by' => $record->created_by ?: 'ไม่ระบุชื่อ',
                ] : [
                    'organisms' => [],
                    'updated_at' => null,
                    'created_by' => null,
                ], [
                    'master_organisms' => $masterOrganisms->map->only(['code', 'name', 'full_name', 'description', 'severity'])->values(),
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Get Organisms Error: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลเชื้อ',
            ], 500);
        }
    }

    public function storeOrganisms(Request $request)
    {
        $validated = $request->validate([
            'hn' => 'required|string|max:20',
            'regist_flag' => 'nullable|string|max:20',
            'ward_id' => 'nullable|string|max:20',
            'organisms' => 'nullable|array|max:50',
            'organisms.*' => 'string|max:50|distinct',
        ]);

        try {
            $hn = trim((string) $validated['hn']);
            $registFlag = $validated['regist_flag'] ?? null;
            $wardId = $validated['ward_id'] ?? null;
            $selectedCodes = collect($validated['organisms'] ?? [])
                ->map(fn ($code) => strtolower(trim((string) $code)))
                ->filter()
                ->unique()
                ->values();
            $activeOrganisms = \App\Models\AmrOrganismMaster::active()->get();
            $selectedOrganisms = $activeOrganisms->whereIn('code', $selectedCodes);

            if ($selectedOrganisms->count() !== $selectedCodes->count()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'มีรายการเชื้อที่ไม่ได้เปิดใช้งานหรือไม่ถูกต้อง',
                ], 422);
            }

            $userName = session('user.fullname')
                ? trim((string) session('user.fullname'))
                : (session('user.username') ?? 'เจ้าหน้าที่');

            $data = [
                'ward_id' => $wardId,

                'created_by' => $userName,
            ];

            $record = DB::transaction(function () use ($hn, $registFlag, $data, $selectedOrganisms) {
                $record = \App\Models\PatientAmrOrganism::updateOrCreate(
                    ['hn' => $hn, 'regist_flag' => $registFlag],
                    $data
                );

                $record->selectedOrganisms()->sync($selectedOrganisms->pluck('id'));

                return $record->load('selectedOrganisms');
            });

            return response()->json([
                'status' => 'success',
                'message' => "บันทึกข้อมูลเชื้อเรียบร้อยแล้วสำหรับ HN: {$hn}",
                'data' => $record,
            ]);
        } catch (\Exception $e) {
            Log::error('Store Organisms Error: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูลเชื้อ',
            ], 500);
        }
    }

    /**
     * รายการ Master ของเชื้อดื้อยา AMR ทั้งหมด
     */
    public function getMasterOrganisms()
    {
        try {
            $organisms = \App\Models\AmrOrganismMaster::orderBy('sort_order')->orderBy('id')->get();

            return response()->json([
                'status' => 'success',
                'data' => $organisms,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Master Organisms Error: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to load master organisms'], 500);
        }
    }

    /**
     * เพิ่มหรือแก้ไข Master เชื้อดื้อยา
     */
    public function storeMasterOrganism(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'full_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'severity' => 'required|string|in:critical,high,medium,info',
            'color' => 'nullable|string|max:30',
        ]);

        try {
            $code = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9_]/', '', $validated['code'])));
            $name = trim((string) $validated['name']);
            $fullName = trim((string) ($validated['full_name'] ?? ''));
            $severity = $validated['severity'];
            $color = $validated['color'] ?: match ($severity) {
                'critical' => '#dc2626',
                'high' => '#ea580c',
                'medium' => '#d97706',
                default => '#0284c7',
            };

            $userName = session('user.fullname') ?: (session('user.username') ?? 'Admin');

            $data = [
                'code' => $code,
                'name' => $name,
                'full_name' => $fullName,
                'description' => trim((string) ($validated['description'] ?? '')),
                'severity' => $severity,
                'color' => $color,
                'created_by' => $userName,
            ];

            if (! empty($validated['id'])) {
                $organism = \App\Models\AmrOrganismMaster::findOrFail($validated['id']);
                $organism->update($data);
            } else {
                $maxSort = \App\Models\AmrOrganismMaster::max('sort_order') ?? 0;
                $data['sort_order'] = $maxSort + 1;
                $data['is_active'] = true;
                $organism = \App\Models\AmrOrganismMaster::create($data);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'บันทึกข้อมูลเชื้อดื้อยาสำเร็จ',
                'data' => $organism,
            ]);
        } catch (\Exception $e) {
            Log::error('Store Master Organism Error: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึก'], 500);
        }
    }

    /**
     * สลับสถานะเปิด/ปิด เชื้อดื้อยา
     */
    public function toggleMasterOrganism($id)
    {
        try {
            $organism = \App\Models\AmrOrganismMaster::findOrFail($id);
            $isActivating = ! $organism->is_active;
            $organism->is_active = $isActivating;

            if ($isActivating) {
                $organism->sort_order = (\App\Models\AmrOrganismMaster::where('is_active', true)->max('sort_order') ?? 0) + 1;
            }

            $organism->save();

            return response()->json([
                'status' => 'success',
                'message' => 'อัปเดตสถานะสำเร็จ',
                'is_active' => $organism->is_active,
            ]);
        } catch (\Exception $e) {
            Log::error('Toggle Master Organism Error: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to toggle organism'], 500);
        }
    }

    /**
     * บันทึกลำดับการแสดงผลของเชื้อดื้อยาที่เปิดใช้งาน
     */
    public function reorderMasterOrganisms(Request $request)
    {
        $validated = $request->validate([
            'organism_ids' => ['required', 'array', 'min:1'],
            'organism_ids.*' => ['required', 'integer', 'distinct', 'exists:amr_organisms_master,id'],
        ]);

        $submittedIds = array_map('intval', $validated['organism_ids']);
        $activeIds = \App\Models\AmrOrganismMaster::where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $submittedSet = $submittedIds;
        $activeSet = $activeIds;
        sort($submittedSet);
        sort($activeSet);

        if ($submittedSet !== $activeSet) {
            return response()->json([
                'status' => 'error',
                'message' => 'รายการเชื้อมีการเปลี่ยนแปลง กรุณาโหลดข้อมูลใหม่แล้วลองอีกครั้ง',
            ], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($submittedIds) {
            foreach ($submittedIds as $index => $id) {
                \App\Models\AmrOrganismMaster::whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'บันทึกลำดับเรียบร้อยแล้ว',
        ]);
    }

    /**
     * ดึงประวัติการบันทึกเชื้อดื้อยา AMR (Audit Logs)
     */
    public function getAuditLogs()
    {
        try {
            $logs = \App\Models\PatientAmrOrganism::with('selectedOrganisms')->latest('updated_at')
                ->take(30)
                ->get()
                ->map(function ($item) {
                    $positives = $item->selectedOrganisms->pluck('name')->values()->all();

                    return [
                        'id' => $item->id,
                        'hn' => $item->hn,
                        'regist_flag' => $item->regist_flag,
                        'ward_id' => $item->ward_id,
                        'organisms' => $positives,
                        'created_by' => $item->created_by ?: 'ไม่ระบุ',
                        'updated_at' => $item->updated_at ? \App\Helpers\DateHelper::formatThaiDate($item->updated_at->format('Y-m-d'), 'short').' '.$item->updated_at->format('H:i น.') : '-',
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Audit Logs Error: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to load audit logs'], 500);
        }
    }
}
