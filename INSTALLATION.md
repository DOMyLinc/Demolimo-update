# DemoLimo Installation Guide

## 📋 Requirements

Before installing DemoLimo, ensure your server meets these requirements:

### Server Requirements
- **PHP**: 8.1 or higher
- **Database**: MySQL 5.7+ or PostgreSQL 10+
- **Web Server**: Apache or Nginx
- **Composer**: Latest version

### Required PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- ZIP

### Optional Requirements
- **FFMPEG**: For audio processing and waveform generation
- **Redis/Memcached**: For caching (recommended for production)
- **Node.js & NPM**: For frontend asset compilation

---

## 🚀 Installation Steps

### Step 1: Upload Files

1. **Download** the DemoLimo package
2. **Extract** the files to your server
3. **Upload** all files to your web root directory (e.g., `public_html`, `www`, or `htdocs`)

**Important**: The application includes a root `.htaccess` file that automatically redirects to the `public/` folder. You can upload files directly to your domain root.

---

### Step 2: Set Permissions

Set the correct permissions for storage and cache directories:

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

Or if using a control panel, set these folders to **755** (read, write, execute for owner).

---

### Step 3: Create Database

Create a new database for DemoLimo:

**Using cPanel:**
1. Go to **MySQL Databases**
2. Create a new database (e.g., `demolimo_db`)
3. Create a database user
4. Add the user to the database with **ALL PRIVILEGES**

**Using Command Line:**
```sql
CREATE DATABASE demolimo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'demolimo_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON demolimo_db.* TO 'demolimo_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### Step 4: Access the Installer

1. Open your browser and navigate to your domain:
   ```
   https://yourdomain.com
   ```

2. You'll be automatically redirected to the installer:
   ```
   https://yourdomain.com/installer
   ```

---

## 🎯 Installer Walkthrough

### **Step 1: Requirements Check**

The installer will verify that your server meets all requirements.

- ✅ **Green checkmarks** = All requirements met
- ❌ **Red X marks** = Missing requirements (must be fixed before continuing)

**Action**: Click **"Continue"** if all requirements are met.

---

### **Step 2: Database Configuration**

Configure your database connection:

| Field | Description | Example |
|-------|-------------|---------|
| **Database Type** | Choose MySQL or PostgreSQL | MySQL |
| **Database Host** | Usually `localhost` or `127.0.0.1` | 127.0.0.1 |
| **Database Port** | Auto-filled based on type | 3306 (MySQL) / 5432 (PostgreSQL) |
| **Database Name** | The database you created | demolimo_db |
| **Username** | Database username | demolimo_user |
| **Password** | Database password | your_password |

**Action**: 
1. Select your database type (MySQL/PostgreSQL)
2. Fill in your database credentials
3. Click **"Test Connection & Continue"**

The installer will:
- Test the database connection
- Run all migrations automatically
- Create all necessary tables

---

### **Step 3: Admin Account**

Create your administrator account:

| Field | Description | Example |
|-------|-------------|---------|
| **Name** | Your full name | John Doe |
| **Email** | Admin email address | admin@yourdomain.com |
| **Password** | Strong password (min 8 characters) | ••••••••••• |
| **Confirm Password** | Re-enter password | ••••••••••• |

**Action**: Fill in the form and click **"Create Admin & Continue"**

---

### **Step 4: Feature Configuration**

Enable or disable platform features:

#### Core Features
- ☑️ **Chat System** - Real-time messaging
- ☑️ **Blog** - Blogging platform
- ☑️ **Radio Stations** - Internet radio
- ☑️ **Podcasts** - Podcast hosting
- ☑️ **Affiliate System** - Referral program
- ☑️ **Points System** - Reward users with points
- ☑️ **Import Tools** - Import from other platforms

#### Audio Processing
- ☑️ **Waveform Generation** - Visual waveforms for tracks
- **FFMPEG Path**: 
  - Auto-download (recommended for Windows)
  - Or specify custom path (e.g., `/usr/bin/ffmpeg`)

**Action**: 
1. Check the features you want to enable
2. Configure FFMPEG settings
3. Click **"Continue"**

---

### **Step 5: System Settings**

Configure your application settings:

#### General Settings
| Field | Description | Example |
|-------|-------------|---------|
| **App Name** | Your platform name | DemoLimo |
| **App URL** | Your full domain URL | https://yourdomain.com |

#### Theme Selection
- Choose from available themes
- **Lava Theme** is selected by default (recommended)
- Preview color schemes before selecting

#### Mail Configuration (SMTP)
| Field | Description | Example |
|-------|-------------|---------|
| **Mail Host** | SMTP server | smtp.gmail.com |
| **Mail Port** | SMTP port | 587 |
| **Mail Username** | SMTP username | your-email@gmail.com |
| **Mail Password** | SMTP password | your-app-password |
| **Mail Encryption** | TLS or SSL | TLS |
| **From Address** | Sender email | noreply@yourdomain.com |

#### Storage Configuration
Choose where to store uploaded files:

**Local Storage** (Default):
- Files stored on your server
- No additional configuration needed

**AWS S3 / Compatible**:
- AWS Access Key ID
- AWS Secret Access Key
- AWS Region (e.g., `us-east-1`)
- AWS Bucket Name

#### Real-time Features (Optional)
Configure Pusher for real-time notifications:
- App ID
- App Key
- App Secret
- App Cluster

**Action**: 
1. Fill in all required settings
2. Select your preferred theme
3. Click **"Finish Installation"**

---

### **Step 6: Installation Complete!**

The installer will now:
1. ✅ Update environment configuration
2. ✅ Seed the database with default data
3. ✅ Seed all feature flags
4. ✅ Apply your selected theme
5. ✅ Create storage symbolic link
6. ✅ Optimize and cache configurations
7. ✅ Mark installation as complete

You'll be redirected to the homepage automatically.

---

## 🎉 Post-Installation

### Access Your Platform

**Frontend (User Area):**
```
https://yourdomain.com
```

**Admin Panel:**
```
https://yourdomain.com/admin
```

**Login Credentials:**
- Email: The email you entered during installation
- Password: The password you created

---

## ⚙️ Post-Installation Configuration

### 1. Configure Payment Gateways

Go to **Admin Panel → Settings → Payment Gateways**

Configure:
- Stripe (API keys)
- PayPal (Client ID & Secret)
- Manual payments

### 2. Set Up Storage

If using S3 or cloud storage:
- Go to **Admin Panel → Settings → Storage**
- Configure your cloud storage provider

### 3. Configure Email Templates

- Go to **Admin Panel → Email Management**
- Customize email templates for notifications

### 4. Set Up Themes

- Go to **Admin Panel → Themes**
- Customize colors and branding
- Upload logo and favicon

### 5. Configure Features

- Go to **Admin Panel → Feature Flags**
- Enable/disable specific features
- Set permissions per feature

### 6. Add Content

- Create genres
- Add sample tracks
- Set up radio stations
- Create featured content

---

## 🔧 Troubleshooting

### Issue: White Screen After Installation

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Issue: 500 Internal Server Error

**Solution:**
1. Check file permissions (755 for folders, 644 for files)
2. Ensure `.env` file exists and is readable
3. Check error logs in `storage/logs/laravel.log`

### Issue: Database Connection Failed

**Solution:**
1. Verify database credentials in `.env`
2. Ensure database user has proper privileges
3. Check if database server is running

### Issue: Public Folder Not Working

**Solution:**
The root `.htaccess` should handle this automatically. If not:
1. Ensure mod_rewrite is enabled
2. Check Apache configuration allows `.htaccess` overrides
3. For Nginx, configure root to `/public`

### Issue: Storage Files Not Accessible

**Solution:**
```bash
php artisan storage:link
```

---

## 📞 Support

If you encounter any issues:

1. **Check Documentation**: Review this guide thoroughly
2. **Error Logs**: Check `storage/logs/laravel.log`
3. **Server Logs**: Check Apache/Nginx error logs
4. **Contact Support**: Reach out with error details

---

## 🔐 Security Recommendations

After installation:

1. ✅ **Delete installer files** (optional, for extra security):
   ```bash
   rm -rf app/Http/Controllers/InstallerController.php
   rm -rf resources/views/installer
   ```

2. ✅ **Set strong passwords** for admin accounts

3. ✅ **Enable HTTPS** (SSL certificate)

4. ✅ **Configure firewall** rules

5. ✅ **Regular backups** of database and files

6. ✅ **Keep PHP and dependencies updated**

---

## 🎵 You're Ready!

Your DemoLimo music platform is now installed and ready to use!

Start by:
1. Logging into the admin panel
2. Customizing your theme and branding
3. Adding genres and content
4. Inviting users to join

**Enjoy your new music streaming platform!** 🚀
