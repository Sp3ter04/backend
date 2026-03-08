#!/bin/bash

# =============================================================================
# 🚀 Quick Production Hotfix Deployment Script
# Fixes shell_exec() error on Hostinger
# =============================================================================

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     🔧 Production Hotfix: shell_exec() Error Fix              ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# =============================================================================
# STEP 1: COMMIT CHANGES
# =============================================================================
echo -e "${BLUE}📦 Step 1: Commit Changes${NC}"
echo "──────────────────────────────────────────────────────────────────"
echo ""
echo "Run these commands to commit the fix:"
echo ""
echo -e "${GREEN}git add app/Services/SimplePausedAudioService.php${NC}"
echo -e "${GREEN}git add PRODUCTION_HOTFIX_SHELL_EXEC.md${NC}"
echo -e "${GREEN}git commit -m \"Fix: Handle disabled shell_exec() on production\"${NC}"
echo -e "${GREEN}git push origin main${NC}"
echo ""
read -p "Press Enter after committing..."

# =============================================================================
# STEP 2: UPLOAD TO PRODUCTION
# =============================================================================
echo ""
echo -e "${BLUE}📤 Step 2: Upload to Production${NC}"
echo "──────────────────────────────────────────────────────────────────"
echo ""
echo "Choose your deployment method:"
echo ""
echo "  Option A: Git Pull (if you have Git on Hostinger)"
echo -e "    ${GREEN}cd /home/username/domains/education.medtrack.click/public_html${NC}"
echo -e "    ${GREEN}git pull origin main${NC}"
echo ""
echo "  Option B: FTP Upload"
echo "    Upload: app/Services/SimplePausedAudioService.php"
echo "    To: /public_html/app/Services/"
echo ""
read -p "Press Enter after uploading..."

# =============================================================================
# STEP 3: CLEAR PRODUCTION CACHES
# =============================================================================
echo ""
echo -e "${BLUE}🧹 Step 3: Clear Production Caches${NC}"
echo "──────────────────────────────────────────────────────────────────"
echo ""
echo "SSH into Hostinger and run:"
echo ""
echo -e "${GREEN}cd /home/username/domains/education.medtrack.click/public_html${NC}"
echo -e "${GREEN}php artisan config:clear${NC}"
echo -e "${GREEN}php artisan route:clear${NC}"
echo -e "${GREEN}php artisan cache:clear${NC}"
echo -e "${GREEN}php artisan view:clear${NC}"
echo ""
echo "Or run all at once:"
echo -e "${GREEN}php artisan optimize:clear${NC}"
echo ""
read -p "Press Enter after clearing caches..."

# =============================================================================
# STEP 4: TEST
# =============================================================================
echo ""
echo -e "${BLUE}🧪 Step 4: Test on Production${NC}"
echo "──────────────────────────────────────────────────────────────────"
echo ""
echo "1. Open browser:"
echo -e "   ${YELLOW}https://education.medtrack.click/admin/exercises${NC}"
echo ""
echo "2. Click 'Listen' button on any exercise"
echo ""
echo "3. Expected result:"
echo "   ✅ Audio plays successfully"
echo "   ✅ No error messages"
echo ""
echo "4. Check logs (optional):"
echo -e "   ${GREEN}tail -f storage/logs/laravel.log${NC}"
echo ""
echo "   You should see:"
echo "   [INFO] shell_exec is disabled on this server, skipping FFmpeg processing"
echo ""
echo "   This is NORMAL and EXPECTED on shared hosting!"
echo ""
read -p "Press Enter after testing..."

# =============================================================================
# VERIFICATION
# =============================================================================
echo ""
echo -e "${BLUE}✅ Step 5: Verification${NC}"
echo "──────────────────────────────────────────────────────────────────"
echo ""
echo "Please confirm:"
echo ""
read -p "1. Can you access /admin/exercises page? (y/n) " admin_access
read -p "2. Does the 'Listen' button work? (y/n) " listen_works
read -p "3. Audio plays successfully? (y/n) " audio_plays
read -p "4. No error messages displayed? (y/n) " no_errors

echo ""

if [[ "$admin_access" == "y" && "$listen_works" == "y" && "$audio_plays" == "y" && "$no_errors" == "y" ]]; then
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║              ✅ DEPLOYMENT SUCCESSFUL! ✅                       ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${GREEN}🎉 Your production issue is fixed!${NC}"
    echo ""
    echo "What was fixed:"
    echo "  ✅ shell_exec() errors resolved"
    echo "  ✅ Graceful handling of disabled functions"
    echo "  ✅ Audio generation still works"
    echo "  ✅ Pauses work (comma-based, no FFmpeg needed)"
    echo ""
    echo "Note: Audio speed adjustment is disabled on production"
    echo "      (requires FFmpeg which needs VPS, not shared hosting)"
    echo ""
else
    echo -e "${RED}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║                 ⚠️  ISSUES DETECTED ⚠️                        ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo "Troubleshooting steps:"
    echo ""
    echo "1. Clear caches again:"
    echo -e "   ${GREEN}php artisan optimize:clear${NC}"
    echo ""
    echo "2. Check file was uploaded:"
    echo -e "   ${GREEN}ls -la app/Services/SimplePausedAudioService.php${NC}"
    echo ""
    echo "3. Check logs:"
    echo -e "   ${GREEN}tail -100 storage/logs/laravel.log${NC}"
    echo ""
    echo "4. Verify PHP version:"
    echo -e "   ${GREEN}php -v${NC}"
    echo "   (Should be PHP 8.2+)"
    echo ""
    echo "5. Review documentation:"
    echo "   📖 PRODUCTION_HOTFIX_SHELL_EXEC.md"
    echo ""
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "📚 Documentation:"
echo "   • Complete guide: PRODUCTION_HOTFIX_SHELL_EXEC.md"
echo "   • Storage setup: PRODUCTION_STORAGE_SETUP.md"
echo "   • Full solution: SOLUTION_SUMMARY.md"
echo ""
echo "🆘 Need help?"
echo "   • Check Laravel logs: storage/logs/laravel.log"
echo "   • Review error details in production error page"
echo "   • Ensure all caches are cleared"
echo ""
