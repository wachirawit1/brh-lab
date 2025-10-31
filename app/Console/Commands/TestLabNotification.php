<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Helpers\TelegramHelper;

class TestLabNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:lab-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ทดสอบการแจ้งเตือนผลแล็บผ่าน Telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('เริ่มทดสอบการแจ้งเตือน...');

        $labres = DB::connection('sqlsrv')
            ->table('Labres_m')
            ->whereDate('res_date', Carbon::now())
            ->get();

        $this->info("พบผลแล็บวันนี้: {$labres->count()} รายการ");

        if ($labres->count() > 0) {
            $subscribers = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->where('is_active', 1)
                ->get();

            $this->info("พบผู้รับการแจ้งเตือน: {$subscribers->count()} คน");

            // สร้าง array เก็บ HN
        $hns = $labres->pluck('hn')->unique()->implode(', ');


            $message = "📢 แจ้งเตือน (ทดสอบ)\n" .
                "ผลแล็บประจำวันที่ " . Carbon::now()->format('d/m/Y') . "\n" .
                "มีhn ดังนี้:\n" .
                $hns;

            foreach ($subscribers as $subscriber) {
                try {
                    TelegramHelper::sendMessage($subscriber->chat_id, $message);
                    $this->info("✅ ส่งข้อความสำเร็จ - Chat ID: {$subscriber->chat_id}");
                } catch (\Exception $e) {
                    $this->error("❌ ไม่สามารถส่งข้อความ - Chat ID: {$subscriber->chat_id}");
                    $this->error("สาเหตุ: " . $e->getMessage());
                }
            }
        }
        $this->info('เสร็จสิ้นการทดสอบ');
    }
}
