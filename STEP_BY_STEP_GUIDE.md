# 🎵 DemoLimo - Complete Installation Guide
## Step-by-Step Instructions for Shared Hosting

---

## 📋 **Table of Contents**

1. [Before You Start](#before-you-start)
2. [Step 1: Prepare Your Files](#step-1-prepare-your-files)
3. [Step 2: Upload to Shared Hosting](#step-2-upload-to-shared-hosting)
4. [Step 3: Set File Permissions](#step-3-set-file-permissions)
5. [Step 4: Configure Database](#step-4-configure-database)
6. [Step 5: Test Your Server](#step-5-test-your-server)
7. [Step 6: Run the Installer](#step-6-run-the-installer)
8. [Step 7: Post-Installation Setup](#step-7-post-installation-setup)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 **Before You Start**

### What You Need:
- ✅ FTP client (FileZilla, WinSCP) or cPanel File Manager access
- ✅ Your hosting account credentials
- ✅ MySQL database credentials (host, name, username, password)
- ✅ All DemoLimo files on your computer
- ✅ About 30 minutes of time

### Important Files to Check:
Make sure these files exist in your DemoLimo folder:
```
demolimo/
├── index.php          ← Must exist (I created this)
├── .htaccess          ← Must exist (I updated this)
├── public/
│   ├── index.php      ← Must exist (I created this)
│   ├── .htaccess      ← Must exist (I created this)
│   └── test.php       ← Must exist (I created this)
├── .env               ← Must exist
├── vendor/            ← Must exist (Composer dependencies)
└── storage/           ← Must exist
```

> **⚠️ IMPORTANT**: If `vendor/` folder is missing, you need to run `composer install` first!

---

## 📦 **Step 1: Prepare Your Files**

### 1.1 Verify All Files Are Present

Open your `demolimo` folder on your computer and verify:

- [ ] `index.php` exists in root folder
- [ ] `.htaccess` exists in root folder
- [ ] `public/index.php` exists
- [ ] `public/.htaccess` exists
- [ ] `public/test.php` exists
- [ ] `.env` file exists
- [ ] `vendor/` folder exists and has files inside

### 1.2 Configure Your .env File

**BEFORE uploading**, open `.env` file and update these settings:

```env
# Application Settings
APP_NAME=DemoLimo
APP_ENV=production
APP_DEBUG=false
APP_URL=https://demolimo.com

# Database Settings (GET THESE FROM YOUR HOSTING PROVIDER)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name_here
DB_USERNAME=your_database_username_here
DB_PASSWORD=your_database_password_here
```

> **💡 TIP**: Keep `APP_DEBUG=true` during installation, then change to `false` after success.

### 1.3 Create a Checklist

Print or write down:
- [ ] Database Name: ________________
- [ ] Database Username: ________________
- [ ] Database Password: ________________
- [ ] Database Host: ________________ (usually `localhost`)

---

## 🚀 **Step 2: Upload to Shared Hosting**

### Option A: Using FTP Client (FileZilla)

#### 2.1 Connect to Your Server

1. Open FileZilla (or your FTP client)
2. Enter your FTP credentials:
   - **Host**: ftp.demolimo.com (or your FTP host)
   - **Username**: Your FTP username
   - **Password**: Your FTP password
   - **Port**: 21 (or 22 for SFTP)
3. Click **"Quickconnect"**

#### 2.2 Navigate to Your Website Directory

In the **Remote Site** panel (right side):
```
Navigate to: /services/users/zstorage7p1/radray/www/demolimo.com/
```

Or it might be:
```
/public_html/
/www/
/htdocs/
```

#### 2.3 Upload All Files

1. In the **Local Site** panel (left side), navigate to your `demolimo` folder
2. Select **ALL** files and folders
3. Right-click → **Upload**
4. Wait for upload to complete (this may take 10-30 minutes)

> **⚠️ IMPORTANT**: Make sure hidden files are uploaded too (`.env`, `.htaccess`)!
> In FileZilla: Server → Force showing hidden files

### Option B: Using cPanel File Manager

#### 2.1 Access File Manager

1. Log in to your cPanel
2. Find and click **"File Manager"**
3. Navigate to your website directory (usually `public_html` or `www`)

#### 2.2 Upload Files

1. Click **"Upload"** button
2. Select all files from your `demolimo` folder
3. Wait for upload to complete

#### 2.3 Extract (if you uploaded a ZIP)

1. If you uploaded a ZIP file, right-click it
2. Select **"Extract"**
3. Extract to current directory
4. Delete the ZIP file after extraction

---

## 🔐 **Step 3: Set File Permissions**

### 3.1 Using FTP Client

Right-click on each folder below and select **"File Permissions"**:

| Folder | Permission | Numeric |
|--------|-----------|---------|
| `storage/` | Read, Write, Execute (recursive) | 755 |
| `bootstrap/cache/` | Read, Write, Execute (recursive) | 755 |

**How to set permissions in FileZilla:**
1. Right-click folder → **File Permissions**
2. Check: ✅ Read, ✅ Write, ✅ Execute for Owner
3. Check: ✅ Read, ✅ Execute for Group
4. Check: ✅ Read, ✅ Execute for Public
5. Check: ✅ **Recurse into subdirectories**
6. Click **OK**

### 3.2 Using cPanel File Manager

1. Navigate to each folder
2. Right-click → **Change Permissions**
3. Set to **755**
4. Check **"Change permissions recursively"**
5. Click **Change Permissions**

### 3.3 Using SSH (if available)

```bash
cd /path/to/demolimo.com/
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

## 🗄️ **Step 4: Configure Database**

### 4.1 Create Database (if not already created)

**Using cPanel:**

1. Go to cPanel → **MySQL Databases**
2. Under **"Create New Database"**:
   - Database Name: `demolimo_db` (or your choice)
   - Click **"Create Database"**
3. Under **"Add New User"**:
   - Username: `demolimo_user` (or your choice)
   - Password: Generate a strong password
   - Click **"Create User"**
4. Under **"Add User to Database"**:
   - Select your user and database
   - Click **"Add"**
   - Grant **ALL PRIVILEGES**
   - Click **"Make Changes"**

### 4.2 Note Your Database Credentials

Write down:
```
Database Name: demolimo_db
Database User: demolimo_user
Database Password: [your generated password]
Database Host: localhost
```

### 4.3 Update .env File on Server

**Using File Manager:**
1. Navigate to your website root
2. Right-click `.env` → **Edit**
3. Update the database section:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=demolimo_db
DB_USERNAME=demolimo_user
DB_PASSWORD=your_password_here
```
4. **Save Changes**

**Using FTP:**
1. Download `.env` file
2. Edit it on your computer
3. Upload it back (overwrite)

---

## 🧪 **Step 5: Test Your Server**

### 5.1 Access the Test Page

Open your web browser and visit:
```
https://demolimo.com/test.php
```

### 5.2 Check Test Results

You should see a page with test results. Look for:

| Test | Expected Result |
|------|----------------|
| PHP Version | ✅ OK (8.1 or higher) |
| OpenSSL Extension | ✅ OK |
| PDO Extension | ✅ OK |
| Mbstring Extension | ✅ OK |
| Tokenizer Extension | ✅ OK |
| XML Extension | ✅ OK |
| Ctype Extension | ✅ OK |
| JSON Extension | ✅ OK |
| BCMath Extension | ✅ OK |
| Fileinfo Extension | ✅ OK |
| GD Extension | ✅ OK |
| storage Directory | ✅ OK (Writable) |
| bootstrap/cache Directory | ✅ OK (Writable) |
| .env File | ✅ OK (Exists) |
| Composer Dependencies | ✅ OK (Installed) |

### 5.3 What If Tests Fail?

**If PHP Version is too old:**
- Contact your hosting provider to upgrade to PHP 8.1+
- Or change PHP version in cPanel → **Select PHP Version**

**If Extensions are missing:**
- Contact your hosting provider to enable them
- Or enable in cPanel → **Select PHP Version** → **Extensions**

**If Directories are not writable:**
- Go back to [Step 3](#step-3-set-file-permissions) and fix permissions

**If Composer Dependencies are missing:**
- You need to run `composer install` on your server
- Contact your hosting provider for help

---

## 🎨 **Step 6: Run the Installer**

### 6.1 Access the Installer

Visit your website:
```
https://demolimo.com
```

You should be automatically redirected to:
```
https://demolimo.com/installer
```

### 6.2 Installation Wizard

Follow the on-screen instructions:

#### **Screen 1: Welcome**
- Read the welcome message
- Click **"Start Installation"** or **"Next"**

#### **Screen 2: Database Configuration**
Enter your database details:
- **Database Type**: MySQL
- **Database Host**: `localhost` (or your DB host)
- **Database Port**: `3306`
- **Database Name**: `demolimo_db` (your database name)
- **Database Username**: `demolimo_user` (your DB username)
- **Database Password**: Your database password
- Click **"Test Connection"** (if available)
- Click **"Next"** or **"Continue"**

#### **Screen 3: Admin Account**
Create your admin account:
- **Name**: Your full name
- **Email**: your-email@example.com
- **Username**: admin (or your choice)
- **Password**: Create a strong password
- **Confirm Password**: Re-enter password
- Click **"Next"** or **"Create Admin"**

#### **Screen 4: Features Selection**
Choose which features to enable:
- ✅ Check features you want enabled
- ⬜ Uncheck features you don't need
- You can change these later in admin panel
- Click **"Next"** or **"Continue"**

#### **Screen 5: Basic Settings**
Configure basic settings:
- **Site Name**: DemoLimo
- **Site URL**: https://demolimo.com
- **Timezone**: Your timezone
- **Currency**: USD (or your currency)
- **Theme**: Choose your default theme
- Click **"Install"** or **"Finish Installation"**

### 6.3 Wait for Installation

The installer will:
1. ✅ Create database tables
2. ✅ Seed initial data
3. ✅ Configure settings
4. ✅ Create admin account
5. ✅ Set up features

This may take 1-5 minutes. **Do not close the browser!**

### 6.4 Installation Complete!

You should see a success message:
```
✅ Installation Complete!
Your DemoLimo platform is ready to use.
```

Click **"Go to Admin Panel"** or **"Login"**

---

## ⚙️ **Step 7: Post-Installation Setup**

### 7.1 Login to Admin Panel

Visit:
```
https://demolimo.com/admin
```

Login with the admin credentials you created.

### 7.2 Security Checklist

#### Update .env File (IMPORTANT!)

Edit your `.env` file and change:
```env
APP_ENV=production
APP_DEBUG=false
```

#### Delete Test File

Delete this file from your server:
```
public/test.php
```

**Using FTP**: Navigate to `public/` folder, right-click `test.php` → Delete

**Using File Manager**: Navigate to `public/` folder, select `test.php` → Delete

### 7.3 Configure Essential Settings

In your admin panel, configure:

#### **Email Settings**
1. Go to **Settings** → **Email**
2. Configure SMTP settings:
   - SMTP Host: (from your email provider)
   - SMTP Port: 587 or 465
   - SMTP Username: your-email@example.com
   - SMTP Password: your email password
   - Encryption: TLS or SSL
3. Click **"Test Email"** to verify
4. Save settings

#### **Payment Gateways**
1. Go to **Settings** → **Payment Gateways**
2. Configure Stripe:
   - Enable Stripe
   - Enter Stripe Publishable Key
   - Enter Stripe Secret Key
   - Set to Test Mode initially
3. Configure PayPal (if needed):
   - Enable PayPal
   - Enter PayPal Client ID
   - Enter PayPal Secret
   - Set to Sandbox Mode initially
4. Save settings

#### **Storage Settings**
1. Go to **Settings** → **Storage**
2. Choose storage provider:
   - **Local**: Files stored on your server
   - **S3**: Amazon S3 (requires AWS credentials)
   - **DigitalOcean Spaces**: (requires DO credentials)
3. Configure and save

#### **General Settings**
1. Go to **Settings** → **General**
2. Update:
   - Site Name
   - Site Description
   - Logo (upload your logo)
   - Favicon
   - Contact Email
   - Social Media Links
3. Save settings

### 7.4 Test Everything

#### Test User Registration
1. Open an incognito/private browser window
2. Visit `https://demolimo.com`
3. Click **"Sign Up"**
4. Create a test user account
5. Verify email works (check spam folder)

#### Test File Upload
1. Login as test user
2. Try uploading a track
3. Verify upload works
4. Verify playback works

#### Test Payment (Test Mode)
1. Enable test mode for payment gateways
2. Try purchasing a track or subscription
3. Use test card numbers:
   - Stripe: `4242 4242 4242 4242`
   - Any future expiry date
   - Any 3-digit CVC
4. Verify payment flow works

---

## 🐛 **Troubleshooting**

### Problem: "403 Forbidden" Error

**Symptoms**: You see "You don't have permission to access this resource"

**Solutions**:
1. ✅ Verify `index.php` exists in root folder
2. ✅ Verify `public/index.php` exists
3. ✅ Verify `.htaccess` files are uploaded
4. ✅ Check file permissions (Step 3)
5. ✅ Contact host to enable `mod_rewrite`

### Problem: "404 Not Found" Error

**Symptoms**: You see "The requested URL was not found"

**Solutions**:
1. ✅ Verify all files from Step 1 are uploaded
2. ✅ Check `.htaccess` files exist (they're hidden!)
3. ✅ Verify `mod_rewrite` is enabled
4. ✅ Try visiting `https://demolimo.com/test.php`

### Problem: "500 Internal Server Error"

**Symptoms**: You see "Internal Server Error" or blank page

**Solutions**:
1. ✅ Check file permissions (Step 3)
2. ✅ Check hosting error logs (cPanel → Error Logs)
3. ✅ Temporarily enable debug:
   ```env
   APP_DEBUG=true
   ```
   Visit site to see actual error
4. ✅ Check `.env` file is configured correctly
5. ✅ Verify `vendor/` folder exists

### Problem: "Database Connection Error"

**Symptoms**: "Could not connect to database"

**Solutions**:
1. ✅ Verify database credentials in `.env`
2. ✅ Verify database exists
3. ✅ Verify database user has permissions
4. ✅ Try `DB_HOST=127.0.0.1` instead of `localhost`
5. ✅ Contact hosting provider for correct DB host

### Problem: "Class Not Found" Errors

**Symptoms**: "Class 'Something' not found"

**Solutions**:
1. ✅ Verify `vendor/` folder exists and has files
2. ✅ Run via SSH (if available):
   ```bash
   composer dump-autoload
   php artisan config:clear
   php artisan cache:clear
   ```
3. ✅ Contact hosting provider to run Composer

### Problem: Blank White Page

**Symptoms**: Nothing shows, just white page

**Solutions**:
1. ✅ Enable debug mode in `.env`:
   ```env
   APP_DEBUG=true
   ```
2. ✅ Check PHP error logs
3. ✅ Verify PHP version is 8.1+
4. ✅ Check all required extensions are enabled

### Problem: CSS/JS Not Loading

**Symptoms**: Page loads but looks broken, no styles

**Solutions**:
1. ✅ Check browser console for errors (F12)
2. ✅ Verify `public/build/` folder exists
3. ✅ Check `.htaccess` files are uploaded
4. ✅ Clear browser cache (Ctrl+F5)
5. ✅ Verify `APP_URL` in `.env` is correct

### Problem: File Upload Fails

**Symptoms**: "Failed to upload file" or similar error

**Solutions**:
1. ✅ Check `storage/` permissions (must be 755)
2. ✅ Check PHP upload limits:
   - `upload_max_filesize` (should be 50M+)
   - `post_max_size` (should be 50M+)
   - `max_execution_time` (should be 300+)
3. ✅ Contact hosting provider to increase limits

---

## 📞 **Getting Help**

### Check These First:
1. ✅ `storage/logs/laravel.log` - Application error logs
2. ✅ cPanel → **Error Logs** - Server error logs
3. ✅ Browser Console (F12) - JavaScript errors
4. ✅ `https://demolimo.com/test.php` - Server test results

### Contact Your Hosting Provider If:
- PHP version is too old (need 8.1+)
- Required PHP extensions are missing
- `mod_rewrite` is not enabled
- You need to run Composer commands
- File permissions issues persist
- Database connection issues persist

### Documentation Files:
- 📄 `COMPLETE_FIX_GUIDE.md` - Complete troubleshooting guide
- 📄 `QUICK_FIX_404.md` - Quick reference for common issues
- 📄 `SHARED_HOSTING_DEPLOYMENT.md` - Deployment best practices
- 📄 `INSTALLATION.md` - Original installation guide

---

## ✅ **Success Checklist**

Your installation is successful when:

- [ ] `https://demolimo.com` loads without errors
- [ ] You can login to admin panel
- [ ] You can create a user account
- [ ] You can upload audio files
- [ ] Music playback works
- [ ] All pages load correctly
- [ ] No errors in browser console
- [ ] Email notifications work
- [ ] Payment processing works (test mode)

---

## 🎉 **Congratulations!**

Your DemoLimo music streaming platform is now live!

### Next Steps:
1. 🎨 Customize your branding and theme
2. 👥 Invite users to test
3. 🎵 Upload some music
4. 💳 Configure payment gateways (live mode)
5. 📧 Set up email templates
6. 🚀 Launch your platform!

---

## 📊 **Quick Reference**

### Important URLs:
- **Homepage**: https://demolimo.com
- **Admin Panel**: https://demolimo.com/admin
- **User Dashboard**: https://demolimo.com/dashboard
- **Installer**: https://demolimo.com/installer
- **Test Page**: https://demolimo.com/test.php (delete after installation)

### Important Files:
- **Configuration**: `.env`
- **Root Entry**: `index.php`
- **Laravel Entry**: `public/index.php`
- **URL Rewriting**: `.htaccess` and `public/.htaccess`
- **Error Logs**: `storage/logs/laravel.log`

### Important Directories:
- **Storage**: `storage/` (must be writable)
- **Cache**: `bootstrap/cache/` (must be writable)
- **Public Assets**: `public/`
- **Dependencies**: `vendor/`

---

**Created**: November 25, 2025  
**Version**: 1.0  
**Platform**: DemoLimo Music Streaming Platform  

**Good luck with your music platform! 🎵🚀**
