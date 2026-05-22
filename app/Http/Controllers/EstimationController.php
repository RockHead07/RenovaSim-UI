<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EstimationController extends Controller
{
    public function result(Request $request)
    {
        $projectName = $request->query('projectName', 'Proyek Renovasi');
        $city = $request->query('city', 'Jakarta');
        $renovationType = $request->query('renovationType');
        $quality = $request->query('quality', 'Standar');
        $area = $request->query('area');
        $unit = $request->query('unit', 'm²');
        $description = $request->query('description', '');
        $mode = $request->query('mode', 'detailed');
        $budget = (int) $request->query('budget', 0);

        // Quality mapping from Indonesian UI labels to English API fields
        $qualityMap = [
            'Ekonomis' => 'ekonomi',
            'Standar' => 'standar',
            'Premium' => 'premium',
        ];
        $apiQuality = isset($qualityMap[$quality]) ? $qualityMap[$quality] : 'standar';

        // Job type mapping from Indonesian UI labels to English API fields
        $jobTypeMap = [
            'Pengecatan' => 'painting',
            'Plesteran' => 'plastering',
            'Renovasi Dapur' => 'kitchen',
            'Renovasi Kamar Mandi' => 'bathroom',
            'Renovasi Total' => 'full_renovation',
            'Penambahan Ruangan' => 'plastering',
            'Lain-lain' => null,
        ];
        $apiJobType = isset($jobTypeMap[$renovationType]) ? $jobTypeMap[$renovationType] : null;

        $response = null;
        $error = null;

        $inputs = [
            'projectName'    => $projectName,
            'city'           => $city,
            'renovationType' => $renovationType,
            'quality'        => $quality,
            'area'           => $area,
            'unit'           => $unit,
            'description'    => $description,
            'mode'           => $mode,
            'budget'         => $budget,
        ];

        try {
            if ($mode === 'ai') {
                $payload = [
                    'project_name' => $projectName,
                    'text' => $description ?: ('Saya ingin merenovasi di kota ' . $city),
                    'budget' => $budget > 0 ? (float) $budget : null,
                ];

                Log::info('Calling RAI AI Estimate API', ['payload' => $payload]);
                $apiResponse = Http::timeout(12)
                    ->post('http://127.0.0.1:8000/api/v2/estimate/ai', $payload);
            } else {
                // Determine scope
                $apiScope = null;
                if ($mode === 'quick') {
                    $apiScope = 'medium';
                }

                $payload = [
                    'project_name' => $projectName,
                    'location' => strtolower($city),
                    'job_type' => $apiJobType,
                    'quality' => $apiQuality,
                    'scope' => $apiScope,
                    'area' => $area ? (float) $area : null,
                    'description' => $description,
                    'budget' => $budget > 0 ? (float) $budget : null,
                ];

                Log::info('Calling RAI Estimate v2 API', ['payload' => $payload]);
                $apiResponse = Http::timeout(12)
                    ->post('http://127.0.0.1:8000/api/v2/estimate', $payload);
            }

            if ($apiResponse->successful()) {
                $response = $apiResponse->json();
            } else {
                $error = 'API responded with status code: ' . $apiResponse->status();
                Log::error('RAI API Error', ['body' => $apiResponse->body(), 'status' => $apiResponse->status()]);
            }
        } catch (\Exception $e) {
            $error = 'Failed to connect to RAI backend: ' . $e->getMessage();
            Log::error('RAI API connection failed', ['exception' => $e]);
        }

        // Graceful Fallback if API fails
        if (!$response) {
            $minBase = 150000;
            $maxBase = 250000;
            
            if ($apiQuality === 'ekonomi') {
                $minBase = 80000; $maxBase = 120000;
            } elseif ($apiQuality === 'premium') {
                $minBase = 250000; $maxBase = 450000;
            }

            $multiplierMap = [
                'jakarta' => 1.30, 'surabaya' => 1.15, 'bandung' => 1.10, 'semarang' => 1.05,
                'jogja' => 0.90, 'yogyakarta' => 0.90, 'default' => 1.00
            ];
            $mult = isset($multiplierMap[strtolower($city)]) ? $multiplierMap[strtolower($city)] : 1.00;

            $itemArea = $area ? (float) $area : 10.0;
            $itemMin = $minBase * $itemArea * $mult;
            $itemMax = $maxBase * $itemArea * $mult;

            $totalMin = $itemMin;
            $totalMax = $itemMax;
            $totalDisplay = 'Rp ' . number_format(($totalMin + $totalMax) / 2, 0, ',', '.');

            $response = [
                'project_name' => $projectName,
                'mode' => $mode,
                'confidence' => [
                    'score' => 60,
                    'label' => 'Sedang (Fallback)',
                    'message' => 'Estimasi dihasilkan menggunakan kalkulasi lokal karena backend RAI offline.',
                ],
                'pre_framing' => 'Estimasi lokal standar pasar.',
                'total_range' => [
                    'min' => $totalMin,
                    'max' => $totalMax,
                    'display' => $totalDisplay,
                ],
                'breakdown' => [
                    [
                        'job_type' => $apiJobType ?: 'painting',
                        'area' => $itemArea,
                        'min' => $itemMin,
                        'max' => $itemMax,
                    ]
                ],
                'assumptions' => [
                    [
                        'field' => 'Luas Area',
                        'value' => $itemArea . ' ' . $unit,
                        'reason' => 'Luas didasarkan dari input Anda',
                        'needs_clarification' => false
                    ],
                    [
                        'field' => 'Lokasi',
                        'value' => $city,
                        'reason' => 'Dari data input Anda',
                        'needs_clarification' => false
                    ],
                    [
                        'field' => 'Kualitas Material',
                        'value' => $quality,
                        'reason' => 'Kualitas standar pasaran',
                        'needs_clarification' => false
                    ],
                ],
                'explanation' => [
                    'Menggunakan kalkulator lokal standar.',
                    'Upah disesuaikan dengan tingkat regional kota ' . $city,
                ],
                'warnings' => [],
                'disclaimer' => 'Estimasi cadangan.',
            ];
        }

        return view('user.pages.estimation-result', [
            'response' => $response,
            'inputs' => $inputs
        ]);
    }
}
