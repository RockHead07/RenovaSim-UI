<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('start', function () {
    $this->info('🚀 Memulai semua server (Laravel :8080, Vite, RAI :5000)...');
    $this->info('   LARAVEL  → http://localhost:8080');
    $this->info('   VITE     → http://localhost:5173');
    $this->info('   RAI      → http://localhost:5000');
    $this->info('');
    $this->info('Tekan Ctrl+C untuk menghentikan semua server sekaligus.');

    passthru(
        'npx concurrently'
        . ' -c "blue,cyan,yellow"'
        . ' -n "LARAVEL,VITE,RAI"'
        . ' "php artisan serve --port=8080"'
        . ' "npm run dev"'
        . ' "cd RAI && py app_server.py"'
    );
})->purpose('Mulai semua server: Laravel :8080, Vite, RAI :5000');

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
