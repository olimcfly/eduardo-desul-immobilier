# 🔐 RBAC (Role-Based Access Control) Guide

## Overview

This guide explains how to implement role-based access control (RBAC) in the Eduardo Desul Immobilier CRM application.

The RBAC system provides:
- **Role-based access control** for admin modules
- **Permission mapping** for different operations (view, create, edit, delete, manage)
- **Security checks** at the module level
- **Audit logging** of access attempts

---

## 📋 Roles & Permissions

### Available Roles

| Role | Description | Use Case |
|------|-------------|----------|
| **admin** | Full access to all modules | System administrator, owner |
| **moderator** | Content & marketing management | Content managers, marketing team |
| **editor** | Content creation & editing only | Article writers, content creators |
| **viewer** | Read-only access | Managers, supervisors |

### Available Permissions

| Permission | Level | Description |
|-----------|-------|-------------|
| `view` | 1 | Read/view data (default) |
| `create` | 2 | Create new records |
| `edit` | 3 | Modify existing records |
| `delete` | 4 | Delete records |
| `manage` | 5 | Manage settings & configuration |

### Permission Matrix

```
             Admin  Moderator  Editor  Viewer
CRM          5      4          -       1
Pages        5      5          3       1
Articles     5      5          3       1
Marketing    5      5          -       -
SEO          5      3          -       1
Analytics    5      -          -       1
Settings     5      -          -       -
```

---

## 🚀 Implementation

### Step 1: Initialize RBAC in Admin Module

Add this to the beginning of each admin module (`/admin/modules/*/index.php`):

```php
<?php
/**
 * Module: Your Module Name
 * Admin Module for managing XYZ
 */

// Load RBAC helper
require_once dirname(__DIR__) . '/includes/rbac-check.php';

// Check permission (choose appropriate permission level)
checkModuleAccess('content_pages', RbacManager::PERM_VIEW);

// Rest of your module code...
$pageTitle = "Pages";
// ... continue with module logic
```

### Step 2: Check Specific Actions

For specific actions within a module, use conditional checks:

```php
<?php
require_once dirname(__DIR__) . '/includes/rbac-check.php';
checkModuleAccess('content_pages', RbacManager::PERM_VIEW);

// View pages - allowed for all roles with permission
$pages = $pdo->query("SELECT * FROM pages")->fetchAll();

// Create new page - check create permission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!hasModuleAccess('content_pages', RbacManager::PERM_CREATE)) {
        showAccessDenied('content_pages', 'create');
        exit;
    }
    // Create logic...
}

// Edit page - check edit permission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!hasModuleAccess('content_pages', RbacManager::PERM_EDIT)) {
        showAccessDenied('content_pages', 'edit');
        exit;
    }
    // Edit logic...
}

// Delete page - check delete permission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!hasModuleAccess('content_pages', RbacManager::PERM_DELETE)) {
        showAccessDenied('content_pages', 'delete');
        exit;
    }
    // Delete logic...
}
```

### Step 3: Use RbacManager in Controllers

In your BaseController subclasses:

```php
<?php
class PageController extends BaseController
{
    public function actionView()
    {
        // Check view permission
        $this->requireRole('content_pages', RbacManager::PERM_VIEW);
        
        // View logic...
        return $pages;
    }

    public function actionCreate()
    {
        // Check create permission
        $this->requireRole('content_pages', RbacManager::PERM_CREATE);
        
        // Create logic...
        return ['success' => true, 'id' => $newId];
    }

    public function actionEdit($id)
    {
        // Check edit permission
        $this->requireRole('content_pages', RbacManager::PERM_EDIT);
        
        // Edit logic...
        return ['success' => true];
    }

    public function actionDelete($id)
    {
        // Check delete permission
        $this->requireRole('content_pages', RbacManager::PERM_DELETE);
        
        // Delete logic...
        return ['success' => true];
    }
}
```

---

## 📚 Module Identifiers

### Content Management
- `content_pages` - Web pages
- `content_articles` - Articles & blog posts
- `content_guide` - Guides & documentation
- `content_secteurs` - Sector/market pages
- `content_blog` - Blog module
- `content_captures` - Landing page captures

### CRM & Marketing
- `crm` - CRM contacts & clients
- `messagerie` - Internal messaging
- `marketing_sequences` - Email sequences
- `marketing_leads` - Lead management
- `marketing_ads` - Advertising campaigns

### Social & Network
- `social_linkedin` - LinkedIn integration
- `social_instagram` - Instagram integration
- `gmb` - Google My Business
- `network` - Network contacts

### System
- `seo` - SEO management
- `analytics` - Analytics & reporting
- `system_settings` - Admin settings
- `system_users` - User management
- `system_maintenance` - Maintenance mode
- `ai` - AI integrations
- `licenses` - License management

---

## 🔒 Security Features

### 1. Authentication Check
```php
$userId = getCurrentUserId(); // Get logged-in user ID
$userRole = getCurrentUserRole(); // Get user's role
```

### 2. Permission Check
```php
if (hasModuleAccess('content_pages', RbacManager::PERM_EDIT)) {
    // User can edit pages
}
```

### 3. Audit Logging
All access attempts are logged to `/logs/rbac-access.log`:
```
[2026-04-01 14:30:45] [ALLOWED] User=1 | Role=admin | Module=content_pages | Permission=edit | IP=192.168.1.1
[2026-04-01 14:31:12] [DENIED] User=5 | Role=viewer | Module=system_users | Permission=delete | IP=192.168.1.2
```

### 4. Error Handling
```php
// Display user-friendly error
showAccessDenied('content_pages', 'delete', 'You cannot delete pages with this role');
```

---

## 🎯 Integration Checklist

- [ ] Add RBAC check to `/admin/modules/crm/`
- [ ] Add RBAC check to `/admin/modules/content/`
- [ ] Add RBAC check to `/admin/modules/builder/`
- [ ] Add RBAC check to `/admin/modules/marketing/`
- [ ] Add RBAC check to `/admin/modules/social/`
- [ ] Add RBAC check to `/admin/modules/seo/`
- [ ] Add RBAC check to `/admin/modules/system/`
- [ ] Add RBAC check to `/admin/modules/ai/`
- [ ] Add RBAC check to `/admin/modules/strategy/`
- [ ] Add RBAC check to `/admin/modules/network/`
- [ ] Add RBAC check to `/admin/modules/license/`
- [ ] Add RBAC check to all API endpoints (`/admin/api/`)
- [ ] Update user management to include role assignment
- [ ] Create role assignment interface
- [ ] Test with different user roles
- [ ] Review audit logs

---

## 🧪 Testing

### Test 1: Admin Access
```php
$_SESSION['auth_admin_role'] = 'admin';
checkModuleAccess('content_pages'); // Should pass
```

### Test 2: Moderator Create
```php
$_SESSION['auth_admin_role'] = 'moderator';
checkModuleAccess('content_pages', RbacManager::PERM_CREATE); // Should pass
checkModuleAccess('system_users', RbacManager::PERM_DELETE); // Should fail
```

### Test 3: Editor Delete
```php
$_SESSION['auth_admin_role'] = 'editor';
checkModuleAccess('content_articles', RbacManager::PERM_EDIT); // Should pass
checkModuleAccess('content_articles', RbacManager::PERM_DELETE); // Should fail
```

### Test 4: Viewer Permissions
```php
$_SESSION['auth_admin_role'] = 'viewer';
checkModuleAccess('analytics', RbacManager::PERM_VIEW); // Should pass
checkModuleAccess('analytics', RbacManager::PERM_EDIT); // Should fail
```

---

## 🔧 Advanced Usage

### Check Multiple Modules
```php
// User must have permission for at least one of these modules
$this->requireAnyRole(['content_pages', 'content_articles'], RbacManager::PERM_EDIT);
```

### Custom Permission Checking
```php
// Get user's permissions for a module
$hasView = RbacManager::hasPermission($userRole, 'content_pages', RbacManager::PERM_VIEW);
$hasEdit = RbacManager::hasPermission($userRole, 'content_pages', RbacManager::PERM_EDIT);

if ($hasView && !$hasEdit) {
    // User can view but not edit
}
```

### Role Information
```php
// Get all available roles
$roles = RbacManager::getAllRoles();
// Returns: ['admin' => 'Administrator', 'moderator' => 'Moderator', ...]

// Get role label
$label = RbacManager::getRoleLabel('editor');
// Returns: 'Editor'
```

---

## 📊 Configuration

### Add New Module
Edit `/includes/classes/RbacManager.php` to add your module:

```php
private static $moduleMap = [
    'my_custom_module' => ['my-module'], // Admin page names
    // ... other modules
];

private static $permissions = [
    RbacManager::ROLE_ADMIN => [
        'my_custom_module' => [
            self::PERM_VIEW, self::PERM_CREATE, self::PERM_EDIT, 
            self::PERM_DELETE, self::PERM_MANAGE
        ],
        // ... other modules
    ],
    // ... other roles
];
```

### Modify Permissions
To change which roles can access which modules, edit the `$permissions` array in `RbacManager.php`.

---

## 🚨 Common Issues

### Issue: "Access Denied" for Admin
**Cause:** Admin role not set in session
**Solution:** Verify `$_SESSION['auth_admin_role']` is set to `'admin'` during login

### Issue: Permission check not working
**Cause:** Module identifier not in `$moduleMap`
**Solution:** Ensure module is registered in both `$moduleMap` and `$permissions`

### Issue: Logs not being created
**Cause:** `/logs/` directory doesn't exist or isn't writable
**Solution:** Create directory: `mkdir -p /logs` with proper permissions: `chmod 755 /logs`

---

## 📝 Best Practices

1. **Always check permissions** - Add RBAC checks to all sensitive operations
2. **Use meaningful module names** - Follow the naming convention `category_subcategory`
3. **Log access attempts** - Review `/logs/rbac-access.log` regularly
4. **Test with all roles** - Ensure each role can only do what's intended
5. **Don't hardcode roles** - Use `RbacManager` constants instead
6. **Combine with validation** - RBAC + input validation = secure system
7. **Document role requirements** - Mark required permissions in comments

---

## 📞 Support

For issues or questions about RBAC implementation, check:
- `/includes/classes/RbacManager.php` - Core RBAC logic
- `/includes/classes/BaseController.php` - Controller integration
- `/admin/includes/rbac-check.php` - Helper functions
- `/logs/rbac-access.log` - Access audit trail

---

**Last Updated:** 2026-04-01  
**Version:** 1.0  
**Status:** Production Ready ✅
