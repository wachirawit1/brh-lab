<?php

namespace App\Helpers;

class TelegramHelper
{
    public static function sendMessage($chatId, $message)
    {
        // API URL ของ Telegram
        $botToken = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        // ข้อมูลที่จะส่ง
        $data = [
            "chat_id" => $chatId,
            "text" => $message,
            "parse_mode" => "HTML", // สำหรับใช้ <b>, <i>, <code> ได้
            "disable_web_page_preview" => true
        ];

        // ใช้ cURL ส่งข้อความ
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL Error: " . $error);
        }

        curl_close($ch);

        // ตรวจสอบสถานะการส่ง
        $responseData = json_decode($response, true);

        if ($httpCode !== 200 || !$responseData['ok']) {
            $errorMsg = $responseData['description'] ?? 'Unknown error';
            throw new \Exception("Telegram API Error: " . $errorMsg);
        }

        return $response;
    }

    /**
     * ส่งข้อความพร้อมปุ่ม Inline Keyboard
     */
    public static function sendMessageWithKeyboard($chatId, $message, $keyboard = [])
    {
        $botToken = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            "chat_id" => $chatId,
            "text" => $message,
            "parse_mode" => "HTML",
            "disable_web_page_preview" => true
        ];

        // เพิ่ม keyboard ถ้ามี
        if (!empty($keyboard)) {
            $data["reply_markup"] = json_encode([
                "inline_keyboard" => $keyboard
            ]);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL Error: " . $error);
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if ($httpCode !== 200 || !$responseData['ok']) {
            $errorMsg = $responseData['description'] ?? 'Unknown error';
            throw new \Exception("Telegram API Error: " . $errorMsg);
        }

        return $response;
    }

    /**
     * ดึงข้อมูลบอท
     */
    public static function getBotInfo()
    {
        $botToken = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$botToken}/getMe";

        $response = file_get_contents($url);
        return json_decode($response, true);
    }

    /**
     * บันทึก chat_id ไปยัง Laravel API
     */
    public static function subscribeUser($chatId, $userName = null)
    {
        
        $url = url('/api/telegram/subscribe');

        $data = [
            'chat_id' => $chatId,
            'user_name' => $userName
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
