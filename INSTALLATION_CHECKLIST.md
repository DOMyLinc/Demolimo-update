# 📋 DemoLimo - Quick Installation Checklist

Print this page and check off each step as you complete it!

---

## ✅ PRE-INSTALLATION CHECKLIST

### Files Ready?
- [ ] Downloaded all DemoLimo files
- [ ] `index.php` exists in root folder
- [ ] `public/index.php` exists
- [ ] `.htaccess` exists in root folder
- [ ] `public/.htaccess` exists
- [ ] `.env` file exists
- [ ] `vendor/` folder exists

### Credentials Ready?
- [ ] FTP/cPanel login credentials
- [ ] Database name: ___________________
- [ ] Database username: ___________________
- [ ] Database password: ___________________
- [ ] Database host: ___________________ (usually `localhost`)

---

## 🚀 INSTALLATION STEPS

### Step 1: Configure .env File (BEFORE UPLOAD)
- [ ] Open `.env` file
- [ ] Set `APP_URL=https://demolimo.com`
- [ ] Set database credentials (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- [ ] Save file

### Step 2: Upload Files
- [ ] Connect to FTP or open cPanel File Manager
- [ ] Navigate to website directory (`public_html` or `www`)
- [ ] Upload ALL files and folders
- [ ] Verify hidden files uploaded (`.env`, `.htaccess`)
- [ ] Upload complete (check file count)

### Step 3: Set Permissions
- [ ] Set `storage/` to 755 (recursive)
- [ ] Set `bootstrap/cache/` to 755 (recursive)

### Step 4: Create Database (if needed)
- [ ] Database created
- [ ] Database user created
- [ ] User added to database with ALL PRIVILEGES

### Step 5: Test Server
- [ ] Visit `https://demolimo.com/test.php`
- [ ] PHP Version: ✅ 8.1+
- [ ] All extensions: ✅ OK
- [ ] Directories writable: ✅ OK
- [ ] Vendor exists: ✅ OK

### Step 6: Run Installer
- [ ] Visit `https://demolimo.com`
- [ ] Redirected to `/installer`
- [ ] Completed database configuration
- [ ] Created admin account
- [ ] Selected features
- [ ] Configured basic settings
- [ ] Installation successful

### Step 7: Post-Installation
- [ ] Logged into admin panel
- [ ] Updated `.env`: `APP_DEBUG=false`
- [ ] Updated `.env`: `APP_ENV=production`
- [ ] Deleted `public/test.php`
- [ ] Configured email settings
- [ ] Configured payment gateways (test mode)
- [ ] Uploaded logo
- [ ] Tested user registration
- [ ] Tested file upload
- [ ] Tested music playback

---

## 🎯 QUICK TROUBLESHOOTING

### 403 Forbidden?
- [ ] Check `index.php` exists in root
- [ ] Check `public/index.php` exists
- [ ] Check `.htaccess` files uploaded

### 404 Not Found?
- [ ] Check all files uploaded
- [ ] Check `.htaccess` files exist
- [ ] Contact host about `mod_rewrite`

### 500 Error?
- [ ] Check file permissions (Step 3)
- [ ] Check hosting error logs
- [ ] Enable `APP_DEBUG=true` temporarily

### Database Error?
- [ ] Verify credentials in `.env`
- [ ] Check database exists
- [ ] Try `DB_HOST=127.0.0.1`

---

## 📞 IMPORTANT CONTACTS

**Hosting Provider**: ___________________  
**Support Email**: ___________________  
**Support Phone**: ___________________  

**Database Host**: ___________________  
**FTP Host**: ___________________  

---

## 🔑 ADMIN CREDENTIALS (Keep Secure!)

**Admin Email**: ___________________  
**Admin Username**: ___________________  
**Admin Password**: ___________________ (don't write here!)

---

## 📊 INSTALLATION TIMELINE

| Task | Estimated Time | Status |
|------|---------------|--------|
| Prepare files | 10 min | ⬜ |
| Upload files | 15-30 min | ⬜ |
| Set permissions | 5 min | ⬜ |
| Configure database | 5 min | ⬜ |
| Run installer | 5-10 min | ⬜ |
| Post-installation | 15 min | ⬜ |
| **TOTAL** | **55-75 min** | ⬜ |

---

## ✨ SUCCESS INDICATORS

Installation is complete when:
- ✅ Homepage loads without errors
- ✅ Can login to admin panel
- ✅ Can create user account
- ✅ Can upload audio files
- ✅ Music playback works
- ✅ No console errors (F12)

---

## 📚 DOCUMENTATION FILES

- [ ] Read `STEP_BY_STEP_GUIDE.md` (detailed instructions)
- [ ] Read `COMPLETE_FIX_GUIDE.md` (troubleshooting)
- [ ] Read `QUICK_FIX_404.md` (common issues)
- [ ] Bookmark for later reference

---

**Installation Date**: ___________________  
**Completed By**: ___________________  
**Notes**: 
___________________________________________
___________________________________________
___________________________________________

---

🎉 **Congratulations on your installation!** 🎉
