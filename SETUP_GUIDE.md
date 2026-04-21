# RenovaSim - Setup Instructions

## 1. Start XAMPP Services

Before running migrations, you must start MySQL and Apache:

### Windows:
1. Open XAMPP Control Panel (C:\xampp\xampp-control.exe)
2. Click "Start" next to MySQL
3. Wait for it to turn green (running)

OR use PowerShell as Admin:
```powershell
cd C:\xampp
.\mysql\bin\mysqld.exe
```

## 2. Create Database

Once MySQL is running:
```bash
cd C:\xampp\htdocs\renovasim\RenovaSim-UI

# Create the renovasim database
php artisan db:create
```

## 3. Run Migrations

```bash
php artisan migrate --force
```

## 4. Seed Admin User

```bash
php artisan db:seed
```

This will create:
- Admin user: `admin@gmail.com` / `admin123`
- Test user: `test@example.com` / `password`

## 5. Setup Google OAuth

To enable Google Sign In:

1. Go to: https://console.developers.google.com
2. Create a new project
3. Enable "Google+ API"
4. Create OAuth 2.0 credentials (Web application)
5. Add authorized redirect URI: `http://localhost/auth/google/callback`
6. Copy Client ID and Client Secret

## 6. Configure Google OAuth in .env

```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

## 7. Run Dev Server

```bash
php artisan serve
```

Or use the npm dev script:
```bash
npm run dev
```

## Database Changes Made

✅ Migrated to MySQL database
✅ Added `is_admin` column to users table
✅ Added Google OAuth columns: `google_id`, `google_email`

## Features Implemented

✅ User can sign up and automatically login to dashboard
✅ User dashboard page with stats and recent rooms
✅ Admin access restricted to `admin@gmail.com` with password `admin123`
✅ Google OAuth login/signup integration
✅ User role-based access control

## Routes

- `/dashboard` - User dashboard (requires authentication)
- `/auth/google` - Google OAuth redirect
- `/auth/google/callback` - Google OAuth callback
- `/admin/dashboard` - Admin panel (requires admin role)
- `/rooms` - User's rooms list
- `/room/create` - Create new room
- `/room/{id}/editor` - Room 3D editor

## Credentials

### Admin Account (for testing):
- Email: `admin@gmail.com`
- Password: `admin123`

### Test User Account:
- Email: `test@example.com`
- Password: `password`
