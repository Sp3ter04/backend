-- ============================================
-- Migration: Update created_by for existing exercises
-- ============================================
-- Date: 2024
-- Description: Set created_by = 'admin@gmail.com' for all existing exercises where it's NULL
-- ============================================

-- Step 1: Update existing NULL records
UPDATE exercises 
SET created_by = 'admin@gmail.com' 
WHERE created_by IS NULL;

-- Step 2: Verify the update
SELECT 
    COUNT(*) as total_exercises,
    COUNT(CASE WHEN created_by = 'admin@gmail.com' THEN 1 END) as admin_exercises,
    COUNT(CASE WHEN created_by IS NULL THEN 1 END) as null_exercises
FROM exercises;

-- Expected result: null_exercises should be 0

-- ============================================
-- END OF MIGRATION
-- ============================================
