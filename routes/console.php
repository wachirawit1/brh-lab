<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Helpers\TelegramHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Jobs\NotifyLabResults;

Schedule::job(new NotifyLabResults)
    ->everyMinute()
    ->withoutOverlapping()
    // ->onFailure(function ($e) {
    //     Log::error('Notification job failed: ' . $e->getMessage());
    // })
    ->name('notify_lab_results');

// ->withoutOverlapping();
