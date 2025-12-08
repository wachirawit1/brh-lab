<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TelegramController;

Route::get('/', function () {
    return redirect()->route('index');
});

Route::fallback(function () {
    return abort(404);
});

Route::middleware(['guest.custom'])->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('loginForm');
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ฝั่ง User ทั่วไป
Route::middleware(['logged.in', 'check.session'])->group(function () {
    Route::get('/index', [AppController::class, 'index'])->name('index');
    Route::get('/lab-results/{hn}', [AppController::class, 'getLabResults']);
    Route::post('/notify', [TelegramController::class, 'notify'])->name('notify');
    Route::post('/telegram/send', [TelegramController::class, 'send'])->name('test.telegram.send');
});

Route::get('/telegram/updates', [TelegramController::class, 'getUpdates'])->name('get.chatids');
// Route::put('/telegram/chats/{chatId}/deactivate', [TelegramController::class, 'deactivateChat']);
// Route::get('/telegram/chats', [TelegramController::class, 'getAllChats']);

// Admin routes
Route::middleware(['logged.in', 'check.session', 'is.admin'])->group(function () {
    Route::get('/admin/notify-management', [AdminController::class, 'notificationSettings'])->name('admin.notifySettings');
    Route::post('/admin/notify-management/update', [AdminController::class, 'updateNotificationStatus'])->name('admin.updateNotificationStatus');

    Route::get('/admin/user-management', [AdminController::class, 'userManagement'])->name('admin.management');
    Route::get('/admin/findUser', [AdminController::class, 'findUser'])->name('admin.findUser');

    Route::post('/admin/users/{username}/set-role', [AdminController::class, 'setRole'])->name('admin.users.setRole');
    Route::delete('/admin/users/{userid}/destroy', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    Route::post('/admin/roles/store', [AdminController::class, 'storeRole'])->name('admin.roles.store');
    Route::delete('/admin/roles/destroy/{id}', [AdminController::class, 'destroyRole'])->name('admin.roles.destroy');

    Route::delete('/admin/notify-management/destroy/{id}', [AdminController::class, 'destroyNotify'])->name('admin.deleteNotificationSubscriber');
});
