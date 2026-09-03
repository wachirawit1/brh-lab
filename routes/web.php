<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AmrController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('amr.index');
});

Route::middleware(['guest.custom'])->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('loginForm');
});

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
    Route::get('/index', function () {
        return redirect()->route('amr.index');
    })->name('index');
    Route::get('/amr', [AmrController::class, 'index'])->name('amr.index');
    Route::get('/lab-results/{hn}', [AppController::class, 'getLabResults']);

    // QR Code รับแจ้งเตือน Telegram
    Route::get('/telegram/qr-data', [TelegramController::class, 'getQrData'])->name('telegram.qr');

    // จัดการข้อมูลการแพ้ยา
    Route::get('/patients/{hn}/allergy', [AppController::class, 'createAllergy'])->name('patients.allergy.create');
    Route::post('/patients/{hn}/allergy', [AppController::class, 'storeAllergy'])->name('patients.allergy.store');

    // จัดการข้อมูลเชื้อดื้อยา AMR
    Route::get('/amr/organisms/{hn}', [AmrController::class, 'getOrganisms'])->name('amr.organisms.get');
    Route::post('/amr/organisms', [AmrController::class, 'storeOrganisms'])->name('amr.organisms.store');

});

// Admin routes
Route::middleware(['logged.in', 'check.session', 'is.admin'])->group(function () {
    // Settings Hub: Master Data & Audit Logs
    Route::get('/settings/master-organisms', [AmrController::class, 'getMasterOrganisms'])->name('settings.organisms.index');
    Route::post('/settings/master-organisms', [AmrController::class, 'storeMasterOrganism'])->name('settings.organisms.store');
    Route::patch('/settings/master-organisms/reorder', [AmrController::class, 'reorderMasterOrganisms'])->name('settings.organisms.reorder');
    Route::patch('/settings/master-organisms/{id}/toggle', [AmrController::class, 'toggleMasterOrganism'])->name('settings.organisms.toggle');
    Route::get('/settings/audit-logs', [AmrController::class, 'getAuditLogs'])->name('settings.audit.logs');

    Route::post('/notify', [TelegramController::class, 'notify'])->name('notify');
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
