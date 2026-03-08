# 🚀 Production Deployment Checklist

## Pre-Deployment (Local)

- [x] ✅ UUID constraint added to `routes/api.php`
- [x] ✅ `.htaccess` updated with storage priority rules
- [x] ✅ Routes tested locally (UUID constraint working)
- [x] ✅ Storage diagnostic passed
- [ ] 📝 Test audio URL locally: http://localhost:8000/storage/audio/sentences/exercise-1-a-mae.mp3
- [ ] 📝 Commit changes to Git
- [ ] 📝 Push to repository

---

## Deployment to Hostinger

### 1. Upload Code
- [ ] Upload via FTP or Git pull
- [ ] Verify all files uploaded correctly
- [ ] Check `.htaccess` file is in `public_html/`

### 2. Create Symbolic Link (CRITICAL)

SSH into Hostinger and run:

```bash
# Navigate to public directory
cd /home/username/domains/education.medtrack.click/public_html

# Remove old symlink if exists
rm -f storage

# Create new symlink
ln -sfn ../storage/app/public storage

# Verify
ls -la storage
# Expected: storage -> ../storage/app/public
```

- [ ] ✅ Symlink created
- [ ] ✅ Symlink points to correct directory

### 3. Set Permissions

```bash
# Set storage permissions
chmod -R 775 ../storage/app/public

# Set symlink permissions
chmod -R 775 storage

# Set ownership
chown -R username:username ../storage/app/public
```

- [ ] ✅ Storage permissions set (775)
- [ ] ✅ Ownership set correctly

### 4. Create Audio Directory (if needed)

```bash
mkdir -p ../storage/app/public/audio/sentences
chmod -R 775 ../storage/app/public/audio
```

- [ ] ✅ Audio directory exists
- [ ] ✅ Directory is writable

### 5. Update Environment Configuration

Edit `.env` file on production:

```env
APP_URL=https://education.medtrack.click
FILESYSTEM_DISK=public
```

**IMPORTANT:** No trailing slash on `APP_URL`!

- [ ] ✅ APP_URL updated (no trailing slash)
- [ ] ✅ FILESYSTEM_DISK set to 'public'

### 6. Clear Laravel Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

- [ ] ✅ All caches cleared

---

## Post-Deployment Testing

### 7. Run Diagnostic Script

```bash
php storage-diagnostic.php
```

Expected result: **"ALL CHECKS PASSED"**

- [ ] ✅ Diagnostic passed
- [ ] ✅ Symlink verified
- [ ] ✅ Audio directory exists
- [ ] ✅ Permissions correct
- [ ] ✅ URL generation correct

### 8. Test Storage Access

Create a test file:

```bash
echo "Storage works!" > ../storage/app/public/test.txt
```

Test in browser:
```
https://education.medtrack.click/storage/test.txt
```

Expected: See "Storage works!"

- [ ] ✅ Test file accessible
- [ ] ✅ HTTP 200 OK response

### 9. Test Audio File

Test an actual audio file:
```
https://education.medtrack.click/storage/audio/sentences/exercise-1-a-mae.mp3
```

- [ ] ✅ Audio file loads
- [ ] ✅ Audio plays or downloads
- [ ] ✅ No 404 error
- [ ] ✅ No PostgreSQL UUID error

### 10. Test UUID Constraint

```bash
php test-route-constraint.php
```

Expected result: "Route constraint is configured"

- [ ] ✅ UUID constraint active
- [ ] ✅ Non-UUID requests return 404

### 11. Browser Developer Tools Check

Open browser DevTools → Network tab

Load page with audio files and verify:

- [ ] ✅ Audio requests go to `/storage/audio/sentences/...`
- [ ] ✅ No requests to `/api/exercises/*.mp3`
- [ ] ✅ HTTP 200 status for audio files
- [ ] ✅ Content-Type: `audio/mpeg`

### 12. Check Laravel Logs

```bash
tail -100 ../storage/logs/laravel.log
```

Verify:
- [ ] ✅ No PostgreSQL UUID errors
- [ ] ✅ No 404 errors for storage files
- [ ] ✅ No permission denied errors

---

## Verification Commands

Run these to verify everything:

```bash
# 1. Check symlink
ls -la public_html/storage

# 2. Check audio files exist
ls -la ../storage/app/public/audio/sentences/ | head -10

# 3. Test storage URL
curl -I https://education.medtrack.click/storage/test.txt

# 4. Check .htaccess
cat public_html/.htaccess | grep -A 3 "storage"

# 5. Test audio file
curl -I https://education.medtrack.click/storage/audio/sentences/exercise-1-a-mae.mp3

# 6. Check routes
php artisan route:list | grep exercises

# 7. Run diagnostics
php storage-diagnostic.php

# 8. Test route constraint
php test-route-constraint.php
```

---

## Common Issues & Quick Fixes

### Issue: 404 on storage files

```bash
# Fix: Recreate symlink
cd public_html
rm -f storage
ln -sfn ../storage/app/public storage
ls -la storage
```

### Issue: 403 Forbidden

```bash
# Fix: Set permissions
chmod -R 755 ../storage/app/public/audio
chmod -R 755 storage
```

### Issue: PostgreSQL UUID error

```bash
# Fix: Clear route cache
php artisan route:clear
php artisan config:clear
# Verify constraint: php test-route-constraint.php
```

### Issue: Wrong URLs generated

```bash
# Fix: Update .env
# Ensure: APP_URL=https://education.medtrack.click (no trailing slash)
php artisan config:clear
```

---

## Success Criteria

✅ **All these should be true:**

1. Diagnostic script passes all checks
2. Test storage URL works: `https://education.medtrack.click/storage/test.txt`
3. Audio files accessible: `https://education.medtrack.click/storage/audio/sentences/exercise-1-a-mae.mp3`
4. No PostgreSQL UUID errors in logs
5. No 404/403 errors on storage files
6. Route constraint test passes
7. Browser DevTools shows correct requests to `/storage/`
8. No API requests for audio files

---

## Rollback Plan (If Issues)

If something goes wrong:

1. **Restore old `.htaccess`:**
   ```bash
   cp .htaccess.backup .htaccess
   ```

2. **Clear all caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

3. **Check Laravel logs:**
   ```bash
   tail -f ../storage/logs/laravel.log
   ```

4. **Contact Hostinger support** for symlink/permission issues

---

## Documentation Reference

- **Setup Guide:** `PRODUCTION_STORAGE_SETUP.md`
- **Complete Solution:** `SOLUTION_SUMMARY.md`
- **Interactive Setup:** `./hostinger-setup-guide.sh`
- **Diagnostic Tool:** `php storage-diagnostic.php`
- **Route Testing:** `php test-route-constraint.php`

---

## Notes

- **Hostinger Username:** Replace `username` with your actual Hostinger username
- **Domain Path:** Adjust paths if your domain structure is different
- **PHP Version:** Ensure PHP 8.1+ is enabled in Hostinger control panel
- **LiteSpeed:** Hostinger uses LiteSpeed which is compatible with Apache `.htaccess` rules

---

## Support Contacts

- **Laravel Logs:** `storage/logs/laravel.log`
- **Apache/LiteSpeed Logs:** Available in Hostinger control panel
- **Hostinger Support:** For symlink/permission issues
- **This Repository:** Check documentation files for detailed help

---

**🎉 Once all checkboxes are marked, your deployment is complete!**

---

## Post-Deployment Monitoring

For the first 24-48 hours after deployment:

- [ ] Monitor Laravel logs: `tail -f storage/logs/laravel.log`
- [ ] Check for 404 errors on storage files
- [ ] Verify audio playback in production
- [ ] Test from different devices/browsers
- [ ] Check performance metrics

---

**Last Updated:** March 8, 2026  
**Version:** 1.0  
**Tested On:** Laravel 11.x + Hostinger LiteSpeed
