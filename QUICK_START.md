# RenovaSim Quick Start Checklist

## ✅ Step 1: Start MySQL Server
- [ ] Open XAMPP Control Panel
- [ ] Click "Start" button for MySQL (should turn green)
- [ ] Verify connection works

## ✅ Step 2: Create Database
```bash
cd C:\xampp\htdocs\renovasim\RenovaSim-UI
php artisan db:create
```

## ✅ Step 3: Run Migrations
```bash
php artisan migrate --force
```

This will create all tables including the new `is_admin`, `google_id`, and `google_email` columns.

## ✅ Step 4: Seed Database
```bash
php artisan db:seed
```

This will create:
- Admin user: `admin@gmail.com` / `admin123`
- Test user: `test@example.com` / `password`

## ✅ Step 5: Setup Google OAuth (Optional but Recommended)

1. Go to: https://console.developers.google.com
2. Create a new project (or select existing)
3. Go to "Credentials" menu
4. Click "Create Credentials" → "OAuth client ID"
5. Select "Web application"
6. Add Authorized redirect URI:
   ```
   http://localhost/auth/google/callback
   ```
7. Copy the Client ID and Client Secret

## ✅ Step 6: Configure Google OAuth in .env
Edit `.env` file and add:
```env
GOOGLE_CLIENT_ID=your_client_id_from_step_5
GOOGLE_CLIENT_SECRET=your_client_secret_from_step_5
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

## ✅ Step 7: Start Development Server
```bash
# Option 1: Using artisan
php artisan serve

# Option 2: Using npm (if vite configured)
npm run dev
```

## ✅ Step 8: Test the Application

### Test Normal Login:
1. Go to http://localhost:8000
2. Click "Sign Up"
3. Create an account with any username and email
4. Should automatically login and see dashboard
5. Click "Sign In" to logout and test login

### Test Admin Access:
1. Go to http://localhost:8000/login
2. Login with: `admin@gmail.com` / `admin123`
3. After login, you should see "Admin Panel" link in navbar
4. Click it to access admin dashboard

### Test Google OAuth:
1. Click "Sign In with Google" on login/signup page
2. Login with your Google account
3. Should automatically create account or login
4. Redirect to dashboard

### Test User Dashboard:
1. After login, check the dashboard
2. View stats, quick actions, and recent rooms
3. Create a new room to test
4. See it appear in recent rooms

## 📝 Common Issues & Solutions

### Issue: MySQL connection refused
**Solution:**
- Make sure MySQL is running in XAMPP
- Check database name matches `.env`
- Verify credentials in `.env`

### Issue: Migration fails with "table already exists"
**Solution:**
```bash
# If you want to reset everything:
php artisan migrate:fresh --force
php artisan db:seed
```

### Issue: Google OAuth returns error
**Solution:**
- Verify Client ID and Secret are correct in `.env`
- Check redirect URI matches exactly: `http://localhost/auth/google/callback`
- Make sure app is running on `localhost:8000`

### Issue: Dashboard shows empty
**Solution:**
- Create at least one room by clicking "Create New Room"
- Recent rooms will appear after creation

## 🎯 Key Features Implemented

✅ Database migrated to MySQL
✅ Users auto-login after signup → Dashboard
✅ User dashboard with stats and rooms
✅ Admin-only access (admin@gmail.com / admin123)
✅ Google OAuth sign in/signup
✅ Admin middleware protection
✅ Enhanced navigation bar

## 📚 Documentation

- `SETUP_GUIDE.md` - Detailed setup instructions
- `IMPLEMENTATION_SUMMARY.md` - Full implementation details
- `README.md` - Project overview

## 🆘 Need Help?

1. Check the error messages in terminal
2. Review `SETUP_GUIDE.md` for detailed instructions
3. Check `IMPLEMENTATION_SUMMARY.md` for architecture
4. Verify all `.env` variables are set correctly
