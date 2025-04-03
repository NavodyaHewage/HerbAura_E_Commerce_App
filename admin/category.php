<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Add new category
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
        
        $stmt = $link->prepare("INSERT INTO Categories (name, description, parent_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $parent_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Category added successfully";
        } else {
            $_SESSION['error'] = "Error adding category: " . $link->error;
        }
        header("Location: category.php");
        exit;
        
    } elseif (isset($_POST['update_category'])) {
        // Update existing category
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $parent_id = isset($_POST['parent_id']) && !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
        
        // Prevent category from being its own parent
        if ($parent_id == $category_id) {
            $_SESSION['error'] = "A category cannot be its own parent";
            header("Location: category.php");
            exit;
        }
        
        $stmt = $link->prepare("UPDATE Categories SET name = ?, description = ?, parent_id = ? WHERE category_id = ?");
        $stmt->bind_param("ssii", $name, $description, $parent_id, $category_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Category updated successfully";
        } else {
            $_SESSION['error'] = "Error updating category: " . $link->error;
        }
        header("Location: category.php");
        exit;
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Check if category has subcategories
    $check_stmt = $link->prepare("SELECT COUNT(*) FROM Categories WHERE parent_id = ?");
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_stmt->bind_result($child_count);
    $check_stmt->fetch();
    $check_stmt->close();
    
    if ($child_count > 0) {
        $_SESSION['error'] = "Cannot delete category - it has subcategories. Please move or delete them first.";
        header("Location: category.php");
        exit;
    }
    
    // Check if category has products
    $product_stmt = $link->prepare("SELECT COUNT(*) FROM Products WHERE category_id = ?");
    $product_stmt->bind_param("i", $delete_id);
    $product_stmt->execute();
    $product_stmt->bind_result($product_count);
    $product_stmt->fetch();
    $product_stmt->close();
    
    if ($product_count > 0) {
        $_SESSION['error'] = "Cannot delete category - it contains products. Please reassign or delete them first.";
        header("Location: category.php");
        exit;
    }
    
    $stmt = $link->prepare("DELETE FROM Categories WHERE category_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Category deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting category: " . $link->error;
    }
    header("Location: category.php");
    exit;
}

// Fetch all categories with hierarchy information
$categories = [];
$query = "SELECT c1.*, c2.name as parent_name 
          FROM Categories c1 
          LEFT JOIN Categories c2 ON c1.parent_id = c2.category_id 
          ORDER BY COALESCE(c1.parent_id, 0), c1.name ASC";
$result = $link->query($query);
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Prepare category tree for dropdown
// Prepare category tree for dropdown
$categoryTree = [];
$query = "SELECT category_id, name, parent_id FROM Categories ORDER BY COALESCE(parent_id, 0), name";
$treeResult = $link->query($query);
if ($treeResult) {
    $allCategories = $treeResult->fetch_all(MYSQLI_ASSOC);
    
    // First pass: Create all top-level categories
    foreach ($allCategories as $category) {
        if ($category['parent_id'] === NULL) {
            $categoryTree[$category['category_id']] = [
                'name' => $category['name'],
                'children' => []
            ];
        }
    }
    
    // Second pass: Add children to their parents
    foreach ($allCategories as $category) {
        if ($category['parent_id'] !== NULL) {
            // Find parent in the tree
            foreach ($categoryTree as &$parentCategory) {
                if (addChildToParent($parentCategory, $category['parent_id'], $category)) {  // Removed $this->
                    break;
                }
            }
        }
    }
}

// Helper function to recursively add children to parent categories
// Helper function to recursively add children to parent categories
function addChildToParent(&$parentCategory, $parentId, $childCategory) {
    if (!isset($parentCategory['category_id'])) {
        return false;
    }
    
    if ($parentCategory['category_id'] == $parentId) {
        $parentCategory['children'][$childCategory['category_id']] = [
            'name' => $childCategory['name'],
            'children' => []
        ];
        return true;
    }
    
    if (!empty($parentCategory['children'])) {
        foreach ($parentCategory['children'] as &$child) {
            if (addChildToParent($child, $parentId, $childCategory)) {
                return true;
            }
        }
    }
    
    return false;
}

// Function to generate dropdown options recursively
function generateCategoryOptions($tree, $selected = null, $prefix = '') {
    $options = '';
    foreach ($tree as $id => $category) {
        $options .= sprintf(
            '<option value="%d"%s>%s%s</option>',
            $id,
            $selected == $id ? ' selected' : '',
            $prefix,
            htmlspecialchars($category['name'])
        );
        
        if (!empty($category['children'])) {
            $options .= generateCategoryOptions($category['children'], $selected, $prefix . '&nbsp;&nbsp;&nbsp;');
        }
    }
    return $options;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management - HerbAura</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2d5a27;
            --secondary-beige: #f5e6d3;
            --accent-orange: #f4623a;
        }
        
        body {
            font-family: 'Merriweather Sans', sans-serif;
            background-color: #f8f9fa;
            padding-top: 60px;
        }
        
        .table-responsive {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            padding: 1rem;
        }
        
        .table th {
            background-color: var(--primary-green);
            color: white;
            border-color: #1e3d19;
        }
        
        .btn-primary {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        .btn-primary:hover {
            background-color: #1e3d19;
            border-color: #1e3d19;
        }
        
        .btn-warn {
            background-color: var(--accent-orange);
            border-color: var(--accent-orange);
        }
        
        .btn-warn:hover {
            background-color: #d35400;
            border-color: #d35400;
        }
        
        .btn-dan {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-dan:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
        }
        
        /* Modal styling */
        .modal-header {
            background-color: var(--primary-green);
            color: white;
        }
        
        .category-description {
            white-space: pre-wrap;
            max-height: 100px;
            overflow-y: auto;
        }
        
        .category-hierarchy {
            font-weight: bold;
        }
        
        .subcategory {
            padding-left: 30px;
            background-color: #f8f9fa;
        }
        
        .subcategory .category-name::before {
            content: "↳ ";
            color: var(--primary-green);
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Category Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="bi bi-plus-circle"></i> Add Category
                        </button>
                    </div>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Parent</th>
                                <th>Description</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr class="<?= $category['parent_id'] ? 'subcategory' : '' ?>">
                                <td><?= htmlspecialchars($category['category_id']) ?></td>
                                <td class="category-name"><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= $category['parent_name'] ?? '—' ?></td>
                                <td class="category-description"><?= htmlspecialchars($category['description']) ?></td>
                                <td><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                                <td><?= date('M d, Y', strtotime($category['updated_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warn me-2" data-bs-toggle="modal" 
                                        data-bs-target="#editCategoryModal" 
                                        data-categoryid="<?= $category['category_id'] ?>"
                                        data-name="<?= htmlspecialchars($category['name']) ?>"
                                        data-description="<?= htmlspecialchars($category['description']) ?>"
                                        data-parentid="<?= $category['parent_id'] ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <a href="category.php?delete_id=<?= $category['category_id'] ?>" 
                                        class="btn btn-sm btn-dan"
                                        onclick="return confirm('Are you sure you want to delete this category?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="category.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category (optional)</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">— None (Top Level) —</option>
                                <?= generateCategoryOptions($categoryTree) ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_category">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="category.php">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_parent_id" class="form-label">Parent Category (optional)</label>
                            <select class="form-select" id="edit_parent_id" name="parent_id">
                                <option value="">— None (Top Level) —</option>
                                <?= generateCategoryOptions($categoryTree) ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="update_category">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit modal data population
        var editCategoryModal = document.getElementById('editCategoryModal');
        editCategoryModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var categoryId = button.getAttribute('data-categoryid');
            var name = button.getAttribute('data-name');
            var description = button.getAttribute('data-description');
            var parentId = button.getAttribute('data-parentid');
            
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            
            // Set the parent dropdown value
            var parentSelect = document.getElementById('edit_parent_id');
            if (parentId && parentId !== 'null') {
                parentSelect.value = parentId;
            } else {
                parentSelect.value = '';
            }
        });
    </script>
</body>
</html>