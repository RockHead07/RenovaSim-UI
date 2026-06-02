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
        $this->url    = config('services.supabase.url')    ?? '';
        $this->key    = config('services.supabase.key')    ?? '';
        $this->secret = config('services.supabase.secret') ?? '';

        if (!$this->url || !$this->key) {
            throw new Exception('Supabase credentials not configured. Add SUPABASE_URL and SUPABASE_KEY to .env');
        }
    }

    // Using sb_secret_ key as apikey (not Bearer) bypasses Supabase RLS for
    // server-side access. Falls back to the publishable key when secret is absent.
    protected function headers(array $extra = []): array
    {
        $serviceKey = $this->secret ?: $this->key;
        return array_merge([
            'apikey'        => $serviceKey,
            'Authorization' => 'Bearer ' . $serviceKey,
            'Content-Type'  => 'application/json',
        ], $extra);
    }

    protected function http()
    {
        return Http::withHeaders($this->headers())->withoutVerifying();
    }

    /**
     * SELECT from a table
     * filters: ['column' => 'value'] or ['column' => ['gt', 'value']]
     */
    public function select(string $table, $columns = '*', array $filters = []): array
    {
        try {
            $url   = $this->url . '/rest/v1/' . $table;
            $query = is_array($columns) ? implode(',', $columns) : $columns;
            $params = ['select' => $query];

            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    [$op, $val] = $value;
                    $params[$key] = "$op.$val";
                } else {
                    $params[$key] = "eq.$value";
                }
            }

            $response = $this->http()->get($url, $params);

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
     * INSERT into a table — returns inserted rows or false
     */
    public function insert(string $table, array $data)
    {
        try {
            $url = $this->url . '/rest/v1/' . $table;

            $response = $this->http()
                ->withHeaders(['Prefer' => 'return=representation'])
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
     * UPDATE by primary key id
     */
    public function update(string $table, $id, array $data): bool
    {
        try {
            $url = $this->url . '/rest/v1/' . $table . '?id=eq.' . $id;

            $response = $this->http()->patch($url, $data);

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
     * DELETE by primary key id
     */
    public function delete(string $table, $id): bool
    {
        try {
            $url = $this->url . '/rest/v1/' . $table . '?id=eq.' . $id;

            $response = $this->http()->delete($url);

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
     * DELETE with custom filters — same filter format as select()
     * Filters are sent as query-string parameters (required by PostgREST).
     */
    public function deleteWhere(string $table, array $filters): bool
    {
        try {
            $params = [];
            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    [$op, $val] = $value;
                    $params[$key] = "$op.$val";
                } else {
                    $params[$key] = "eq.$value";
                }
            }

            // PostgREST expects filters in query string for DELETE
            $url      = $this->url . '/rest/v1/' . $table . '?' . http_build_query($params);
            $response = $this->http()->delete($url);

            if ($response->failed()) {
                \Log::error('Supabase deleteWhere error: ' . $response->body());
                return false;
            }

            return true;
        } catch (Exception $e) {
            \Log::error('Supabase deleteWhere exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * SELECT with an OR filter string (PostgREST `or` param).
     * Example: selectOr('users', '*', 'username.ilike.*foo*,email.ilike.*foo*')
     */
    public function selectOr(string $table, $columns = '*', string $orFilter = '', array $extraFilters = []): array
    {
        try {
            $url    = $this->url . '/rest/v1/' . $table;
            $query  = is_array($columns) ? implode(',', $columns) : $columns;
            $params = ['select' => $query];

            if ($orFilter !== '') {
                $params['or'] = "($orFilter)";
            }

            foreach ($extraFilters as $key => $value) {
                $params[$key] = is_array($value) ? "{$value[0]}.{$value[1]}" : "eq.$value";
            }

            $response = $this->http()->get($url, $params);

            if ($response->failed()) {
                \Log::error('Supabase selectOr failed: ' . $response->status() . ' - ' . $response->body());
                return [];
            }

            return $response->json() ?? [];
        } catch (Exception $e) {
            \Log::error('Supabase selectOr error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Call a Supabase RPC (database function)
     */
    public function rpc(string $functionName, array $params = [])
    {
        try {
            $url = $this->url . '/rest/v1/rpc/' . $functionName;

            $response = $this->http()->post($url, $params);

            if ($response->failed()) {
                \Log::error('Supabase RPC error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            \Log::error('Supabase RPC error: ' . $e->getMessage());
            return null;
        }
    }
}
