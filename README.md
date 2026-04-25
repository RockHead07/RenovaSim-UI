<div align="center">
    <img width="402" alt="Image" src="https://github.com/user-attachments/assets/cbafc851-d99a-44a5-a2e6-c47fea75e266" />
</div>
<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About RenovaSim

This repository contains the **RenovaSim web app**: a **public landing page** plus a **dark-mode Admin Dashboard** for managing core data (Users, Projects, Materials, Pricing Plans, Partners).

While the product vision is a renovation cost estimation platform, this codebase already includes a working Laravel backend structure (routing, controllers, models, migrations) to support the admin panel.

In a nutshell, this project's purpose are:

- Design and present the core idea of RenovaSim
- Build a clean and modern landing page
- Provide an admin interface to manage platform data
- Serve as a foundation before moving into full product development

> [!NOTE]
> Tbh, this project happened because it's related with my college assignment :P.

## Admin Dashboard (Modules)

The admin dashboard lives under the `/admin` routes (protected by `auth` + `admin` middleware) and includes:

- **Users**: manage accounts, roles, and extended profile fields (status, timezone/language, avatar, assigned projects)
- **Projects**: manage projects (room type, area size, etc.)
- **Materials**: manage materials catalog (category, price/unit)
- **Pricing Plans**: manage subscription plans and features
- **Partners**: manage partner list and logos

## UI Consistency (Reusable Admin Form Components)

All Admin **Add/Edit** screens are standardized using reusable Blade components:

- `resources/views/components/admin/form/card.blade.php`
- `resources/views/components/admin/form/errors.blade.php`
- `resources/views/components/admin/form/input.blade.php`
- `resources/views/components/admin/form/textarea.blade.php`
- `resources/views/components/admin/form/select.blade.php`
- `resources/views/components/admin/form/actions.blade.php`

These components enforce consistent dark-theme styling for:

- **Inputs**: border radius, background, focus states, placeholder typography
- **Spacing**: uniform padding/margins between labels and fields
- **Actions**: standardized Save/Update + Cancel button styling/alignment
- **Layout**: consistent container max width and header typography

# Design Overview

<div align="center">
    <img width="720" alt="Image" src="https://github.com/user-attachments/assets/accd54f5-841a-499c-8879-114d52f54015" />
</div>

# Typography & Color Palette
<div align="center">
    <img width="720" alt="Image" src="https://github.com/user-attachments/assets/1e4c8b21-c460-4315-8c85-95fe43c49119" />
</div>

A visual reference sheet defining the core typographic system and color 
palette for consistent brand expression across all design touchpoints.

## Typography

### PP Editorial New — Serif
- **Usage:** Titles & Captions

### PP Neue Montreal — Sans-Serif
- **Usage:** Paragraphs & Body Text

## Color Palette

| Name           | Hex       | Preview |
|----------------|-----------|---------|
| Tech White     | `#F5F5F5` | ⬜      |
| Zen Gray       | `#747473` | 🩶      |
| Coconut Green  | `#8BA023` | 🟩      |
| Thatch Green   | `#3B411E` | 🌿      |
| Metallic Black | `#2C2C2B` | ⬛      |

>[!IMPORTANT]
> This sheet serves as the single source of truth for brand visual language.  
> Always refer to these specifications when designing new assets or interfaces.

## Tech Stack
<p align="left">
  <img src="https://skillicons.dev/icons?i=laravel,php,tailwind,vite,mysql" />
</p>

- Laravel (Blade templating)
- Tailwind CSS
- Vite

## Installation

1. Clone the repository

```bash
git clone https://github.com/RockHead07/RenovaSim-UI.git
cd RenovaSim-UI
```

2. Install dependencies

```bash
composer install
npm install
```

3. Setup environment

```bash
cp .env.example .env 
php artisan key:generate
```

4. Setup database & run migrations

```bash
php artisan migrate
```

5. (Optional) Enable local uploads (avatars/logos)

```bash
php artisan storage:link
```

6. Run development server

```bash
php artisan serve
npm run dev
```

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
