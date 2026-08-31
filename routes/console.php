<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\NotifyLabResults;

if (config('services.lab_notifications.enabled')) {
    Schedule::job(new NotifyLabResults)
        ->hourly()
        ->withoutOverlapping()
        ->name('notify_lab_results');
}

Schedule::command('telegram:sync')
    ->everyMinute()
    ->withoutOverlapping()
    ->name('sync_telegram_users');
