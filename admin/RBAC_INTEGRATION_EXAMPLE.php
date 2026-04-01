<?php
/**
 * RBAC Integration Example
 *
 * This file demonstrates how to integrate RBAC into admin modules.
 * Copy this pattern to your modules.
 *
 * Usage:
 *   1. Copy this template to your module
 *   2. Replace 'content_pages' with your actual module identifier
 *   3. Replace 'Pages' with your module name
 *   4. Add your module logic
 */

// ══════════════════════════════════════════════════════════════
// STEP 1: Load RBAC Helper
// ══════════════════════════════════════════════════════════════
require_once __DIR__ . '/includes/rbac-check.php';

// ══════════════════════════════════════════════════════════════
// STEP 2: Check Base Permission (VIEW)
// ══════════════════════════════════════════════════════════════
// This checks that the user has at least VIEW permission for this module
checkModuleAccess('content_pages', RbacManager::PERM_VIEW);

// Get current user info
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

?>

<!-- Example HTML with conditional display based on permissions -->
<div class="module-container">
    <h1>Pages Module</h1>
    <p>Welcome back, <?= htmlspecialchars($userRole) ?> user!</p>

    <!-- Display list of items -->
    <section class="items-list">
        <h2>Pages</h2>

        <!-- CREATE Button - only show if user has create permission -->
        <?php if (hasModuleAccess('content_pages', RbacManager::PERM_CREATE)): ?>
            <a href="?page=pages&action=create" class="btn btn-primary">+ New Page</a>
        <?php endif; ?>

        <!-- Items table -->
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample items (replace with actual data) -->
                <tr>
                    <td>Home Page</td>
                    <td>home</td>
                    <td><span class="badge badge-published">Published</span></td>
                    <td>
                        <!-- EDIT - show if user has edit permission -->
                        <?php if (hasModuleAccess('content_pages', RbacManager::PERM_EDIT)): ?>
                            <a href="?page=pages&action=edit&id=1" class="btn btn-sm btn-secondary">Edit</a>
                        <?php endif; ?>

                        <!-- DELETE - show if user has delete permission -->
                        <?php if (hasModuleAccess('content_pages', RbacManager::PERM_DELETE)): ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(1)">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- Settings - only show if user has manage permission -->
    <?php if (hasModuleAccess('content_pages', RbacManager::PERM_MANAGE)): ?>
        <section class="module-settings">
            <h3>Module Settings</h3>
            <form method="post">
                <input type="hidden" name="action" value="save_settings">
                <label>
                    Items per page:
                    <input type="number" name="items_per_page" value="20" min="5" max="100">
                </label>
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </section>
    <?php endif; ?>
</div>

<?php
// ══════════════════════════════════════════════════════════════
// STEP 3: Handle POST Actions with Permission Checks
// ══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE action
    if ($action === 'create') {
        // Check that user has CREATE permission
        if (!hasModuleAccess('content_pages', RbacManager::PERM_CREATE)) {
            showAccessDenied('content_pages', 'create', 'You do not have permission to create pages');
            exit;
        }

        // Handle create logic
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $_SESSION['error'] = 'Title is required';
        } else {
            // Insert page...
            $_SESSION['success'] = 'Page created successfully';
            header('Location: ?page=pages');
            exit;
        }
    }

    // EDIT action
    else if ($action === 'edit') {
        // Check that user has EDIT permission
        if (!hasModuleAccess('content_pages', RbacManager::PERM_EDIT)) {
            showAccessDenied('content_pages', 'edit', 'You do not have permission to edit pages');
            exit;
        }

        // Handle edit logic
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $_SESSION['error'] = 'Title is required';
        } else {
            // Update page...
            $_SESSION['success'] = 'Page updated successfully';
            header('Location: ?page=pages');
            exit;
        }
    }

    // DELETE action
    else if ($action === 'delete') {
        // Check that user has DELETE permission
        if (!hasModuleAccess('content_pages', RbacManager::PERM_DELETE)) {
            showAccessDenied('content_pages', 'delete', 'You do not have permission to delete pages');
            exit;
        }

        // Handle delete logic
        $id = (int)($_POST['id'] ?? 0);
        // Delete page...
        $_SESSION['success'] = 'Page deleted successfully';
        header('Location: ?page=pages');
        exit;
    }

    // MANAGE action
    else if ($action === 'save_settings') {
        // Check that user has MANAGE permission
        if (!hasModuleAccess('content_pages', RbacManager::PERM_MANAGE)) {
            showAccessDenied('content_pages', 'manage', 'You do not have permission to manage settings');
            exit;
        }

        // Handle settings save
        $_SESSION['success'] = 'Settings saved successfully';
        header('Location: ?page=pages');
        exit;
    }
}

// ══════════════════════════════════════════════════════════════
// STEP 4: Advanced - Check Multiple Modules
// ══════════════════════════════════════════════════════════════
/*
// Example: Show content admin section if user can manage ANY content module
if (hasModuleAccess('content_pages') ||
    hasModuleAccess('content_articles') ||
    hasModuleAccess('content_blog')) {
    echo '<div class="content-admin-section">...</div>';
}
*/

// ══════════════════════════════════════════════════════════════
// STEP 5: Use in Controllers (if using BaseController)
// ══════════════════════════════════════════════════════════════
/*
class PageController extends BaseController {
    public function create() {
        // Check permission - throws exception if denied
        $this->requireRole('content_pages', RbacManager::PERM_CREATE);

        // Rest of create logic...
    }

    public function edit($id) {
        // Check permission
        $this->requireRole('content_pages', RbacManager::PERM_EDIT);

        // Rest of edit logic...
    }

    public function delete($id) {
        // Check permission
        $this->requireRole('content_pages', RbacManager::PERM_DELETE);

        // Rest of delete logic...
    }
}
*/

?>

<style>
.module-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    font-weight: 600;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
}

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover {
    background: #d1d5db;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

table th {
    background: #f9fafb;
    font-weight: 600;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-published {
    background: #d1fae5;
    color: #065f46;
}

.badge-draft {
    background: #f1f5f9;
    color: #64748b;
}
</style>
