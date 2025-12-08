<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifyLabResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            $today = Carbon::today();

            // หาว่ามี log ของวันนี้หรือยัง
            $log = DB::connection('mysql')
                ->table('notifications_log')
                ->whereDate('date', $today)
                ->first();

            $lastNotified = $log->last_notified_at ?? $today->startOfDay();

            // ดึงเฉพาะผลแล็บที่มาใหม่หลังจาก last_notified_at
            $labres = DB::connection('sqlsrv')
                ->table('Labres_m')
                ->where('res_date', '>', $lastNotified)
                ->orderBy('res_date')
                ->get();

            if ($labres->isEmpty()) {
                Log::info("ไม่พบผลแล็บใหม่ตั้งแต่ " . $lastNotified);
                return;
            }

            $subscribers = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('is_active', 1)
                ->where('allowed', 1)
                ->get();

            if ($subscribers->isEmpty()) {
                Log::info("ไม่พบผู้รับการแจ้งเตือนที่เปิดใช้งาน");
                return;
            }

            // สร้างข้อความ
            $hns = $labres->pluck('hn')->unique()->implode(", ");
            $message = "📢 แจ้งเตือน\n" .
                "ผลแล็บใหม่ (" . Carbon::now()->format('d/m/Y H:i') . ")\n" .
                "HN: " . $hns;

            $successCount = 0;
            foreach ($subscribers as $subscriber) {
                try {
                    TelegramHelper::sendMessage($subscriber->chat_id, $message);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("ไม่สามารถส่งข้อความ - Chat ID: {$subscriber->chat_id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if ($successCount > 0) {
                $lastResDate = $labres->max('res_date');

                if ($log) {
                    // อัปเดต record เดิมของวันนี้
                    DB::connection('mysql')
                        ->table('notifications_log')
                        ->where('id', $log->id)
                        ->update([
                            'last_notified_at'  => $lastResDate,
                            'success_count'     => DB::raw('success_count + ' . $successCount),
                            'updated_at'        => Carbon::now(),
                            'status'            => 'แจ้งเตือนเพิ่ม'
                        ]);
                } else {
                    // ถ้ายังไม่มี record วันนี้ → insert ใหม่
                    DB::connection('mysql')
                        ->table('notifications_log')
                        ->insert([
                            'date'              => $today->toDateString(),
                            'last_notified_at'  => $lastResDate,
                            'success_count'     => $successCount,
                            'total_subscribers' => $subscribers->count(),
                            'created_at'        => Carbon::now(),
                            'updated_at'        => Carbon::now(),
                            'status'            => 'แจ้งเตือนครั้งแรก'
                        ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("เกิดข้อผิดพลาดในการแจ้งเตือนผลแล็บ: " . $e->getMessage());
        }
    }
}
