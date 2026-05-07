<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    if (!Schema::hasColumn('users', 'last_active_at')) {
        return;
    }

    $cutoff = now()->subMinutes(30);

    DB::table('users')
        ->where('account_status', 'active')
        ->where(function ($query) use ($cutoff) {
            $query->where('last_active_at', '<=', $cutoff)
                ->orWhere(function ($subQuery) use ($cutoff) {
                    $subQuery->whereNull('last_active_at')
                        ->where('created_at', '<=', $cutoff);
                });
        })
        ->update(['account_status' => 'inactive']);
})->everyMinute()->name('users:mark-inactive');
