<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class SupabaseService
{
    protected string $url;
    protected string $key;
    protected string $secret;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->secret = config('services.supabase.secret');

        if (!$this->url || !$this->key) {
            throw new Exception('Supabase credentials not configured');
        }
    }

    /**
     * SELECT from a table
     * @param string $table
     * @param array|string $columns
     * @param array $filters ['column' => 'value', 'column2' => ['gt', 'value']]
     * @return array
     */
    public function select(string $table, $columns = '*', array $filters = []): array
    {
        try {
            $url = $this->url . '/rest/v1/' . $table;
            $query = is_array($columns) ? implode(',', $columns) : $columns;
            
            $params = ['select' => $query];
            
            // Build filter string
            if (!empty($filters)) {
                foreach ($filters as $key => $value) {
                    if (is_array($value)) {
                        // Handle operators like ['gt', 100]
                        [$op, $val] = $value;
                        $params[$key] = "$op.$val";
                    } else {
                        // Simple equality
                        $params[$key] = "eq.$value";
                    }
                }
            }

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()  // Disable SSL verification for development
            ->get($url, $params);

            if ($response->failed()) {
                \Log::error('Supabase select failed: ' . $response->status() . ' - ' . $response->body());
                return [];
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            \Log::error('Supabase select error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * INSERT into a table
     * @param string $table
     * @param array $data
     * @return array|bool
     */
    public function insert(string $table, array $data)
    {
        try {
            $url = $this->url . '/rest/v1/' . $table;

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])
            ->withoutVerifying()  // Disable SSL verification
            ->post($url, $data);

            if ($response->failed()) {
                \Log::error('Supabase insert error: ' . $response->body());
                return false;
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            \Log::error('Supabase insert exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE a record
     * @param string $table
     * @param string|int $id
     * @param array $data
     * @return bool
     */
    public function update(string $table, $id, array $data): bool
    {
        try {
            $url = $this->url . '/rest/v1/' . $table . '?id=eq.' . $id;

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()  // Disable SSL verification
            ->patch($url, $data);

            if ($response->failed()) {
                \Log::error('Supabase update error: ' . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Supabase update exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE a record
     * @param string $table
     * @param string|int $id
     * @return bool
     */
    public function delete(string $table, $id): bool
    {
        try {
            $url = $this->url . '/rest/v1/' . $table . '?id=eq.' . $id;

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()  // Disable SSL verification
            ->delete($url);

            if ($response->failed()) {
                \Log::error('Supabase delete error: ' . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Supabase delete exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute a raw RPC function
     * @param string $functionName
     * @param array $params
     * @return mixed
     */
    public function rpc(string $functionName, array $params = [])
    {
        try {
            $url = $this->url . '/rest/v1/rpc/' . $functionName;

            $response = Http::withHeaders([
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()  // Disable SSL verification
            ->post($url, $params);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            \Log::error('Supabase RPC error: ' . $e->getMessage());
            return null;
        }
    }
}
