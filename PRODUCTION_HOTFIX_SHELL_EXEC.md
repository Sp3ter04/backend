# 🔧 Production Hotfix: shell_exec() Disabled on Hostinger

## 🚨 Issue on Production

**Error:** `Call to undefined function shell_exec()`  
**Location:** `app/Services/SimplePausedAudioService.php:232`  
**Environment:** Hostinger shared hosting  
**Cause:** `shell_exec()` and `exec()` are disabled in `php.ini` for security

---

## ✅ Solution Implemented

### Changes Made to `SimplePausedAudioService.php`

#### 1. **Added Global Namespace Prefix**
All global PHP functions now use `\` prefix:
- `\shell_exec()`, `\exec()`, `\trim()`, `\urlencode()`, etc.

#### 2. **Added Function Availability Checks**

**Before:**
```php
protected function processAudioSpeed(string $audioData, float $speed): ?string
{
    $ffmpegPath = trim(shell_exec('which ffmpeg') ?? '');
    // ... rest of code
}
```

**After:**
```php
protected function processAudioSpeed(string $audioData, float $speed): ?string
{
    // Check if shell_exec is available (disabled on shared hosting)
    if (!\function_exists('shell_exec')) {
        Log::info('shell_exec is disabled on this server, skipping FFmpeg processing');
        return $audioData;
    }
    
    try {
        $ffmpegPath = \trim(\shell_exec('which ffmpeg') ?? '');
        // ...
    } catch (\Exception $e) {
        Log::warning('Cannot check for FFmpeg: ' . $e->getMessage());
        return $audioData;
    }
    
    // Later in the code...
    if (!\function_exists('exec')) {
        Log::warning('exec() is disabled on this server, cannot process audio with FFmpeg');
        @\unlink($inputFile);
        return $audioData;
    }
    
    // ... rest of code
}
```

---

## 🎯 Impact

### What Works Now

✅ **Audio generation works** - Uses Google TTS API  
✅ **Comma-based pauses work** - No FFmpeg needed  
✅ **Graceful degradation** - Falls back when FFmpeg unavailable  
✅ **No errors** - Handles disabled functions properly  

### What's Different on Production

⚠️ **Audio speed adjustment disabled** - FFmpeg requires shell access  
⚠️ **Uses default speed** - Speed parameter ignored when FFmpeg unavailable  
✅ **Still functional** - Audio generation continues without speed processing  

---

## 📊 Behavior Comparison

| Feature | Local (Dev) | Production (Hostinger) |
|---------|-------------|------------------------|
| Audio generation | ✅ Works | ✅ Works |
| Google TTS API | ✅ Works | ✅ Works |
| Comma pauses | ✅ Works | ✅ Works |
| Speed adjustment | ✅ Works (FFmpeg) | ⚠️ Skipped (no FFmpeg) |
| `shell_exec()` | ✅ Available | ❌ Disabled |
| `exec()` | ✅ Available | ❌ Disabled |
| Error handling | ✅ Graceful | ✅ Graceful |

---

## 🔍 Technical Details

### Hostinger PHP Configuration

```ini
; These functions are typically disabled on shared hosting:
disable_functions = exec,passthru,shell_exec,system,proc_open,
                   popen,curl_exec,curl_multi_exec,parse_ini_file,
                   show_source
```

### Why These Functions Are Disabled

1. **Security** - Prevents malicious code execution
2. **Resource limits** - Shared hosting protection
3. **Server stability** - Prevents resource abuse

### Our Solution

Instead of failing, the code now:
1. ✅ **Checks** if function exists before calling
2. ✅ **Logs** when features are unavailable
3. ✅ **Continues** with core functionality
4. ✅ **Returns** original audio without speed processing

---

## 🚀 Deployment Instructions

### Step 1: Upload Updated Code

```bash
# Commit changes
git add app/Services/SimplePausedAudioService.php
git commit -m "Fix: Handle disabled shell_exec on production"
git push

# Or upload via FTP:
# - app/Services/SimplePausedAudioService.php
```

### Step 2: Clear Production Caches

SSH into Hostinger:

```bash
cd /home/username/domains/education.medtrack.click/public_html
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Step 3: Verify Fix

Test the "Listen" button on any exercise:
- ✅ Should play audio
- ✅ No error messages
- ✅ Check logs: `tail -f ../storage/logs/laravel.log`

---

## 📝 Log Messages to Expect

### On Production (Hostinger)

```
[INFO] shell_exec is disabled on this server, skipping FFmpeg processing
```

This is **normal** and **expected** on shared hosting.

### On Local Development

```
[WARNING] FFmpeg not found, returning original audio
```

This means FFmpeg is not installed locally (optional).

---

## 🧪 Testing Checklist

After deployment, verify:

- [ ] Can access `/admin/exercises` page
- [ ] Click "Listen" button on any exercise
- [ ] Audio plays successfully
- [ ] No error messages displayed
- [ ] Check Laravel logs for errors
- [ ] Test multiple exercises
- [ ] Test with different browsers

---

## 🔄 Fallback Behavior

### When FFmpeg is Unavailable

```php
// Speed parameter is ignored, returns original audio
$audioPath = $service->generateSentenceAudio(
    "A menina vê a mamã",
    'pt-PT',
    true,    // ✅ Pauses still work (comma-based)
    0.9      // ⚠️ Speed ignored (no FFmpeg)
);
```

### What Users Experience

- ✅ Audio still plays
- ✅ Pauses work correctly
- ⚠️ Audio speed is always normal (1.0x)
- ✅ No error messages

---

## 💡 Future Improvements

### Option 1: Use VPS Instead of Shared Hosting

**Pros:**
- Full shell access
- Can install FFmpeg
- Better performance

**Cons:**
- More expensive
- Requires server management

### Option 2: Use Audio Processing API

**Pros:**
- Works on shared hosting
- No server dependencies

**Cons:**
- Additional cost
- API dependency

### Option 3: Pre-process Audio Offline

**Pros:**
- No runtime processing needed
- Works anywhere

**Cons:**
- Manual process
- Storage requirements

---

## 📚 Related Files

- **Main fix:** `app/Services/SimplePausedAudioService.php`
- **Test script:** `test-shell-exec-fix.php`
- **Documentation:** `PRODUCTION_STORAGE_SETUP.md`
- **Deployment:** `DEPLOYMENT_CHECKLIST.md`

---

## 🆘 Troubleshooting

### Still Getting Errors?

1. **Clear all caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

2. **Check file was uploaded:**
   ```bash
   ls -la app/Services/SimplePausedAudioService.php
   # Should show recent modification date
   ```

3. **Check PHP version:**
   ```bash
   php -v
   # Should be PHP 8.2+
   ```

4. **Check logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

5. **Test directly:**
   ```bash
   php artisan tinker
   >>> $service = new \App\Services\SimplePausedAudioService();
   >>> $service->generateSentenceAudio("teste", "pt-PT", false, 1.0);
   ```

---

## ✅ Success Criteria

Your deployment is successful when:

- [ ] No `shell_exec()` errors in logs
- [ ] Audio plays on exercise pages
- [ ] "Listen" button works
- [ ] No 500 errors
- [ ] Logs show graceful handling: `"shell_exec is disabled on this server"`

---

## 📞 Support

If issues persist:

1. Check `storage/logs/laravel.log`
2. Verify file upload: `app/Services/SimplePausedAudioService.php`
3. Ensure caches are cleared
4. Contact Hostinger if PHP version issues

---

**Status:** ✅ Ready to Deploy  
**Tested:** ✅ Local environment verified  
**Production Ready:** ✅ Handles shared hosting restrictions  
**Date:** March 8, 2026
