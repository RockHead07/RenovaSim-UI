<div align="center">
<img width="1524" height="492" alt="Image" src="https://github.com/user-attachments/assets/2eda46a2-212f-4f42-8ff3-39d6ac0c02f9" />
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

This project idea purpose, is to make an **Web Platform** named **RenovaSim**. However, on this repo, I'll only focus on building the **RenovaSim UI** as a landing page prototype for a web-based renovation cost estimation platform. 

Therefore, this repository focuses **only on the user interface (UI)** and visual presentation of the product idea. It does not include the propper backend logic, database integration, or actual cost calculation features (yet). In a nutshell, this project purpose are:

- Design and present the core idea of RenovaSim
- Build a clean and modern landing page
- Translate product concepts into a visual experience
- Serve as a foundation berfore moving into full dev

> [!NOTE]
> Tbh, this project happened because it's related with my college assignment :P.

<div align="center">
<img width="1901" height="926" alt="Image" src="https://github.com/user-attachments/assets/c0030d02-0eba-40df-9475-cc16e5e0e76b" />
</div>

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

4. Run development server

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
