# RenovaSim-UI - Project Documentation

## 📋 Ringkasan Proyek

**RenovaSim-UI** adalah prototype landing page untuk platform web bernama **RenovaSim**. Project ini merupakan bagian dari assignment kuliah yang fokus pada desain dan presentasi UI/UX dari sebuah produk estimasi biaya renovasi berbasis web.

### Tujuan Proyek:
- Mendesain dan mempresentasikan konsep bisnis RenovaSim
- Membangun landing page yang modern dan clean
- Mengubah konsep produk menjadi pengalaman visual yang menarik
- Menjadi foundation sebelum development backend penuh

---

## 🎯 Fitur Utama

Platform ini menawarkan 3 fitur core untuk pengguna:

1. **Cost Estimation Engine** 
   - Memperkirakan biaya renovasi berdasarkan luas area, tipe pekerjaan, dan kebutuhan material

2. **Detailed Cost Breakdown**
   - Menampilkan breakdown biaya secara detail dan terstruktur
   - Pengguna bisa melihat kemana budget mereka digunakan

3. **Design Inspiration**
   - Galeri visual referensi desain
   - Membantu pengguna memutuskan style dan arah renovasi mereka

---

## 💻 Tech Stack

| Teknologi | Fungsi |
|-----------|--------|
| **Laravel 13** | Backend framework & templating engine |
| **PHP 8.3** | Server-side programming language |
| **Blade** | Template engine untuk rendering views |
| **Tailwind CSS** | CSS framework untuk styling |
| **Vite** | Build tool & dev server yang modern |
| **MySQL** | Database (setup ready, belum fully integrated) |
| **N/A** | Belum ada backend logic/API |

---

## 📁 Struktur Proyek

```
RenovaSim-UI/
├── app/
│   ├── Http/Controllers/          # Controller (masih kosong)
│   ├── Models/                    # Database models
│   └── Providers/
├── resources/
│   ├── views/
│   │   ├── welcome.blade.php      # Main landing page
│   │   ├── faq.blade.php          # FAQ section (dihapus duplikatnya)
│   │   └── components/            # Reusable Blade components
│   │       ├── features.blade.php # Features showcase
│   │       ├── pricing.blade.php  # Pricing section
│   │       └── faq.blade.php      # FAQ component
│   ├── css/
│   │   └── app.css                # Custom styles
│   └── js/
│       ├── app.js                 # Main JS file
│       ├── bootstrap.js           # Bootstrap file
│       └── cursor.js              # Custom cursor effect
├── public/
│   ├── images/
│   │   ├── features/              # Feature images
│   │   └── partners/              # Partner logos
│   └── font/
│       ├── PP-Editorial-New/
│       └── PP-Neue-Montreald/
├── routes/
│   └── web.php                    # Route definitions
├── config/                        # Configuration files
├── database/                      # Migrations & seeders
├── vite.config.js                 # Vite configuration
├── tailwind.config.js             # Tailwind configuration
└── package.json                   # NPM dependencies
```

---

## 🚀 Cara Menjalankan

### Prerequisites:
- PHP 8.3+
- Node.js & npm
- Composer
- MySQL (optional)

### Setup Steps:

```bash
# 1. Clone repository
git clone <repository-url>
cd RenovaSim-UI

# 2. Install PHP dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Install frontend dependencies
npm install

# 5. Run development server
npm run dev

# 6. Di terminal lain, jalankan Laravel server
php artisan serve
```

Akses di: http://localhost:8000

---

## 🎨 Desain & UX

Aspek desain yang diimplementasikan:

- **Color Scheme**: Custom color palette dari Tailwind config
- **Typography**: Sans-serif moderna dengan serif accents untuk headers
- **Layout**: Responsive design (mobile-first approach)
- **Components**: Reusable Blade components untuk maintainability
- **Interactivity**: 
  - Custom cursor effect
  - FAQ accordion toggle
  - Smooth transitions

---

## 🔧 Features Implementasi

✅ **Completed:**
- Landing page design
- Features showcase
- FAQ section dengan toggle functionality
- Pricing section (preview)
- Responsive layout
- Custom styling
- Component-based architecture

❌ **Not Yet Implemented:**
- Backend API & business logic
- Database integration (models ready, no logic)
- User authentication
- Actual cost calculation engine
- Payment processing
- Admin panel

---

## 📊 Current Status

Proyek saat ini berada di tahap **UI/UX Prototype**. Fokus adalah:
- Visual design & user experience
- Frontend implementation
- Component structure

Backend logic dan database integration akan dikembangkan pada fase selanjutnya (future development).

---

## ✏️ Catatan Teknis

- **Blade Templating**: Digunakan untuk template reusable
- **Component Pattern**: Mengikuti Laravel component best practices
- **Tailwind CSS**: Utility-first CSS framework untuk rapid development
- **Vite**: Modern bundler untuk fast HMR (Hot Module Replacement) di development
- **No External APIs**: Semua data saat ini static/mock data

---

## 🎓 Learning Outcomes

Melalui project ini, telah dipelajari:
- Laravel framework fundamentals
- Blade template engine
- Tailwind CSS & responsive design
- Component-based architecture
- Vite build tool
- Web design principles
- UX/UI best practices

---

## 📝 Kesimpulan

**RenovaSim-UI** adalah sebuah prototype landing page yang mendemonstrasikan konsep platform estimasi biaya renovasi. Proyek ini fokus pada aspek presentasi visual dan user experience, dengan foundation yang siap untuk pengembangan backend di masa depan.

