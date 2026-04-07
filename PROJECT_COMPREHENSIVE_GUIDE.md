# RenovaSim-UI - Comprehensive Project Guide

## 📖 Project Overview

**RenovaSim-UI** is a modern web-based design system and prototype for **RenovaSim**, an AI-powered renovation cost estimation platform. This is a Laravel-based full-stack application that combines UI design, authentication, and backend infrastructure for managing renovation projects, pricing plans, materials, and partners.

**Purpose:** 
- Create a landing page and user authentication system for RenovaSim
- Establish an admin dashboard for managing platform data
- Build the foundation for AI-powered cost estimation features
- Demonstrate the product concept through clean UI/UX design

**Status:** Foundation-phase project with auth system and data models ready; cost calculation engine pending

---

## 🛠 Technology Stack

| Technology | Version | Purpose |
|-----------|---------|---------|
| **Laravel** | 13.0 | Backend framework & MVC routing |
| **PHP** | 8.3+ | Server-side language |
| **Blade** | Native | Template engine for HTML rendering |
| **Tailwind CSS** | 4.2.2 | Utility-first CSS framework |
| **Vite** | 8.0.0 | Module bundler & dev server |
| **MySQL** | 8.0+ | Relational database (configured, ready for integration) |
| **Alpine.js** | (via CDN) | Interactive frontend components (show/hide toggling) |
| **Composer** | 2.x | PHP package manager |
| **NPM** | Latest | Node package manager (JavaScript dependencies) |

---

## 📁 Complete Directory Structure

```
RenovaSim-UI/
│
├── 📄 Core Configuration Files
│   ├── artisan                           # Laravel CLI tool
│   ├── composer.json                     # PHP dependencies manifest
│   ├── package.json                      # Node.js dependencies manifest
│   ├── vite.config.js                    # Vite build configuration
│   ├── tailwind.config.js                # Tailwind CSS configuration
│   ├── phpunit.xml                       # PHPUnit testing configuration
│   └── .env / .env.example               # Environment variables
│
├── 📂 app/                               # Application logic
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php        # Login/Register/Logout logic
│   │       ├── NewsletterController.php  # Newsletter subscription
│   │       └── Admin/                    # Admin-only controllers
│   │           ├── PartnerController.php
│   │           ├── PricingPlanController.php
│   │           ├── MaterialController.php
│   │           └── ProjectController.php
│   │
│   ├── Models/                           # Eloquent ORM Models
│   │   ├── User.php                      # User model (extends Authenticatable)
│   │   ├── Project.php                   # Renovation project data
│   │   ├── Material.php                  # Building materials catalog
│   │   ├── PricingPlan.php               # SaaS pricing tier plans
│   │   ├── PlanFeature.php               # Features within pricing plans
│   │   ├── Partner.php                   # Contractor/vendor partners
│   │   └── ProjectMaterial.php           # Junction table (many-to-many)
│   │
│   └── Providers/
│       └── AppServiceProvider.php        # Service provider registration
│
├── 📂 bootstrap/                         # Application bootstrap files
│   ├── app.php                           # Service container setup
│   ├── providers.php                     # Auto-register providers
│   └── cache/
│       ├── packages.php
│       └── services.php
│
├── 📂 config/                            # Configuration files
│   ├── app.php                           # App name, timezone, providers
│   ├── auth.php                          # Authentication guards
│   ├── cache.php                         # Cache driver settings
│   ├── database.php                      # Database connections
│   ├── filesystems.php                   # Storage configuration
│   ├── logging.php                       # Log channels
│   ├── mail.php                          # Email configuration
│   ├── queue.php                         # Job queue settings
│   ├── services.php                      # Third-party service credentials
│   └── session.php                       # Session configuration
│
├── 📂 database/
│   │
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_04_04_044900_create_partners_table.php
│   │   ├── 2026_04_04_044940_create_pricing_plans_table.php
│   │   ├── 2026_04_04_044945_create_materials_table.php
│   │   ├── 2026_04_04_044952_create_projects_table.php
│   │   ├── 2026_04_04_044956_create_project_materials_table.php
│   │   └── 2026_04_04_045003_create_plan_features_table.php
│   │
│   ├── factories/
│   │   └── UserFactory.php              # Factory for generating fake users
│   │
│   └── seeders/
│       └── DatabaseSeeder.php           # Database seeding orchestrator
│
├── 📂 resources/
│   │
│   ├── views/                           # Blade template files
│   │   │
│   │   ├── welcome.blade.php            # Landing page (main entry point)
│   │   ├── navbar.blade.php             # Navigation component
│   │   │
│   │   ├── auth/                        # Authentication pages
│   │   │   ├── signin.blade.php         # Login form
│   │   │   └── signup.blade.php         # Registration form
│   │   │
│   │   ├── components/                  # Reusable Blade components
│   │   │   ├── features.blade.php       # Features showcase section
│   │   │   ├── pricing.blade.php        # Pricing plans display
│   │   │   ├── faq.blade.php            # FAQ section
│   │   │   ├── footer.blade.php         # Footer component
│   │   │   ├── partners-carousel.blade.php # Partner logos carousel
│   │   │   └── about.blade.php          # About section
│   │   │
│   │   └── admin/                       # Admin dashboard pages
│   │       ├── layout.blade.php         # Admin layout wrapper
│   │       ├── dashboard.blade.php      # Admin dashboard home
│   │       ├── materials/               # Material CRUD views
│   │       ├── partners/                # Partner CRUD views
│   │       ├── pricing-plans/           # Pricing plan CRUD views
│   │       └── projects/                # Project CRUD views
│   │
│   ├── css/
│   │   ├── app.css                      # Main application styles
│   │   └── navbar.css                   # Navbar-specific styles
│   │
│   ├── js/
│   │   ├── app.js                       # Main JavaScript entry point
│   │   ├── bootstrap.js                 # Bootstrap axios & Alpine
│   │   └── cursor.js                    # Custom cursor effect
│   │
│   └── (compiled by Vite to public/build/)
│
├── 📂 routes/
│   ├── web.php                          # HTTP route definitions
│   └── console.php                      # Artisan command definitions
│
├── 📂 public/                            # Publicly accessible files
│   ├── index.php                        # Application entry point
│   ├── robots.txt                       # Search engine directives
│   ├── images/
│   │   ├── logo.svg                     # RenovaSim logo
│   │   ├── phone-mockup.png             # Landing page hero image
│   │   ├── features/                    # Feature showcase images
│   │   └── partners/                    # Partner logo images
│   ├── font/
│   │   ├── PP-Editorial-New/            # Custom font files
│   │   └── PP-Neue-Montreald/           # Custom font files
│   └── build/                           # Vite-compiled assets (ignored in source)
│
├── 📂 storage/
│   ├── app/
│   │   ├── private/                     # Private file storage
│   │   └── public/                      # Public file storage (symlink to public/)
│   ├── framework/
│   │   ├── cache/                       # Laravel cache files
│   │   ├── sessions/                    # Session storage
│   │   ├── testing/                     # Testing temporary files
│   │   └── views/                       # Compiled blade views
│   └── logs/                            # Application logs
│
├── 📂 tests/
│   ├── TestCase.php                     # Base test class
│   ├── Feature/
│   │   └── ExampleTest.php              # Example feature test
│   └── Unit/
│       └── ExampleTest.php              # Example unit test
│
├── 📂 bootstrap/cache/                  # Bootstrap cache (auto-generated)
├── 📂 vendor/                           # Composer packages (auto-generated)
├── 📂 node_modules/                     # NPM packages (auto-generated)
├── 📂 .git/                             # Git repository files
│
├── 📄 Documentation
│   ├── README.md                        # Quick start guide
│   ├── PROJECT_EXPLANATION.md           # Indonesian project docs
│   └── LICENSE                          # MIT License
│
└── 📄 Dotfiles
    ├── .env                             # Local environment variables (DO NOT COMMIT)
    ├── .env.example                     # Template for .env
    ├── .gitignore                       # Git ignore rules
    ├── .gitattributes                   # Git attributes
    └── .editorconfig                    # Editor configuration
```

---

## 🗄 Database Schema

### Tables Overview

```
users
├── id (PK)
├── name VARCHAR
├── email VARCHAR UNIQUE
├── password VARCHAR (hashed)
├── email_verified_at TIMESTAMP
├── remember_token VARCHAR
├── timestamps (created_at, updated_at)

partners
├── id (PK)
├── name VARCHAR
├── description TEXT
├── logo_url VARCHAR
├── website_url VARCHAR
├── contact_email VARCHAR
├── timestamps

pricing_plans
├── id (PK)
├── name VARCHAR
├── description TEXT
├── price DECIMAL(8,2)
├── is_popular BOOLEAN
├── is_active BOOLEAN
├── timestamps

plan_features (Belongs to pricing_plans)
├── id (PK)
├── pricing_plan_id FK
├── feature_name VARCHAR
├── timestamps

materials
├── id (PK)
├── name VARCHAR
├── description TEXT
├── price_per_unit DECIMAL(8,2)
├── unit_type VARCHAR (e.g., 'sqm', 'pcs', 'liter')
├── timestamps

projects (Belongs to User)
├── id (PK)
├── user_id FK
├── name VARCHAR
├── room_type VARCHAR
├── area_size DECIMAL(5,2)
├── total_cost DECIMAL(10,2)
├── status VARCHAR (e.g., 'draft', 'processing', 'completed')
├── timestamps

project_materials (Junction table: Projects ↔ Materials)
├── id (PK)
├── project_id FK
├── material_id FK
├── quantity DECIMAL(8,2)
├── subtotal DECIMAL(10,2)
├── timestamps
```

### Model Relationships

```
User
├── posts (not implemented yet)
└── projects (1-to-many)
    └── materials (many-to-many through project_materials)

Project
├── user (belongs-to)
└── materials (many-to-many)

Material
└── projects (many-to-many)

PricingPlan
└── features (1-to-many)

PlanFeature
└── pricing_plan (belongs-to)

Partner
└── (standalone, used for reference)
```

---

## 🔑 Authentication System

### Routes (Auth-Related)

```
GET  /login              → AuthController@showLogin    # Show login form
POST /login              → AuthController@login         # Process login
GET  /register           → AuthController@showRegister  # Show registration form
POST /register           → AuthController@register      # Process registration
POST /logout             → AuthController@logout        # Destroy session
```

### Admin Routes (Protected with 'auth' middleware)

```
GET  /admin                           → Show admin dashboard
GET  /admin/partners                  → List partners
POST /admin/partners                  → Create partner
GET  /admin/partners/{id}             → Show partner details
PUT  /admin/partners/{id}             → Update partner
DELETE /admin/partners/{id}           → Delete partner

GET  /admin/pricing-plans             → List pricing plans
POST /admin/pricing-plans             → Create plan
GET  /admin/pricing-plans/{id}        → Show plan
PUT  /admin/pricing-plans/{id}        → Update plan
DELETE /admin/pricing-plans/{id}      → Delete plan

GET  /admin/materials                 → List materials
POST /admin/materials                 → Create material
GET  /admin/materials/{id}            → Show material
PUT  /admin/materials/{id}            → Update material
DELETE /admin/materials/{id}          → Delete material

GET  /admin/projects                  → List projects
POST /admin/projects                  → Create project
GET  /admin/projects/{id}             → Show project
PUT  /admin/projects/{id}             → Update project
DELETE /admin/projects/{id}           → Delete project
```

---

## 🎨 UI Components (Blade)

### Landing Page Components
- **navbar.blade.php** - Navigation bar with links
- **features.blade.php** - Showcase of 3 core features
- **pricing.blade.php** - Pricing plans section
- **faq.blade.php** - Frequently asked questions
- **footer.blade.php** - Footer with links
- **partners-carousel.blade.php** - Partner logos
- **about.blade.php** - About section

### Auth Pages
- **signin.blade.php** - Login form with email/password
- **signup.blade.php** - Registration form with full name

### Admin Pages
- **admin/layout.blade.php** - Base layout for admin pages
- **admin/dashboard.blade.php** - Admin dashboard overview
- **admin/materials/** - Material management CRUD
- **admin/partners/** - Partner management CRUD
- **admin/pricing-plans/** - Pricing plan management CRUD
- **admin/projects/** - Project management CRUD

---

## 🚀 Running the Project

### Prerequisites
```bash
- PHP 8.3+
- Composer 2.x
- Node.js 18+
- npm or yarn
- MySQL 8.0+
```

### Setup Instructions

```bash
# 1. Clone repository
git clone https://github.com/RockHead07/RenovaSim-UI.git
cd RenovaSim-UI

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database
# Edit .env and set:
# DB_HOST=localhost
# DB_DATABASE=renovasim_ui
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Run migrations
php artisan migrate

# 6. Start development server
php artisan serve       # Runs on http://localhost:8000
npm run dev            # Runs Vite dev server

# 7. Access the app
# Landing page: http://localhost:8000
# Login: http://localhost:8000/login
# Admin: http://localhost:8000/admin (after login)
```

### Useful Artisan Commands

```bash
php artisan migrate              # Run pending migrations
php artisan migrate:rollback    # Rollback last migration batch
php artisan db:seed             # Seed database with fake data
php artisan tinker              # Interactive PHP shell
php artisan test                # Run PHPUnit tests
php artisan key:generate        # Generate APP_KEY
php artisan config:cache        # Cache config files
php artisan route:list          # Show all routes
```

---

## 🎯 Key Features Overview

### 1. **Landing Page**
- Modern, responsive design
- Hero section with key value proposition
- Features showcase (3 core features)
- Pricing plans display
- FAQ section
- Partner/social proof section
- Newsletter subscription
- Call-to-action buttons

### 2. **Authentication System**
- User registration with validation
- Email/password login
- Session-based authentication
- Logout functionality
- Password hashing (bcrypt)
- Remember me option
- Admin middleware protection

### 3. **Admin Dashboard**
- Protected CRUD operations for:
  - Materials (building materials catalog)
  - Pricing Plans (subscription/pricing tiers)
  - Partners (contractor/vendor management)
  - Projects (user renovation projects)
- Dashboard overview page
- RESTful API endpoints

### 4. **Data Models**
- User management
- Project management with material tracking
- Material cost tracking
- Multi-tier pricing plans with features
- Partner/vendor catalog
- Many-to-many relationship handling

### 5. **Frontend Features**
- Responsive Tailwind CSS design
- Custom cursor effect
- Alpine.js interactivity
- Form validation
- Newsletter subscription integration

---

## 📊 Development Workflow

### Code Organization

**Controllers** (`app/Http/Controllers/`)
- `AuthController` - Handles login/register/logout
- `NewsletterController` - Handles newsletter subscription
- `Admin/PartnerController` - CRUD for partners
- `Admin/MaterialController` - CRUD for materials
- `Admin/PricingPlanController` - CRUD for pricing plans
- `Admin/ProjectController` - CRUD for projects

**Models** (`app/Models/`)
- Each model corresponds to a database table
- Uses Eloquent ORM for database interactions
- Contains relationships and casting rules

**Views** (`resources/views/`)
- Blade templates (`.blade.php`) for rendering HTML
- Components for reusability
- Admin views for CRUD operations

**Routes** (`routes/web.php`)
- Public routes: landing page, auth pages
- Admin routes: protected with 'auth' middleware
- Resource routes for CRUD operations

### Build Process

**Vite** handles:
- Bundling JavaScript modules
- CSS processing (Tailwind)
- Hot module replacement during development
- Minification for production

**Tailwind CSS**:
- Utility-first CSS framework
- Configured in `tailwind.config.js`
- Compiled during build process

---

## 🔐 Security Features

- Password hashing (bcrypt)
- CSRF token protection on forms
- SQL injection prevention (Eloquent ORM)
- Session-based authentication
- Admin middleware for route protection
- Input validation (Laravel validators)
- XSS protection through Blade escaping

---

## 📝 Recent Changes & Customization

### UI Size Reductions (25% Smaller)
- Sign-in page (`signin.blade.php`): Reduced font sizes, padding, spacing
- Sign-up page (`signup.blade.php`): Applied same 25% reduction
- Form inputs: `py-3 → py-2`, `px-5 → px-4`, `text-sm → text-xs`
- Headings: `text-4xl → text-3xl`
- Social buttons: Icon size `w-5 h-5 → w-4 h-4`

---

## 🛠 For AI Developers (Claude, Gpt-4, etc.)

### How to Understand This Project Quickly

1. **Entry Point**: `resources/views/welcome.blade.php` - Main landing page
2. **Database**: Check migrations in `database/migrations/` for table structure
3. **Models**: Look at `app/Models/` for ORM relationships
4. **Controllers**: `app/Http/Controllers/` contains all business logic
5. **Routes**: `routes/web.php` maps URLs to controller actions
6. **Config**: `config/` directory contains all settings

### Quick Navigation

| What You Need | Where to Find |
|---------------|---------------|
| Add new database table | Create migration in `database/migrations/` |
| Add new page | Create new view in `resources/views/` |
| Add new API endpoint | Add route in `routes/web.php` & controller method |
| Style changes | Edit views (Blade) or CSS in `resources/css/` |
| Add form validation | Update controller request validation |
| Database relationships | Define in Model files using Eloquent methods |

### Common Tasks

**Add a new Model with CRUD:**
```bash
php artisan make:model YourModel -m -c
```
This creates model, migration, and controller.

**Generate authentication scaffolding:**
```bash
php artisan ui:auth
```

**Run tests:**
```bash
php artisan test
```

---

## 📚 Documentation Files

- **README.md** - Quick start guide
- **PROJECT_EXPLANATION.md** - Original Indonesian documentation
- **PROJECT_COMPREHENSIVE_GUIDE.md** - This file (comprehensive overview)

---

## ⚖️ License

MIT License - See [LICENSE](LICENSE) file for details

---

**Created:** 2026  
**Current Phase:** Foundation/MVP  
**Next Steps:** Cost estimation engine, frontend form interactions, API integration
