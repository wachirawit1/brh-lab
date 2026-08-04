<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TelegramController;

Route::get('/', function () {
    return redirect()->route('index');
});


Route::middleware(['guest.custom'])->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('loginForm');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// เช็คสถานะ Session (ใช้โดย JavaScript Polling)
Route::get('/check-session', function () {
    if (session()->has('user') && session('user.logged_in') === true) {
        // เช็คเวลา last_activity แบบเดียวกับใน CheckUserSession Middleware
        $lastActivity = session('user.last_activity');
        if ($lastActivity && now()->diffInMinutes($lastActivity) > 60) {
            session()->forget('user'); // เคลียร์ session
            \Illuminate\Support\Facades\Auth::logout();
            return response()->json(['alive' => false, 'message' => 'Session Expired'], 401);
        }

        return response()->json(['alive' => true]);
    }
    return response()->json(['alive' => false], 401);
})->name('session.ping');

// ฝั่ง User ทั่วไป
Route::middleware(['logged.in', 'check.session'])->group(function () {
    Route::get('/index', [AppController::class, 'index'])->name('index');
    Route::get('/lab-results/{hn}', [AppController::class, 'getLabResults']);
    Route::post('/notify', [TelegramController::class, 'notify'])->name('notify');
    Route::post('/telegram/send', [TelegramController::class, 'send'])->name('test.telegram.send');
    
    // จัดการข้อมูลการแพ้ยา
    Route::get('/patients/{hn}/allergy', [AppController::class, 'createAllergy'])->name('patients.allergy.create');
    Route::post('/patients/{hn}/allergy', [AppController::class, 'storeAllergy'])->name('patients.allergy.store');
});

Route::get('/telegram/updates', [TelegramController::class, 'getUpdates'])->name('get.chatids');
Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook'])->name('telegram.webhook');
Route::get('/telegram/set-webhook', [TelegramController::class, 'setWebhook'])->name('telegram.setWebhook');
Route::get('/telegram/webhook-info', [TelegramController::class, 'getWebhookInfo'])->name('telegram.webhookInfo');
// Route::put('/telegram/chats/{chatId}/deactivate', [TelegramController::class, 'deactivateChat']);
// Route::get('/telegram/chats', [TelegramController::class, 'getAllChats']);

// Admin routes
Route::middleware(['logged.in', 'check.session', 'is.admin'])->group(function () {
    Route::get('/admin/notify-management', [AdminController::class, 'notificationSettings'])->name('admin.notifySettings');
    Route::post('/admin/notify-management/update', [AdminController::class, 'updateNotificationStatus'])->name('admin.updateNotificationStatus');

    Route::get('/admin/user-management', [AdminController::class, 'userManagement'])->name('admin.management');
    Route::get('/admin/findUser', [AdminController::class, 'findUser'])->name('admin.findUser');

    Route::post('/admin/users/{username}/set-role', [AdminController::class, 'setRole'])->name('admin.users.setRole');
    Route::delete('/admin/users/{username}/destroy', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::post('/admin/roles/store', [AdminController::class, 'storeRole'])->name('admin.roles.store');
    Route::delete('/admin/roles/destroy/{id}', [AdminController::class, 'destroyRole'])->name('admin.roles.destroy');

    Route::delete('/admin/notify-management/destroy/{id}', [AdminController::class, 'destroyNotify'])->name('admin.deleteNotificationSubscriber');
});

Route::fallback(function () {
    return abort(404);
});
