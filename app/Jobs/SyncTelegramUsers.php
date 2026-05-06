<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncTelegramUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $botToken = config('services.telegram.bot_token');
        if (empty($botToken)) {
            // Fallback ถ้าไม่ได้เซ็ตใน config
            $botToken = env('TELEGRAM_BOT_TOKEN');
        }

        $url = "https://api.telegram.org/bot{$botToken}/getUpdates";

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response === false) {
                return;
            }

            $data = json_decode($response, true);

            if (isset($data['result']) && is_array($data['result'])) {
                foreach ($data['result'] as $update) {
                    // ตรวจสอบว่ามี message และ chat id
                    if (isset($update['message']['chat']['id'])) {
                        $this->processChat($update['message']);
                    }
                    // รองรับ callback query (ถ้ามี)
                    elseif (isset($update['callback_query']['message']['chat']['id'])) {
                        $this->processChat($update['callback_query']['message']);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("SyncTelegramUsers Error: " . $e->getMessage());
        }
    }

    private function processChat($message)
    {
        $chat = $message['chat'];
        $from = $message['from'] ?? [];
        $chatId = $chat['id'];

        // ดึง username จาก /start command (ถ้ามี)
        $username = null;
        if (isset($message['text']) && strpos($message['text'], '/start ') === 0) {
            $username = trim(substr($message['text'], 7));
        }

        // เตรียมข้อมูล
        $data = [
            'first_name' => $from['first_name'] ?? $chat['first_name'] ?? null,
            'last_name' => $from['last_name'] ?? $chat['last_name'] ?? null,
            'username' => $from['username'] ?? $chat['username'] ?? null,
            'title' => $chat['title'] ?? null,
            'last_message_at' => isset($message['date']) ? Carbon::createFromTimestamp($message['date'])->setTimezone(config('app.timezone')) : now(),
            'updated_at' => now(),
        ];

        // อัปเดตหรือสร้างใหม่
        $existing = DB::table('telegram_subscribers')->where('chat_id', $chatId)->first();

        if ($existing) {
            // อัปเดตข้อมูลล่าสุด และสถานะ Active
            $updateData = $data;

            // ถ้ามี username ส่งมาใหม่ ให้ Update ทับ
            if ($username) {
                $updateData['pm'] = $username;
            }

            // Re-activate ถ้าเคยถูกปิดไป
            $updateData['is_active'] = true;

            DB::table('telegram_subscribers')
                ->where('chat_id', $chatId)
                ->update($updateData);
        } else {
            // สร้างใหม่
            $data['chat_id'] = $chatId;
            $data['pm'] = $username; // อาจเป็น null ถ้ากด start เฉยๆ
            $data['is_active'] = true;
            $data['allowed'] = true; // อนุญาตโดยเริ่มต้น (หรือจะแก้เป็น false เพื่อรออนุมัติ)
            $data['created_at'] = now();

            DB::table('telegram_subscribers')->insert($data);
        }
    }
}
