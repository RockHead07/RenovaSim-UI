<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class ApiManagerController extends Controller
{
    public function index()
    {
        $apiKey  = config('services.renovasim_api.key', env('RENOVASIM_API_KEY', ''));
        $baseUrl = rtrim(config('app.url', 'http://127.0.0.1:8080'), '/') . '/api/v1';

        $endpoints = [
            ['method' => 'GET',    'path' => '/users',          'desc' => 'List semua users'],
            ['method' => 'GET',    'path' => '/users/{id}',      'desc' => 'Detail user by ID'],
            ['method' => 'GET',    'path' => '/projects',        'desc' => 'List semua projects'],
            ['method' => 'GET',    'path' => '/estimations',     'desc' => 'List semua estimations'],
            ['method' => 'GET',    'path' => '/materials',       'desc' => 'List semua materials'],
            ['method' => 'GET',    'path' => '/partners',        'desc' => 'List semua partners aktif'],
            ['method' => 'GET',    'path' => '/pricing-plans',   'desc' => 'List pricing plans + features'],
        ];

        return view('admin.api.index', compact('apiKey', 'baseUrl', 'endpoints'));
    }

    public function regenerate()
    {
        $newKey = 'rsk_' . bin2hex(random_bytes(24));
        // Write to .env file
        $envPath    = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, 'RENOVASIM_API_KEY=')) {
            $envContent = preg_replace('/^RENOVASIM_API_KEY=.*/m', "RENOVASIM_API_KEY={$newKey}", $envContent);
        } else {
            $envContent .= "\nRENOVASIM_API_KEY={$newKey}\n";
        }
        file_put_contents($envPath, $envContent);

        return back()->with('success', "API key baru: {$newKey} — simpan dan gunakan sebagai Bearer token.");
    }
}
