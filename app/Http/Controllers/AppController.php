<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Helpers\DateHelper;
use Carbon\Carbon;

class AppController extends Controller
{
    public function index(Request $request)
    {
        try {
            $page = (int) $request->get('page', 1);
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            $start_date = (Carbon::now()->year + 543) . Carbon::now()->format('md');
            $end_date = (Carbon::now()->subDays(7)->year + 543) . Carbon::now()->subDays(7)->format('md');

            // Base conditions
            $whereConditions = [];
            $bindings = [];
            $countBindings = [];

            // เงื่อนไขวันที่
            if ($request->has('date') && !empty(trim($request->date))) {
                $date = trim($request->date);

                // แปลงวันที่ให้ถูกต้อง
                try {
                    $carbonDate = Carbon::parse($date);
                    $buddhistYear = $carbonDate->year + 543;
                    $formattedDate = $buddhistYear . $carbonDate->format('md');

                    // ใช้ admit_date แทน req_date เพื่อให้สอดคล้องกับ SQL
                    $whereConditions[] = "i.admit_date = ?";
                    $bindings[] = $formattedDate;
                    $countBindings[] = $formattedDate;

                    Log::info("Date filter applied: {$date} -> {$formattedDate}");
                } catch (\Exception $e) {
                    Log::error("Date parsing error: " . $e->getMessage());
                    // ถ้าแปลงวันที่ไม่ได้ ให้ใช้ช่วงวันที่แทน
                    $whereConditions[] = "i.admit_date BETWEEN ? AND ?";
                    $bindings[] = $end_date;
                    $bindings[] = $start_date;
                    $countBindings[] = $end_date;
                    $countBindings[] = $start_date;
                }
            } else {
                $whereConditions[] = "i.admit_date BETWEEN ? AND ?";
                $bindings[] = $end_date;
                $bindings[] = $start_date;
                $countBindings[] = $end_date;
                $countBindings[] = $start_date;
            }

            // เงื่อนไขการค้นหา
            if ($request->has('search') && !empty(trim($request->search))) {
                $searchTerm = trim($request->search);
                $whereConditions[] = "(i.hn LIKE ? OR CONCAT(ISNULL(pt.firstName,''), ' ', ISNULL(pt.lastName,'')) LIKE ?)";
                $bindings[] = '%' . $searchTerm . '%';
                $bindings[] = '%' . $searchTerm . '%';
                $countBindings[] = '%' . $searchTerm . '%';
                $countBindings[] = '%' . $searchTerm . '%';
            }

            // เงื่อนไขวอร์ด
            if ($request->has('ward') && !empty(trim($request->ward))) {
                $wardId = trim($request->ward);
                $whereConditions[] = "i.ward_id = ?";
                $bindings[] = $wardId;
                $countBindings[] = $wardId;
            }

            // สร้าง WHERE clause
            $whereClause = "WHERE ld.lab_type = 'MB' AND lh.lconfirm = 'Y'";
            if (!empty($whereConditions)) {
                $whereClause .= " AND " . implode(" AND ", $whereConditions);
            }

            // Base FROM clause
            $baseQuery = "
                FROM dbo.Ipd_h i WITH (NOLOCK)
                LEFT JOIN dbo.PATIENT pt WITH (NOLOCK) ON pt.hn = i.hn
                LEFT JOIN dbo.PTITLE t WITH (NOLOCK) ON t.titleCode = pt.titleCode
                LEFT JOIN dbo.Labreq_h lh WITH (NOLOCK) ON lh.hn = i.hn AND lh.reg_flag = i.regist_flag
                LEFT JOIN dbo.Labreq_d ld WITH (NOLOCK) ON ld.req_no = lh.req_no
                LEFT JOIN dbo.Ward w WITH (NOLOCK) ON w.ward_id = i.ward_id
                {$whereClause}
            ";

            // นับจำนวนทั้งหมด - ใช้ COUNT(*) ง่ายๆ
            $countSql = "SELECT COUNT(*) as total " . $baseQuery;

            Log::info('Count SQL: ' . $countSql);
            Log::info('Count Bindings: ' . json_encode($countBindings));

            $countResult = DB::connection('sqlsrv')->select($countSql, $countBindings);
            $totalCount = $countResult[0]->total ?? 0;

             // Query หลัก
            $afterWardSubquery = "
                STUFF((
                    SELECT '<--' + RTRIM(w2.ward_name)
                    FROM dbo.Resident rs WITH (NOLOCK)
                    LEFT JOIN dbo.Ward w2 WITH (NOLOCK) ON w2.ward_id = rs.ward_id
                    WHERE rs.hn = i.hn AND rs.regist_flag = i.regist_flag
                        AND rs.res_backup_stat <> 'R'
                    ORDER BY rs.check_in_date DESC, rs.check_in_time DESC
                    FOR XML PATH('')
                ), 1, 0, '')
                ";

            $sql = "
                SELECT
                    i.hn,
                    i.regist_flag,
                    i.ladmit_n,
                    RTRIM(ISNULL(t.titleName,'')) + RTRIM(ISNULL(pt.firstName,'')) + '  ' + RTRIM(ISNULL(pt.lastName,'')) as name,
                    i.ward_id,
                    RTRIM(ISNULL(w.ward_name,'')) as ward_name,
                    {$afterWardSubquery} as after_ward,
                    lh.req_date,
                    lh.req_no,
                    lh.res_ok,
                    ld.lab_code,
                    ld.lab_name,
                    ld.res_ready
                " . $baseQuery . "
                ORDER BY lh.req_date DESC, i.hn
                OFFSET ? ROWS
                FETCH NEXT ? ROWS ONLY
            ";

            // เพิ่ม offset และ limit
            $dataBindings = array_merge($bindings, [$offset, $perPage]);

            Log::info('Main SQL: ' . $sql);
            Log::info('Main Bindings: ' . json_encode($dataBindings));

            $results = DB::connection('sqlsrv')->select($sql, $dataBindings);

            // สร้าง Paginator
            $patients = new \Illuminate\Pagination\LengthAwarePaginator(
                $results,
                $totalCount,
                $perPage,
                $page,
                ['path' => $request->url()]
            );

            $patients->appends($request->except('page'));

            // ข้อมูล Telegram
            $telegram_data = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('pm', session('user.username'))
                ->select('is_active', 'allowed')
                ->first();

            $telegram_status  = data_get($telegram_data, 'is_active', 0);
            $telegram_allowed = data_get($telegram_data, 'allowed', 0);

            // ข้อมูลวอร์ด - เพิ่ม cache
            $wards = cache()->remember('wards_list', 3600, function () {
                return DB::connection('sqlsrv')
                    ->table('Ward')
                    ->select('ward_id', 'ward_name')
                    ->orderBy('ward_id')
                    ->get();
            });

            $users = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('is_active', 1)
                ->where('allowed', 1)
                ->get();

            if ($request->ajax()) {
                $tableHtml = view('patients.table', compact('patients'))->render();
                $paginationHtml = $patients->links()->render();

                return response()->json([
                    'html' => $tableHtml,
                    'pagination' => $paginationHtml,
                    'status' => 'success'
                ]);
            }

            $bot_username = 'brh_test_bot';
            $qr = QrCode::size(300)->generate('https://t.me/' . $bot_username . '?start=' . session('user.username'));

            return view('index', compact('patients', 'telegram_status', 'telegram_allowed', 'qr', 'wards', 'users'));
        } catch (\Exception $e) {
            Log::error('Search Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'เกิดข้อผิดพลาดในการค้นหา',
                    'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
                ], 500);
            }

            return view('index')->with('error', 'เกิดข้อผิดพลาดในการค้นหา');
        }
    }

    public function getLabResults($hn)
    {
        try {
            $labResults = DB::connection('sqlsrv')
                ->table('Labres_m')
                ->select('res_date', 'resText')
                ->where('hn', $hn)
                ->orderBy('res_date', 'desc')
                ->limit(10) // จำกัดผลลัพธ์
                ->get()
                ->map(function ($lab) {
                    return [
                        'res_date' => DateHelper::formatThaiDateTime($lab->res_date, 'full'),
                        'resText' => $lab->resText,

                    ];
                });

            return response()->json([
                'labResults' => $labResults,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Lab Results Error: ' . $e->getMessage());

            return response()->json([
                'labResults' => [],
                'error' => 'เกิดข้อผิดพลาดในการดึงผลแลป',
                'status' => 'error'
            ], 500);
        }
    }
}
