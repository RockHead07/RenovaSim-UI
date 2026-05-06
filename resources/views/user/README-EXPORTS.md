# RenovaSim UI Export — Setup Guide

Folder `exports` berisi semua file yang dibutuhkan untuk mengintegrasikan RenovaSim UI ke project Laravel final Anda. Struktur sudah diorganisir sesuai dengan penempatan target di Laravel project.

## 📁 Struktur Folder Exports

```
exports/
├── resources/
│   ├── user/
│   │   └── theme/
│   │       ├── css/user.css            → Copy ke: resources/user/theme/css/user.css
│   │       └── js/user.js              → Copy ke: resources/user/theme/js/user.js
│   └── views/
│       └── user/
│           ├── layouts/
│           │   ├── dashboard.blade.php → Copy ke: resources/views/user/layouts/dashboard.blade.php
│           │   └── app.blade.php       → Copy ke: resources/views/user/layouts/app.blade.php
│           ├── components/
│           │   ├── layout/             → Copy ke: resources/views/components/user/layout/
│           │   ├── shared/             → Copy ke: resources/views/components/user/shared/
│           │   └── estimation/         → Copy ke: resources/views/components/user/estimation/
│           └── pages/                  → Copy ke: resources/views/user/pages/
│
├── app/
│   └── Helpers/
│       └── helpers.php                 → Copy ke: app/Helpers/helpers.php
│
├── config/
│   └── renovasim.php                   → Copy ke: config/renovasim.php
│
└── build-config/
    ├── package.json                    → Merge dependencies
    ├── composer.json                   → Merge dependencies
    ├── tailwind.config.js              → Merge/update existing
    ├── vite.config.js                  → Merge/update existing
    └── postcss.config.js               → Copy atau merge jika belum ada
```

## 🚀 Langkah Implementasi

### 1. Copy File Statis (Helpers & Config)

```bash
# From: exports/app/Helpers/helpers.php
# To: YOUR_LARAVEL_PROJECT/app/Helpers/helpers.php

# From: exports/config/renovasim.php
# To: YOUR_LARAVEL_PROJECT/config/renovasim.php
```

### 2. Copy Theme Assets

```bash
# From: exports/resources/user/theme/
# To: YOUR_LARAVEL_PROJECT/resources/user/theme/

# Pastikan struktur:
# YOUR_LARAVEL_PROJECT/
#   resources/
#     user/
#       theme/
#         css/
#           user.css
#         js/
#           user.js
```

### 3. Copy Blade Layouts & Components

```bash
# Layouts:
# From: exports/resources/views/user/layouts/
# To: YOUR_LARAVEL_PROJECT/resources/views/user/layouts/

# Components:
# From: exports/resources/views/user/components/
# To: YOUR_LARAVEL_PROJECT/resources/views/components/user/
# (Atau: resources/views/user/components/, sesuai preferensi Anda)

# Pages:
# From: exports/resources/views/user/pages/
# To: YOUR_LARAVEL_PROJECT/resources/views/user/pages/
```

### 4. Update composer.json

Di file `composer.json` project Anda, pastikan helper file ter-daftarkan di autoload:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/"
    },
    "files": [
      "app/Helpers/helpers.php"
    ]
  }
}
```

Jika belum ada, tambahkan `"files": ["app/Helpers/helpers.php"]`.

### 5. Merge package.json Dependencies

Ambil dari `build-config/package.json` file ini, pastikan dependency ada di project Anda:

```json
{
  "dependencies": {
    "@fontsource/dm-sans": "^5.0.18",
    "@fontsource/playfair-display": "^5.0.20",
    "alpinejs": "^3.13.5"
  },
  "devDependencies": {
    "tailwindcss-animate": "^1.0.7"
  }
}
```

### 6. Update tailwind.config.js

Pastikan content path mencakup folder user:

```javascript
export default {
    content: [
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
        "./resources/user/**/*.blade.php",  // ← Tambahkan ini
    ],
    // ... rest of config
};
```

Merge theme.extend dari build-config/tailwindcss.js ke existing config Anda.

### 7. Update vite.config.js

Tambahkan input untuk user theme assets:

```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/user/theme/css/user.css",    // ← Tambahkan
                "resources/user/theme/js/user.js",      // ← Tambahkan
            ],
            refresh: true,
        }),
    ],
});
```

### 8. Install & Build

```bash
# Dari root project Anda:
composer install
composer dump-autoload
npm install
npm run dev    # Development dengan HMR
# atau
npm run build  # Production build
```

### 9. Clear Caches & Test

```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan serve
```

Buka di browser: `http://127.0.0.1:8000/user/dashboard` (sesuaikan route Anda)

## 📌 Catatan Penting

### Theme Scoping

File `resources/user/theme/css/user.css` sudah di-scope dengan class `.theme-user`. Pastikan:

1. Layout user (`resources/views/user/layouts/dashboard.blade.php`) punya `class="theme-user"` di `<body>` tag.
2. Hal ini memastikan warna dan styling hanya berlaku di halaman user, bukan di halaman auth/admin.

### Component Namespace

Komponen diatur sesuai struktur:

- `<x-user.components.layout.sidebar />`
- `<x-user.components.shared.portfolio-metrics />`
- `<x-user.components.estimation.cost-range-card />`

Jika Anda ingin struktur berbeda, pastikan update referensi component di setiap Blade file.

### Helper Functions

Tiga helper global tersedia di `app/Helpers/helpers.php`:

- `format_rp($amount)` — Format ke Rupiah: 1500000 → "Rp 1.500.000"
- `format_rp_short($amount)` — Format ringkas: 12400000 → "Rp 12.4M"
- `format_idr_input($raw)` — Format input: "15000000" → "15.000.000"

Gunakan di Blade: `{{ format_rp_short(config('renovasim.projects.0.totalCost')) }}`

### Config Mock Data

File `config/renovasim.php` menyediakan data mock untuk development. Akses di Blade:

- `config('renovasim.projects')` — Array project mock
- `config('renovasim.cities')` — List kota Indonesia
- `config('renovasim.renovation_types')` — Tipe renovasi
- dll

Untuk production, ganti dengan data dari database.

## ✅ Validation Checklist

Setelah implementasi, verifikasi:

- [ ] Helper functions tidak error
- [ ] CSS variable `--primary`, `--secondary`, dll terbaca di element Inspector
- [ ] Sidebar navigasi muncul dan collapse bekerja
- [ ] Tidak ada error component undefined
- [ ] Halaman auth/admin tema-nya tetap normal
- [ ] Hot reload (HMR) berfungsi saat edit CSS/JS
- [ ] Build production tidak error

## 🆘 Troubleshooting

**Error: "Call to undefined function format_rp_short()"**
- Jalankan: `composer dump-autoload`

**Error: "Unable to locate a class or view for component [user.components.layout.sidebar]"**
- Pastikan path blade file benar dan sesuai namespace
- Run: `php artisan view:clear`

**CSS variable tidak terbaca (--primary black, bukan warna yang diharapkan)**
- Pastikan `.theme-user` class ada di `<body>`
- Pastikan tailwind.config.js sudah diupdate dengan `./resources/user/**/*.blade.php`
- Jalankan: `npm run dev` lagi

**Asset tidak ter-load di production**
- Pastikan `vite.config.js` sudah include input user theme
- Jalankan: `npm run build`

---

**Created:** 2026-04-28  
**RenovaSim Version:** Blade + Tailwind  
**Target Laravel:** ^11.0
