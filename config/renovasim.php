<?php

/**
 * Mock data ported from src/context/ProjectContext.tsx
 *
 * In a production app this would be replaced with a `projects` table and
 * Eloquent models — the structure below mirrors the React `Project` interface
 * exactly so views can be wired in unchanged.
 */
return [

    'projects' => [
        [
            'id' => '1',
            'name' => 'Rumah Pak Budi',
            'status' => 'In Progress',
            'modified' => 'Modified 2 hours ago',
            'materialCost' => 12_400_000,
            'laborCost' => 8_200_000,
            'totalCost' => 20_600_000,
            'paid' => 5_000_000,
        ],
        [
            'id' => '2',
            'name' => 'Kitchen Expansion',
            'status' => 'Completed',
            'modified' => 'Modified 1 day ago',
            'materialCost' => 45_000_000,
            'laborCost' => 18_500_000,
            'totalCost' => 63_500_000,
            'paid' => 63_500_000,
        ],
        [
            'id' => '3',
            'name' => 'Garden Decking',
            'status' => 'Draft',
            'modified' => 'Modified 3 days ago',
            'materialCost' => 5_200_000,
            'laborCost' => 3_000_000,
            'totalCost' => 8_200_000,
            'paid' => 0,
        ],
    ],

    /**
     * Default mock estimation result (port of getMockResponse() in
     * src/pages/EstimationResult.tsx). Wire to a real /api/v2/estimate later.
     */
    'mock_estimate' => [
        'project_name' => 'Renovasi Rumah',
        'mode' => 'standard',
        'confidence' => [
            'score' => 0.72,
            'label' => 'Sedang',
            'message' => 'Estimasi perlu beberapa asumsi',
        ],
        'pre_framing' =>
            "Banyak yang mengira biaya cat hanya untuk catnya saja, padahal persiapan permukaan dan upah tukang sering jadi porsi terbesar.",
        'total_range' => [
            'min' => 8_000_000,
            'max' => 12_000_000,
            'display' => 'Rp 8 – 12 juta',
        ],
        'breakdown' => [
            ['job_type' => 'plumbing', 'area' => 9, 'min' => 3_000_000, 'max' => 4_500_000],
            ['job_type' => 'ceramic',  'area' => 9, 'min' => 5_000_000, 'max' => 7_500_000],
        ],
        'assumptions' => [
            [
                'field' => 'area',
                'value' => '9m²',
                'source' => 'assumed',
                'reason' => "Luas diasumsikan dari deskripsi 'kecil'",
                'impact' => 'high',
                'needs_clarification' => true,
                'editable' => true,
            ],
            [
                'field' => 'location',
                'value' => 'jakarta',
                'source' => 'user',
                'reason' => 'Berdasarkan lokasi yang Anda input',
                'impact' => 'high',
                'needs_clarification' => false,
                'editable' => true,
            ],
            [
                'field' => 'quality',
                'value' => 'standar',
                'source' => 'user',
                'reason' => 'Material kualitas standar pasaran',
                'impact' => 'medium',
                'needs_clarification' => false,
                'editable' => true,
            ],
        ],
        'explanation' => [
            'Upah tukang di Jakarta ~30% lebih tinggi dari rata-rata nasional',
            'Pemasangan keramik butuh ketelitian lebih — biaya tukang lebih tinggi',
            'Ditambahkan 5% untuk material cadangan dan waste selama pengerjaan',
        ],
        'warnings' => [
            [
                'type' => 'underbudget',
                'severity' => 'critical',
                'message' => 'Budget Anda kemungkinan tidak cukup untuk scope ini.',
            ],
        ],
        'clarification_needed' => 'Berapa luas area yang akan direnovasi? (dalam m²)',
        'disclaimer' =>
            'Estimasi ini berdasarkan harga pasar rata-rata dan dapat bervariasi tergantung kondisi lapangan, ketersediaan material, dan negosiasi dengan kontraktor.',
    ],

    'job_type_id' => [
        'painting'      => 'Pengecatan',
        'ceramic'       => 'Pemasangan Keramik',
        'plumbing'      => 'Plumbing',
        'electrical'    => 'Instalasi Listrik',
        'roofing'       => 'Pekerjaan Atap',
        'waterproofing' => 'Waterproofing',
    ],

    'assumption_field_id' => [
        'area'     => 'Luas Area',
        'location' => 'Lokasi',
        'quality'  => 'Kualitas Material',
        'scope'    => 'Scope Renovasi',
    ],

    'cities' => [
        'Aceh', 'Ambon', 'Balikpapan', 'Banda Aceh', 'Bandar Lampung', 'Bandung',
        'Banjarmasin', 'Batam', 'Bekasi', 'Bengkulu', 'Bogor', 'Cilegon',
        'Cimahi', 'Cirebon', 'Denpasar', 'Depok', 'Dumai', 'Gorontalo',
        'Jakarta', 'Jambi', 'Jayapura', 'Kendari', 'Kupang', 'Lhokseumawe',
        'Lubuklinggau', 'Madiun', 'Magelang', 'Makassar', 'Malang', 'Manado',
        'Mataram', 'Medan', 'Metro', 'Mojokerto', 'Padang', 'Padangpanjang',
        'Pagar Alam', 'Palangkaraya', 'Palembang', 'Palu', 'Pangkalpinang',
        'Pare Pare', 'Pariaman', 'Pasuruan', 'Payakumbuh', 'Pekanbaru',
        'Pematangsiantar', 'Pontianak', 'Probolinggo', 'Samarinda', 'Sawahlunto',
        'Semarang', 'Serang', 'Sibolga', 'Singkawang', 'Solok', 'Sorong',
        'Subulussalam', 'Sukabumi', 'Surabaya', 'Surakarta', 'Tangerang',
        'Tasikmalaya', 'Tanjungpinang', 'Tarakan', 'Ternate', 'Tidore', 'Yogyakarta',
    ],

    'renovation_types' => [
        'Pengecatan', 'Lantai Keramik', 'Kamar Mandi', 'Dapur', 'Atap',
        'Plafon', 'Listrik', 'Sanitasi', 'Pintu & Jendela',
        'Taman & Eksterior', 'Renovasi Total',
    ],

    'qualities' => ['Ekonomi', 'Standar', 'Premium'],
];
