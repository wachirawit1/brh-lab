<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\NotifyLabResults;

// Schedule::call(function () {
//     try {
//         // ตรวจสอบว่ามีการแจ้งเตือนวันนี้ไปแล้วหรือยัง
//         $notified = DB::connection('mysql')
//             ->table('notifications_log')
//             ->whereDate('created_at', Carbon::now())
//             ->exists();

//         if ($notified) {
//             Log::info("วันนี้มีการแจ้งเตือนผลแล็บไปแล้ว");
//             return;
//         }

//         // ดึงผลแล็บวันนี้
//         $labres = DB::connection('sqlsrv')
//             ->table('Labres_m')
//             ->whereDate('res_date', Carbon::now())
//             ->get();

//         if ($labres->isEmpty()) {
//             Log::info("ไม่พบผลแล็บใหม่วันนี้");
//             return;
//         }

//         // ดึงรายชื่อผู้รับการแจ้งเตือน
//         $subscribers = DB::connection('mysql')
//             ->table('telegram_subscribers')
//             ->where('is_active', 1)
//             ->get();

//         if ($subscribers->isEmpty()) {
//             Log::info("ไม่พบผู้รับการแจ้งเตือนที่เปิดใช้งาน");
//             return;
//         }

//         // สร้างข้อความแจ้งเตือน
//         $hns = $labres->pluck('hn')->unique()->implode(", ");
//         $message = "📢 แจ้งเตือน\n" .
//             "ผลแล็บประจำวันที่ " . Carbon::now()->format('d/m/Y') . "\n" .
//             "มี HN ดังนี้:\n" .
//             $hns;

//         // ส่งข้อความแจ้งเตือน
//         $successCount = 0;
//         foreach ($subscribers as $subscriber) {
//             try {
//                 TelegramHelper::sendMessage($subscriber->chat_id, $message);
//                 $successCount++;
//                 Log::info("ส่งข้อความสำเร็จ - Chat ID: {$subscriber->chat_id}");
//             } catch (\Exception $e) {
//                 Log::error("ไม่สามารถส่งข้อความ - Chat ID: {$subscriber->chat_id}", [
//                     'error' => $e->getMessage()
//                 ]);
//             }
//         }

//         // บันทึกการแจ้งเตือนหลังจากส่งสำเร็จอย่างน้อย 1 คน
//         if ($successCount > 0) {
//             DB::connection('mysql')
//                 ->table('notifications_log')
//                 ->insert([
//                     'date' => Carbon::now()->toDateString(),
//                     'success_count' => $successCount,
//                     'total_subscribers' => $subscribers->count(),
//                     'created_at' => Carbon::now(),
//                     'updated_at' => Carbon::now(),
//                     'status' => 'แจ้งเตือนสำเร็จ'
//                 ]);
//         }
//     } catch (\Exception $e) {
//         Log::error("เกิดข้อผิดพลาดในการแจ้งเตือนผลแล็บ: " . $e->getMessage());
//     }
// })->daily('08:00')->name('notify_lab_results');

// Schedule::call(function () {
//     NotifyLabResults::dispatch();
// })->everyFiveMinutes()->name('notify_lab_results');

Schedule::job(new NotifyLabResults)
    ->everyMinute()
    // ->withoutOverlapping()
    // ->onFailure(function ($e) {
    //     Log::error('Notification job failed: ' . $e->getMessage());
    // })
    ->name('notify_lab_results');

// ->withoutOverlapping();
