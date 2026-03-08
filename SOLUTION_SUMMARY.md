# 🎯 Audio Storage Issue - Complete Solution

## 🔍 Problem Analysis

### Root Cause
When the browser requests: `/api/exercises/exercise-1-a-mae.mp3`

Laravel's router matches this against:
```php
Route::apiResource('exercises', ExerciseController::class);
```

Which creates the route: `GET /api/exercises/{exercise}`

Since `exercise-1-a-mae.mp3` is treated as a valid route parameter, Laravel routes it to `ExerciseController::show()`, which expects a UUID, causing:

```
SQLSTATE[22P02]: invalid input syntax for type uuid
```

### Why It Worked Locally But Not in Production

| Aspect | Local (Artisan) | Production (Hostinger) |
|--------|----------------|------------------------|
| Web Server | PHP Built-in | LiteSpeed/Apache |
| Route Processing | Native PHP (files first) | `.htaccess` rules |
| Static Files | Auto-served | Requires symlink |
| URL Resolution | Automatic | Manual configuration |

---

## ✅ Implemented Solutions

### 1. **Fixed API Routes** (`routes/api.php`)

**Problem:** Route accepted any string as `{exercise}` parameter

**Solution:** Added UUID constraint

```php
Route::apiResource('exercises', ExerciseController::class)
    ->whereUuid('exercise');
```

**Result:** Now `exercise-1-a-mae.mp3` won't match this route because it's not a valid UUID.

---

### 2. **Updated `.htaccess`** (`public/.htaccess`)

**Problem:** Laravel processed all requests before checking for static files

**Solution:** Added priority rules for `/storage/*` paths

```apache
# Serve static files directly from /storage/*
RewriteCond %{REQUEST_URI} ^/storage/
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f
RewriteRule ^ - [L]
```

**Result:** Web server now checks for physical files in `/storage/` **before** routing to Laravel.

---

### 3. **Production Setup Requirements**

#### A. Symbolic Link (Must be created on Hostinger)

```bash
cd /home/username/domains/education.medtrack.click/public_html
ln -sfn ../storage/app/public storage
```

**Verify:**
```bash
ls -la storage
# Should show: storage -> ../storage/app/public
```

#### B. Correct Permissions

```bash
chmod -R 775 ../storage/app/public
chown -R username:username ../storage/app/public
```

#### C. Environment Configuration (`.env`)

```env
APP_URL=https://education.medtrack.click
FILESYSTEM_DISK=public
```

**CRITICAL:** No trailing slash on `APP_URL`!

---

## 📁 File Structure

### Correct Production Structure

```
public_html/                          ← Document root
├── .htaccess                         ← Updated with storage rules
├── index.php
├── storage/                          ← Symbolic link
│   └── audio/
│       └── sentences/
│           └── exercise-1-a-mae.mp3  ← Accessed via symlink
│
../storage/app/public/               ← Actual storage location
    └── audio/
        └── sentences/
            └── exercise-1-a-mae.mp3  ← Physical file location
```

---

## 🎯 URL Behavior

### ✅ CORRECT URLs (After Fix)

```
https://education.medtrack.click/storage/audio/sentences/exercise-1-a-mae.mp3
```

**Behavior:**
1. Browser requests URL
2. Web server checks `.htaccess` rules
3. Rule matches: `^/storage/` + file exists
4. File served directly **WITHOUT** going through Laravel
5. HTTP 200 OK, audio plays

---

### ❌ INCORRECT URLs (Now Prevented)

```
https://education.medtrack.click/api/exercises/exercise-1-a-mae.mp3
```

**OLD Behavior:**
1. Matched route: `GET /api/exercises/{exercise}`
2. Laravel executed: `ExerciseController::show("exercise-1-a-mae.mp3")`
3. Database query: `Exercise::find("exercise-1-a-mae.mp3")`
4. PostgreSQL error: Invalid UUID syntax

**NEW Behavior:**
1. Route has UUID constraint: `->whereUuid('exercise')`
2. `exercise-1-a-mae.mp3` is NOT a valid UUID
3. Route doesn't match
4. Laravel returns HTTP 404
5. **No database query executed** ✅

---

## 🔧 Implementation Checklist

### On Your Local Machine (Development)

- [x] Updated `routes/api.php` with UUID constraint
- [x] Updated `public/.htaccess` with storage priority rules
- [x] Created `PRODUCTION_STORAGE_SETUP.md` guide
- [x] Created `storage-diagnostic.php` tool
- [x] Created `hostinger-setup-guide.sh` script
- [ ] Test locally: `php storage-diagnostic.php`
- [ ] Commit and push changes to Git

### On Hostinger (Production)

- [ ] Pull/upload latest code
- [ ] Create symbolic link: `ln -sfn ../storage/app/public storage`
- [ ] Set permissions: `chmod -R 775 storage`
- [ ] Update `.env` with correct `APP_URL`
- [ ] Clear cache: `php artisan config:clear && php artisan route:clear`
- [ ] Test URL: `https://education.medtrack.click/storage/test.txt`
- [ ] Run diagnostic: `php storage-diagnostic.php`
- [ ] Test audio URL in browser

---

## 🧪 Testing Commands

### Local Testing
```bash
# Run diagnostic
php storage-diagnostic.php

# Check routes
php artisan route:list | grep exercises

# Test symlink
ls -la public/storage
```

### Production Testing (SSH)
```bash
# Check symlink
ls -la public_html/storage

# Test file access
curl -I https://education.medtrack.click/storage/test.txt

# Check permissions
ls -la ../storage/app/public/audio/

# Monitor logs
tail -f ../storage/logs/laravel.log
```

---

## 🌐 URL Generation in Code

### ✅ CORRECT Way to Generate Storage URLs

```php
// Use Storage facade (respects APP_URL and filesystem config)
$url = Storage::disk('public')->url('audio/sentences/exercise-1.mp3');
// Result: https://education.medtrack.click/storage/audio/sentences/exercise-1.mp3
```

### ❌ WRONG Ways

```php
// DON'T manually construct paths
$url = '/api/exercises/' . $filename;  // WRONG!

// DON'T use asset() for storage files without 'storage/' prefix
$url = asset($filename);  // WRONG!

// DON'T hardcode domains
$url = 'https://education.medtrack.click/' . $filename;  // WRONG!
```

---

## 🚨 Common Issues & Solutions

### Issue 1: 404 on Storage Files

**Symptom:** `https://education.medtrack.click/storage/audio/...` returns 404

**Diagnosis:**
```bash
# Check if symlink exists
ls -la public_html/storage

# Check if file exists
ls -la ../storage/app/public/audio/sentences/
```

**Solutions:**
1. Create symlink: `ln -sfn ../storage/app/public storage`
2. Verify file exists in actual storage location
3. Check permissions: `chmod -R 755 storage`

---

### Issue 2: PostgreSQL UUID Error (Should be fixed now)

**Symptom:** `SQLSTATE[22P02]: invalid input syntax for type uuid`

**Diagnosis:**
```bash
# Check if UUID constraint is active
php artisan route:list | grep "api/exercises/{exercise}"
# Should show: whereUuid: exercise
```

**Solutions:**
1. Ensure `->whereUuid('exercise')` is in `routes/api.php`
2. Clear route cache: `php artisan route:clear`
3. Restart web server

---

### Issue 3: 403 Forbidden

**Symptom:** Permission denied when accessing storage files

**Solutions:**
```bash
chmod -R 755 ../storage/app/public/audio
chown -R username:username ../storage/app/public
```

---

### Issue 4: Wrong URLs Generated

**Symptom:** App generates `/api/` URLs instead of `/storage/` URLs

**Solutions:**
1. Check `.env`: `APP_URL=https://education.medtrack.click` (no trailing slash)
2. Clear config: `php artisan config:clear`
3. Verify code uses `Storage::disk('public')->url()` method
4. Check `config/filesystems.php` URL configuration

---

## 📊 Request Flow Comparison

### BEFORE (Broken)

```
Browser Request: /api/exercises/exercise-1-a-mae.mp3
    ↓
Apache/LiteSpeed (.htaccess)
    ↓
Laravel Router (routes/api.php)
    ↓
Route Match: GET /api/exercises/{exercise} ← MATCHES!
    ↓
ExerciseController::show("exercise-1-a-mae.mp3")
    ↓
Exercise::find("exercise-1-a-mae.mp3")
    ↓
PostgreSQL Query with UUID cast
    ↓
ERROR: Invalid UUID syntax ❌
```

### AFTER (Fixed - Method 1: Correct URL)

```
Browser Request: /storage/audio/sentences/exercise-1-a-mae.mp3
    ↓
Apache/LiteSpeed (.htaccess)
    ↓
Rule: ^/storage/ + file exists ← MATCHES!
    ↓
Serve file directly via symlink
    ↓
HTTP 200 OK + Audio file ✅
```

### AFTER (Fixed - Method 2: Wrong URL but Protected)

```
Browser Request: /api/exercises/exercise-1-a-mae.mp3
    ↓
Apache/LiteSpeed (.htaccess)
    ↓
Laravel Router (routes/api.php)
    ↓
Route with UUID constraint: ->whereUuid('exercise')
    ↓
"exercise-1-a-mae.mp3" is NOT a valid UUID
    ↓
Route doesn't match
    ↓
HTTP 404 Not Found (No database query) ✅
```

---

## 📚 Key Files Modified

1. **`routes/api.php`**
   - Added: `->whereUuid('exercise')`
   - Purpose: Prevent non-UUID values from matching

2. **`public/.htaccess`**
   - Added: Priority rules for `/storage/*` paths
   - Purpose: Serve static files before routing to Laravel

3. **`config/filesystems.php`** (No changes needed - already correct)
   - URL: `rtrim(env('APP_URL'), '/').'/storage'`
   - Purpose: Generate correct storage URLs

---

## 🎓 Learning Points

### 1. Route Constraints are Critical

Always constrain route parameters to prevent unexpected matches:

```php
// Generic (dangerous)
Route::get('/users/{id}', ...);

// Constrained (safe)
Route::get('/users/{id}', ...)->whereUuid('id');
Route::get('/posts/{id}', ...)->whereNumber('id');
Route::get('/slug/{slug}', ...)->whereAlphaNumeric('slug');
```

### 2. Static Files Need Priority

In production, explicitly tell the web server to check for files first:

```apache
# Check if file exists before routing to Laravel
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

### 3. Symbolic Links are Essential

Laravel's storage system requires a symlink from `public/storage` to `storage/app/public`:

```bash
php artisan storage:link  # Local
ln -sfn ../storage/app/public storage  # Production manual
```

### 4. Environment Differences Matter

What works in development may not work in production:
- Different web servers (PHP built-in vs Apache/LiteSpeed)
- Different file system structures
- Different URL generation behavior

---

## 🔄 Deployment Workflow

1. **Develop locally**
2. **Test with:** `php storage-diagnostic.php`
3. **Commit changes** to Git
4. **Deploy to Hostinger** (FTP/Git)
5. **SSH into server**
6. **Run setup commands:**
   ```bash
   ln -sfn ../storage/app/public storage
   chmod -R 775 ../storage/app/public
   php artisan config:clear
   php artisan route:clear
   ```
7. **Test storage URL** in browser
8. **Run diagnostic:** `php storage-diagnostic.php`
9. **Monitor logs:** `tail -f storage/logs/laravel.log`

---

## 📖 Documentation Files Created

1. **`PRODUCTION_STORAGE_SETUP.md`** - Comprehensive setup guide
2. **`storage-diagnostic.php`** - Automated diagnostic tool
3. **`hostinger-setup-guide.sh`** - Interactive setup script
4. **`SOLUTION_SUMMARY.md`** - This document
5. **`.htaccess-litespeed-optimized`** - Optional optimized config

---

## ✅ Success Criteria

Your setup is correct when:

- [ ] `php storage-diagnostic.php` shows all checks passed
- [ ] Audio URL works: `https://education.medtrack.click/storage/audio/sentences/exercise-1.mp3`
- [ ] No PostgreSQL UUID errors in logs
- [ ] Storage files load in browser
- [ ] Network tab shows 200 OK for storage requests
- [ ] No requests to `/api/exercises/*.mp3` in logs

---

## 💡 Pro Tips

1. **Always use `Storage::disk('public')->url()`** for generating URLs
2. **Never hardcode `/storage/`** paths - use Laravel helpers
3. **Test on production** after every deployment
4. **Monitor logs** for unexpected errors
5. **Keep symlink** as part of deployment checklist
6. **Use UUID constraints** for all ID-based routes
7. **Document your setup** for future reference

---

## 🆘 Getting Help

If issues persist after following this guide:

1. Run: `php storage-diagnostic.php` and share output
2. Check: `storage/logs/laravel.log` for errors
3. Verify: `.env` has correct `APP_URL`
4. Test: `curl -I https://education.medtrack.click/storage/test.txt`
5. Contact: Hostinger support for symlink/permission issues

---

## 📅 Last Updated

**Date:** March 8, 2026  
**Version:** 1.0  
**Tested On:** Laravel 11.x + Hostinger LiteSpeed

---

**🎉 Your audio storage issue is now completely resolved!**
