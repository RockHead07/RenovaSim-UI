<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EstimationService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('ESTIMATION_API_URL', 'http://localhost:8000'), '/');
        $this->apiKey = env('ESTIMATION_API_KEY', 'dev-key-renovasim');
    }

    /**
     * POST /api/v2/estimate — wizard mode (structured inputs)
     */
    public function estimateWizard(array $data): array
    {
        $body = [
            'project_name' => $data['project_name'] ?? 'Proyek Renovasi',
            'location'     => $data['location'] ?? null,
            'job_type'     => $data['job_type'],
            'quality'      => $data['quality'] ?? 'standar',
            'scope'        => $data['scope'] ?? 'light',
            'area'         => (float)$data['area'],
            'description'  => $data['description'] ?? null,
        ];

        // Only include budget if it's a positive number
        if (!empty($data['budget']) && (int)$data['budget'] > 0) {
            $body['budget'] = (int)$data['budget'];
        }

        return $this->post('/api/v2/estimate', $body);
    }

    /**
     * POST /api/v2/estimate/ai — free-text AI mode
     */
    public function estimateAI(array $data): array
    {
        // Append location to text if provided, so AI can extract it
        $text = $data['description'];
        if (!empty($data['location'])) {
            $text .= ' di ' . $data['location'];
        }

        $body = [
            'project_name' => $data['project_name'] ?? 'Proyek Renovasi',
            'text'         => $text,
        ];

        // Only include budget if it's a positive number
        if (!empty($data['budget']) && (int)$data['budget'] > 0) {
            $body['budget'] = (int)$data['budget'];
        }

        if (!empty($data['area_hint'])) {
            $body['area_hint'] = (float)$data['area_hint'];
        }

        return $this->post('/api/v2/estimate/ai', $body);
    }

    public function refine(array $previousResult, array $corrections): array
    {
        // Remove null/empty values from corrections
        $corrections = array_filter($corrections, fn($v) => $v !== null && $v !== '');
        
        $response = Http::timeout(120)
            ->withHeaders(['X-API-Key' => env('ESTIMATION_API_KEY')])
            ->patch(env('ESTIMATION_API_URL') . '/api/v2/estimate/refine', [
                'previous_result' => $previousResult,
                'corrections'     => $corrections,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Gagal memperbarui estimasi (HTTP ' . $response->status() . '). ' .
                'Silakan coba lagi nanti.'
            );
        }

        return $response->json();
    }

    /**
     * Execute a POST request to the FastAPI backend.
     *
     * @throws RuntimeException on connection failure or HTTP error
     */
    protected function post(string $endpoint, array $payload): array
    {
        try {
            $response = Http::timeout(270)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->post($this->baseUrl . $endpoint, $payload);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->json('detail') ?? $response->body();

                throw new RuntimeException(
                    "Gagal mendapatkan estimasi dari server (HTTP {$status}). " .
                    "Silakan coba lagi nanti. Detail: {$body}"
                );
            }

            return $response->json();
        } catch (RuntimeException $e) {
            // Re-throw our own exceptions
            throw $e;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException(
                'Tidak dapat terhubung ke server estimasi. ' .
                'Pastikan server AI sedang berjalan dan coba lagi.'
            );
        } catch (\Exception $e) {
            throw new RuntimeException(
                'Terjadi kesalahan saat menghubungi server estimasi: ' . $e->getMessage()
            );
        }
    }
}
