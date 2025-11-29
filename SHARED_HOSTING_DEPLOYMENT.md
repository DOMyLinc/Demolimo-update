# Shared Hosting Deployment Guide for DemoLimo

## Problem Solved ✅

The **403 Forbidden** error you encountered was caused by a missing `index.php` file in the `public` directory. This file is Laravel's main entry point and is essential for the application to work.

**Files Created:**
- ✅ `public/index.php` - Laravel's main entry point
- ✅ `public/.htaccess` - URL rewriting rules for Apache

## Deployment Steps for Shared Hosting

### Step 1: Upload Files

Upload all files to your shared hosting account. Your directory structure should look like this:

```
/services/users/zstorage7p1/radray/www/demolimo.com/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← This is your document root
│   ├── .htaccess
│   ├── index.php    ← NOW EXISTS!
│   ├── build/
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
├── artisan
└── composer.json
```

### Step 2: Configure Document Root

**IMPORTANT:** Your domain's document root MUST point to the `public` directory, NOT the root directory.

In your hosting control panel (cPanel, Plesk, etc.):
1. Go to **Domain Settings** or **Document Root Settings**
2. Set the document root to: `/services/users/zstorage7p1/radray/www/demolimo.com/public`
3. Save the changes

### Step 3: Set Permissions

Set the correct permissions for Laravel:

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

Or via FTP/File Manager:
- `storage/` → 755 (recursive)
- `bootstrap/cache/` → 755 (recursive)

### Step 4: Configure Environment

1. Copy `.env.example` to `.env` (if not already done)
2. Edit `.env` file with your database credentials:

```env
APP_NAME=DemoLimo
APP_ENV=production
APP_DEBUG=false
APP_URL=https://demolimo.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### Step 5: Access the Installer

Once the above steps are complete, visit your domain:

```
https://demolimo.com
```

The application will automatically redirect you to:

```
https://demolimo.com/installer
```

### Step 6: Complete Installation

Follow the web-based installer steps:

1. **Welcome Screen** - Click "Start Installation"
2. **Database Configuration** - Enter your database details
3. **Admin Account** - Create your admin account
4. **Features Selection** - Choose which features to enable
5. **Settings** - Configure basic settings
6. **Complete** - Installation finished!

## Troubleshooting

### Issue: Still Getting 403 Forbidden

**Solution:**
1. Verify `public/index.php` exists
2. Verify `public/.htaccess` exists
3. Check that your document root points to the `public` directory
4. Ensure Apache `mod_rewrite` is enabled (contact your host if needed)

### Issue: 500 Internal Server Error

**Solution:**
1. Check file permissions (storage and bootstrap/cache must be writable)
2. Enable error display temporarily in `.env`:
   ```env
   APP_DEBUG=true
   ```
3. Check error logs in your hosting control panel

### Issue: Database Connection Error

**Solution:**
1. Verify database credentials in `.env`
2. Ensure the database exists
3. Check that the database user has proper permissions
4. Verify the database host (might be `localhost` or an IP address)

### Issue: Missing Vendor Directory

**Solution:**
If you uploaded files without the `vendor` directory, you need to run:
```bash
composer install --no-dev --optimize-autoloader
```

Contact your hosting provider if you don't have SSH access - they may need to run this for you.

## Post-Installation

After successful installation:

1. **Disable Debug Mode** - Set `APP_DEBUG=false` in `.env`
2. **Generate Application Key** (if not already done):
   ```bash
   php artisan key:generate
   ```
3. **Clear Cache**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```
4. **Set Up Cron Jobs** (if needed for scheduled tasks):
   ```
   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
   ```

## Security Checklist

- ✅ Document root points to `public` directory
- ✅ `.env` file is NOT accessible via web browser
- ✅ `APP_DEBUG=false` in production
- ✅ Strong database password
- ✅ Storage directory is writable but not publicly accessible
- ✅ SSL certificate is installed (HTTPS)

## Support

If you continue to experience issues:

1. Check the Laravel log files in `storage/logs/laravel.log`
2. Contact your hosting provider to ensure:
   - PHP 8.1+ is installed
   - Required PHP extensions are enabled
   - `mod_rewrite` is enabled
   - You can set custom document roots

## File Structure Explanation

```
demolimo.com/
├── public/              ← WEB ROOT (document root points here)
│   ├── index.php        ← Entry point for all requests
│   ├── .htaccess        ← URL rewriting rules
│   └── assets/          ← Public assets (CSS, JS, images)
│
├── app/                 ← Application code (NOT accessible via web)
├── config/              ← Configuration files (NOT accessible via web)
├── storage/             ← File storage (NOT accessible via web)
├── .env                 ← Environment config (NOT accessible via web)
└── ...
```

**Why this structure?**
- Only the `public` directory should be web-accessible
- All sensitive files (.env, app code, etc.) are outside the web root
- This is a security best practice for Laravel applications

---

**Created:** November 25, 2025  
**For:** DemoLimo Music Platform  
**Version:** 1.0
