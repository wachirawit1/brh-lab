<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramController extends Controller
{
    /**
     * แจ้งเตือนผู้ป่วยทาง Telegram
     */
    public function notify(Request $request)
    {
        try {
            $hn = $request->input('hn');
            $action = $request->input('action');
            $firstname = $request->input('firstname') ?? '';
            $lastname = $request->input('lastname') ?? '';
            $fullname = trim($firstname . ' ' . $lastname);

            // สร้างข้อความที่จะส่ง
            $message = "🏥 <b>แจ้งเตือนจากระบบผู้ป่วย</b>\n\n"
                . "📋 <b>HN:</b> {$hn}\n"
                . "👤 <b>ชื่อ-นามสกุล:</b> {$fullname}\n"
                . "⚡ <b>การกระทำ:</b> {$action}\n"
                . "🕐 <b>เวลา:</b> " . now()->format('d/m/Y H:i:s');

            $subscribers = DB::connection('mysql')
                ->table('telegram_subscribers')
                ->get();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($subscribers as $subscriber) {
                try {
                    TelegramHelper::sendMessage($subscriber->chat_id, $message);
                    $successCount++;
                } catch (\Exception $e) {
                    // บันทึกข้อผิดพลาดหากไม่สามารถส่งข้อความได้
                    $errorCount++;
                    $errors[] = "Chat ID {$subscriber->chat_id}: " . $e->getMessage();
                    Log::error("ไม่สามารถส่งข้อความไปยัง Chat ID: {$subscriber->chat_id}", [
                        'error' => $e->getMessage(),
                        'hn' => $hn,
                        'action' => $action
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "ส่งแจ้งเตือนเรียบร้อยแล้ว",
                'data' => [
                    'total_subscribers' => count($subscribers),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Notification error:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการส่งแจ้งเตือน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงข้อมูลอัพเดทจาก Telegram เพื่อหา Chat ID
     */
    public function getUpdates()
    {
        $botToken = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$botToken}/getUpdates";

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new \Exception("cURL Error");
            }

            if ($httpCode !== 200) {
                throw new \Exception("HTTP Error: " . $httpCode);
            }

            $data = json_decode($response, true);

            $savedChats = [];
            $newChats = 0;
            $updatedChats = 0;

            if (isset($data['result']) && is_array($data['result'])) {
                DB::beginTransaction();
                try {
                    foreach ($data['result'] as $update) {
                        if (isset($update['message']['chat']['id'])) {
                            $chatData = $this->prepareChatData($update);
                            $chatId = $chatData['chat_id'];

                            $existingChat = DB::table('telegram_subscribers')->where('chat_id', $chatId)->first();

                            if ($existingChat) {
                                DB::table('telegram_subscribers')
                                    ->where('chat_id', $chatId)
                                    ->update($chatData);
                                $updatedChats++;
                                $status = 'updated';
                            } else {
                                DB::table('telegram_subscribers')->insert($chatData);
                                $newChats++;
                                $status = 'new';
                            }

                            $savedChats[] = [
                                'chat_id' => $chatId,
                                'name'    => $chatData['first_name'] ?? null,
                                'title'   => $chatData['title'] ?? null,
                                'pm'      => $chatData['pm'] ?? null,
                                'status'  => $status,
                                'updated_at' => now(),

                            ];
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            return response()->json([
                'success' => true,
                'new' => $newChats,
                'updated' => $updatedChats,
                'saved' => $savedChats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * เตรียมข้อมูลสำหรับบันทึกเข้าฐานข้อมูล
     */
    private function prepareChatData($update)
    {
        $message = $update['message'];
        $chat = $message['chat'];
        $from = $message['from'] ?? [];

        // ดึง username จาก /start command
        $username = null;
        if (isset($message['text']) && strpos($message['text'], '/start ') === 0) {
            $username = trim(substr($message['text'], 7));
        }

        $data = [
            'chat_id' => $chat['id'],
            'first_name' => $from['first_name'] ?? $chat['first_name'] ?? null,
            'last_name' => $from['last_name'] ?? $chat['last_name'] ?? null,
            'username' => $from['username'] ?? $chat['username'] ?? null,
            'title' => $chat['title'] ?? null,
            'last_message_at' => isset($message['date']) ? Carbon::createFromTimestamp($message['date'])->setTimezone(config('app.timezone')) : now(),
            'is_active' => true,
            'allowed' => true,
        ];

        // เพิ่ม pm เฉพาะตอน insert หรือมี username ใหม่
        if ($username) {
            $data['pm'] = $username;
        }

        // เพิ่ม created_at เฉพาะตอน insert
        if (!DB::table('telegram_subscribers')->where('chat_id', $chat['id'])->exists()) {
            $data['created_at'] = now();
        }

        return $data;
    }

    /**
     * ดึงรายการ Chat ทั้งหมดจากฐานข้อมูล
     */
    public function getAllChats()
    {
        try {
            $chats = DB::connection('mysql')->table('telegram_subscribers')
                ->where('is_active', true)
                ->orderBy('last_message_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $chats,
                'total' => $chats->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Get all chats error:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'ไม่สามารถดึงข้อมูล Chat ได้'
            ], 500);
        }
    }
    /**
     * ปิดใช้งาน Chat (soft delete)
     */
    public function deactivateChat($chatId)
    {
        try {
            $chat = DB::connection('mysql')->table('telegram_subscribers')
                ->where('chat_id', $chatId)->first();

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'error' => 'ไม่พบ Chat ID นี้'
                ], 404);
            }

            $chat->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'ปิดใช้งาน Chat เรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            Log::error('Deactivate chat error:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'ไม่สามารถปิดใช้งาน Chat ได้'
            ], 500);
        }
    }

    public function send(Request $request)
    {
        $request->validate([
            'chat_id' => 'required',
            'message' => 'required|string'
        ]);

        try {
            TelegramHelper::sendMessage($request->chat_id, $request->message);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
