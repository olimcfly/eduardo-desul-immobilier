-- Migration: Add role column to admins table
-- Description: Add auth_admin_role column to support RBAC
-- Status: v1.0

-- Add role column to admins table if it doesn't exist
ALTER TABLE `admins` ADD COLUMN `role` VARCHAR(50) DEFAULT 'admin' AFTER `password`;

-- Set existing admins to admin role (super users)
UPDATE `admins` SET `role` = 'admin' WHERE `role` IS NULL OR `role` = '';

-- Create index for role-based queries
ALTER TABLE `admins` ADD INDEX `idx_role` (`role`);

-- Add column for tracking role changes
ALTER TABLE `admins` ADD COLUMN `role_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `role`;
