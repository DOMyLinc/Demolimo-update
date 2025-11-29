-- SQL Script to Enable Lava Theme for Admin and Frontend
-- Run this after your database is configured and migrations are complete

-- Update Lava theme to support admin panel
UPDATE themes 
SET supports_admin = 1,
    is_active = 1,
    is_default = 1
WHERE name = 'lava';

-- Deactivate other themes
UPDATE themes 
SET is_active = 0,
    is_default = 0
WHERE name != 'lava';

-- Verify the update
SELECT name, display_name, is_active, is_default, supports_admin, supports_landing_page 
FROM themes;
