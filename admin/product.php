<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

// Image upload directory configuration
$uploadDir = '../assets/uploads/products/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Allowed image types
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Fetch all categories with hierarchy for dropdown
$categories = [];
$categoryTree = [];
$category_query = "SELECT c1.category_id, c1.name, c1.parent_id, c2.name as parent_name 
                  FROM Categories c1 
                  LEFT JOIN Categories c2 ON c1.parent_id = c2.category_id 
                  ORDER BY COALESCE(c1.parent_id, 0), c1.name ASC";
$category_result = $link->query($category_query);
if ($category_result) {
    $allCategories = $category_result->fetch_all(MYSQLI_ASSOC);
    
    // Build hierarchical category tree
    foreach ($allCategories as $category) {
        if ($category['parent_id'] === NULL) {
            $categoryTree[$category['category_id']] = [
                'name' => $category['name'],
                'children' => []
            ];
        }
    }
    
    foreach ($allCategories as $category) {
        if ($category['parent_id'] !== NULL && isset($categoryTree[$category['parent_id']])) {
            $categoryTree[$category['parent_id']]['children'][$category['category_id']] = [
                'name' => $category['name'],
                'children' => []
            ];
        }
    }
}

// Function to generate category options with hierarchy
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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add new product
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        
        // Handle image uploads
        $uploadedImages = [];
        
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($_FILES['image_'.$i]['name'])) {
                $fileTmpPath = $_FILES['image_'.$i]['tmp_name'];
                $fileName = $_FILES['image_'.$i]['name'];
                $fileSize = $_FILES['image_'.$i]['size'];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Validate file type
                if (in_array($fileType, $allowedTypes)) {
                    // Generate unique filename
                    $newFileName = md5(time() . $fileName) . '.' . $fileType;
                    $destPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $uploadedImages[] = $newFileName;
                    }
                }
            }
        }
        
        // Convert images array to JSON for storage
        $imagesJson = !empty($uploadedImages) ? json_encode($uploadedImages) : null;
        
        // Insert product into database
        $stmt = $link->prepare("INSERT INTO Products (name, description, price, stock_quantity, category_id, images) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $price, $stock_quantity, $category_id, $imagesJson);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product added successfully with " . count($uploadedImages) . " images";
        } else {
            $_SESSION['error'] = "Error adding product: " . $link->error;
        }
        header("Location: product.php");
        exit;
        
    } elseif (isset($_POST['update_product'])) {
        // Update existing product
        $product_id = intval($_POST['product_id']);
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        
        // Handle image uploads for update
        $existingImages = [];
        if (!empty($_POST['existing_images'])) {
            $existingImages = json_decode($_POST['existing_images'], true);
        }
        
        $uploadedImages = [];
        
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($_FILES['image_'.$i]['name'])) {
                $fileTmpPath = $_FILES['image_'.$i]['tmp_name'];
                $fileName = $_FILES['image_'.$i]['name'];
                $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                if (in_array($fileType, $allowedTypes)) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileType;
                    $destPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $uploadedImages[] = $newFileName;
                    }
                }
            }
        }
        
        // Combine existing and new images
        $allImages = array_merge($existingImages, $uploadedImages);
        $imagesJson = !empty($allImages) ? json_encode($allImages) : null;
        
        $stmt = $link->prepare("UPDATE Products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, images = ? WHERE product_id = ?");
        $stmt->bind_param("ssdiisi", $name, $description, $price, $stock_quantity, $category_id, $imagesJson, $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product updated successfully";
        } else {
            $_SESSION['error'] = "Error updating product: " . $link->error;
        }
        header("Location: product.php");
        exit;
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // First get images to delete them from server
    $stmt = $link->prepare("SELECT images FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        if (!empty($product['images'])) {
            $images = json_decode($product['images'], true);
            foreach ($images as $image) {
                $filePath = $uploadDir . $image;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
    
    // Now delete the product
    $stmt = $link->prepare("DELETE FROM Products WHERE product_id = ?");
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Product deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting product: " . $link->error;
    }
    header("Location: product.php");
    exit;
}

// Fetch all products with category names
$products = [];
$query = "SELECT p.*, c.name as category_name 
          FROM Products p 
          LEFT JOIN Categories c ON p.category_id = c.category_id 
          ORDER BY p.created_at DESC";
$result = $link->query($query);
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
    
    // Decode images JSON for each product
    foreach ($products as &$product) {
        $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
    }
    unset($product); // Break the reference
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - HerbAura</title>
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
        
        .product-description {
            white-space: pre-wrap;
            max-height: 100px;
            overflow-y: auto;
        }
        
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
        
        /* Image upload styles */
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        
        .image-upload-box {
            border: 2px dashed #ccc;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .image-upload-box:hover {
            border-color: var(--primary-green);
            background-color: #f0f0f0;
        }
        
        .existing-images {
            margin-bottom: 20px;
        }
        
        .image-thumbnail {
            position: relative;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Product Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-circle"></i> Add Product
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
                                <th>Images</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Category</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= htmlspecialchars($product['product_id']) ?></td>
                                <td>
                                    <?php if (!empty($product['images'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_slice($product['images'], 0, 3) as $image): ?>
                                                <img src="<?= $uploadDir . htmlspecialchars($image) ?>" class="product-image" alt="Product Image">
                                            <?php endforeach; ?>
                                            <?php if (count($product['images']) > 3): ?>
                                                <span class="badge bg-secondary">+<?= count($product['images']) - 3 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No images</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td class="product-description"><?= htmlspecialchars($product['description']) ?></td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                                <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                                <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                                <td><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warn me-2" data-bs-toggle="modal" 
                                        data-bs-target="#editProductModal" 
                                        data-productid="<?= $product['product_id'] ?>"
                                        data-name="<?= htmlspecialchars($product['name']) ?>"
                                        data-description="<?= htmlspecialchars($product['description']) ?>"
                                        data-price="<?= htmlspecialchars($product['price']) ?>"
                                        data-stockquantity="<?= htmlspecialchars($product['stock_quantity']) ?>"
                                        data-categoryid="<?= htmlspecialchars($product['category_id'] ?? '') ?>"
                                        data-images="<?= htmlspecialchars(json_encode($product['images'])) ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <a href="product.php?delete_id=<?= $product['product_id'] ?>" 
                                        class="btn btn-sm btn-dan"
                                        onclick="return confirm('Are you sure you want to delete this product? All images will be permanently deleted.')">
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

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="product.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">-- Select Category --</option>
                                        <?= generateCategoryOptions($categoryTree) ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Upload Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Product Images</h5>
                                <p class="text-muted">Upload up to 5 images (JPEG, PNG, GIF, WEBP)</p>
                                
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="image-upload-box mb-3">
                                    <label for="image_<?= $i ?>" class="form-label">Image <?= $i ?></label>
                                    <input type="file" class="form-control" id="image_<?= $i ?>" name="image_<?= $i ?>" accept="image/*">
                                    <div class="image-preview-container" id="preview_<?= $i ?>"></div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="add_product">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="product.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <input type="hidden" name="existing_images" id="edit_existing_images">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_category_id" class="form-label">Category</label>
                                    <select class="form-select" id="edit_category_id" name="category_id">
                                        <option value="">-- Select Category --</option>
                                        <?= generateCategoryOptions($categoryTree) ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_stock_quantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="edit_stock_quantity" name="stock_quantity" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Existing Images Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Current Images</h5>
                                <div class="existing-images" id="edit_existing_images_container">
                                    <!-- Existing images will be loaded here -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- New Image Upload Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <h5>Add New Images</h5>
                                <p class="text-muted">Upload up to 5 additional images</p>
                                
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="image-upload-box mb-3">
                                    <label for="edit_image_<?= $i ?>" class="form-label">New Image <?= $i ?></label>
                                    <input type="file" class="form-control" id="edit_image_<?= $i ?>" name="image_<?= $i ?>" accept="image/*">
                                    <div class="image-preview-container" id="edit_preview_<?= $i ?>"></div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="update_product">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview functionality for add modal
        document.addEventListener('DOMContentLoaded', function() {
            <?php for ($i = 1; $i <= 5; $i++): ?>
            document.getElementById('image_<?= $i ?>').addEventListener('change', function(event) {
                const file = event.target.files[0];
                const previewContainer = document.getElementById('preview_<?= $i ?>');
                
                if (file) {
                    previewContainer.innerHTML = '';
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        previewContainer.appendChild(img);
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.innerHTML = '';
                }
            });
            <?php endfor; ?>
            
            // Image preview functionality for edit modal
            <?php for ($i = 1; $i <= 5; $i++): ?>
            document.getElementById('edit_image_<?= $i ?>').addEventListener('change', function(event) {
                const file = event.target.files[0];
                const previewContainer = document.getElementById('edit_preview_<?= $i ?>');
                
                if (file) {
                    previewContainer.innerHTML = '';
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        previewContainer.appendChild(img);
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.innerHTML = '';
                }
            });
            <?php endfor; ?>
        });

        // Handle edit modal data population
        var editProductModal = document.getElementById('editProductModal');
        editProductModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var productId = button.getAttribute('data-productid');
            var name = button.getAttribute('data-name');
            var description = button.getAttribute('data-description');
            var price = button.getAttribute('data-price');
            var stockQuantity = button.getAttribute('data-stockquantity');
            var categoryId = button.getAttribute('data-categoryid');
            var imagesJson = button.getAttribute('data-images');
            
            document.getElementById('edit_product_id').value = productId;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_stock_quantity').value = stockQuantity;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_existing_images').value = imagesJson;
            
            // Clear previous previews
            for (let i = 1; i <= 5; i++) {
                document.getElementById('edit_preview_' + i).innerHTML = '';
            }
            
            // Load existing images
            const existingImagesContainer = document.getElementById('edit_existing_images_container');
            existingImagesContainer.innerHTML = '';
            
            if (imagesJson) {
                const images = JSON.parse(imagesJson);
                
                if (images.length > 0) {
                    images.forEach((image, index) => {
                        const imageDiv = document.createElement('div');
                        imageDiv.className = 'image-thumbnail';
                        
                        const img = document.createElement('img');
                        img.src = '../assets/uploads/products/' + image;
                        img.className = 'image-preview';
                        
                        const removeBtn = document.createElement('span');
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '&times;';
                        removeBtn.onclick = function() {
                            // Remove image from the array
                            const updatedImages = images.filter((_, i) => i !== index);
                            document.getElementById('edit_existing_images').value = JSON.stringify(updatedImages);
                            imageDiv.remove();
                        };
                        
                        imageDiv.appendChild(img);
                        imageDiv.appendChild(removeBtn);
                        existingImagesContainer.appendChild(imageDiv);
                    });
                } else {
                    existingImagesContainer.innerHTML = '<p class="text-muted">No images available</p>';
                }
            } else {
                existingImagesContainer.innerHTML = '<p class="text-muted">No images available</p>';
            }
        });
    </script>
</body>
</html>