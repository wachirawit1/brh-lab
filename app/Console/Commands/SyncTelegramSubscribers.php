<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Log;

class SyncTelegramSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ดึงข้อมูลผู้ติดตามใหม่จาก Telegram API ลงฐานข้อมูลอัตโนมัติ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $controller = app(TelegramController::class);
            $response = $controller->getUpdates();
            
            $data = json_decode($response->getContent(), true);
            if (isset($data['success']) && $data['success']) {
                $newCount = $data['new'] ?? 0;
                $updatedCount = $data['updated'] ?? 0;
                $this->info("Sync completed: {$newCount} new, {$updatedCount} updated.");
                Log::info("Telegram Sync Command: {$newCount} new, {$updatedCount} updated.");
            } else {
                $errorMsg = $data['error'] ?? 'Unknown error or empty response';
                $this->error("Sync failed: " . $errorMsg);
                Log::warning("Telegram Sync Failed: " . $errorMsg . " | Raw: " . $response->getContent());
            }
        } catch (\Exception $e) {
            $this->error("Error executing Telegram sync: " . $e->getMessage());
            Log::error("Error executing Telegram sync: " . $e->getMessage());
        }

        return 0;
    }
}
