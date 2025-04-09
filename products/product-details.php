<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: shop.php');
    exit;
}

$product_id = intval($_GET['id']);

// Handle Add to Cart form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = intval($_POST['quantity']);
    
    // Validate quantity
    if ($quantity < 1 || $quantity > 10) {
        $_SESSION['error'] = "Invalid quantity selected";
        header("Location: product-details.php?id=$product_id");
        exit;
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // $_SESSION['error'] = "Please login to add items to your cart";
        $_SESSION['redirect_after_login'] = "product-details.php?id=$product_id";
        header("Location: ../public/login.php");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if product already exists in cart
    $check_cart = "SELECT ci.* FROM Cart_Items ci
                  JOIN Cart c ON ci.cart_id = c.cart_id
                  WHERE c.user_id = ? AND ci.product_id = ?";
    $stmt = $link->prepare($check_cart);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $existing_item = $stmt->get_result()->fetch_assoc();
    
    if ($existing_item) {
        // Update existing item
        $new_quantity = $existing_item['quantity'] + $quantity;
        $update = "UPDATE Cart_Items SET quantity = ? 
                  WHERE cart_item_id = ?";
        $stmt = $link->prepare($update);
        $stmt->bind_param("ii", $new_quantity, $existing_item['cart_item_id']);
        $stmt->execute();
    } else {
        // Add new item to cart
        // First ensure user has a cart
        $check_cart = "SELECT cart_id FROM Cart WHERE user_id = ?";
        $stmt = $link->prepare($check_cart);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart = $stmt->get_result()->fetch_assoc();
        
        if (!$cart) {
            // Create cart if doesn't exist
            $create_cart = "INSERT INTO Cart (user_id) VALUES (?)";
            $stmt = $link->prepare($create_cart);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $cart_id = $link->insert_id;
        } else {
            $cart_id = $cart['cart_id'];
        }
        
        // Add item to cart
        $insert = "INSERT INTO Cart_Items (cart_id, product_id, quantity) 
                  VALUES (?, ?, ?)";
        $stmt = $link->prepare($insert);
        $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
        $stmt->execute();
    }
    
    $_SESSION['message'] = "Product added to cart successfully!";
    header("Location: product-details.php?id=$product_id");
    exit;
}

// Fetch product details
$product_query = "SELECT p.*, c.name as category_name 
                 FROM Products p 
                 LEFT JOIN Categories c ON p.category_id = c.category_id 
                 WHERE p.product_id = ?";
$stmt = $link->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: shop.php');
    exit;
}

$product = $result->fetch_assoc();
$product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];

// Fetch related products (same category)
$related_query = "SELECT p.* FROM Products p 
                 WHERE p.category_id = ? AND p.product_id != ?
                 LIMIT 4";
$stmt = $link->prepare($related_query);
$stmt->bind_param("ii", $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($related_products as &$related) {
    $related['images'] = !empty($related['images']) ? json_decode($related['images'], true) : [];
}
unset($related);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?= htmlspecialchars($product['name']) ?> - HerbAura</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico" />
    <!-- Bootstrap Icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary-green: #2d5a27;
            --secondary-beige: #f5e6d3;
            --accent-orange: #f4623a;
        }
        
        .product-detail-section {
            padding: 5rem 0;
        }
        
        .product-image-main {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
        }
        
        .product-thumbnails {
            display: flex;
            margin-top: 1rem;
            gap: 0.5rem;
        }
        
        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.25rem;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .product-thumbnail:hover, .product-thumbnail.active {
            border-color: var(--primary-green);
        }
        
        .product-title {
            color: var(--primary-green);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--accent-orange);
            margin-bottom: 1.5rem;
        }
        
        .product-category {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .product-description {
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            margin: 0 0.5rem;
        }
        
        .btn-add-to-cart {
            background-color: var(--primary-green);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-add-to-cart:hover {
            background-color: #1e3d19;
            color: white;
        }
        
        .product-meta {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .related-products {
            background-color: #f8f9fa;
            padding: 4rem 0;
        }
        
        .related-product-card {
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        
        .related-product-img {
            height: 200px;
            object-fit: contain;
            background-color: white;
            padding: 1rem;
        }
        
        .alert {
            margin-bottom: 0;
            border-radius: 0;
        }
        
        @media (max-width: 768px) {
            .product-image-main {
                height: 300px;
            }
            
            .product-detail-section {
                padding: 3rem 0;
            }
            
            .related-products {
                padding: 2rem 0;
            }
        }
    </style>
</head>
<body id="page-top">
    <!-- Navigation -->
    <?php include '../includes/header.php'; ?>

    <!-- Display messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Product Detail Section -->
    <section class="product-detail-section">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6">
                    <img id="mainProductImage" src="<?= !empty($product['images']) ? '../assets/uploads/products/'.htmlspecialchars($product['images'][0]) : '../assets/img/placeholder-product.jpg' ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-image-main">
                    
                    <?php if (count($product['images']) > 1): ?>
                    <div class="product-thumbnails">
                        <?php foreach ($product['images'] as $index => $image): ?>
                            <img src="../assets/uploads/products/<?= htmlspecialchars($image) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?> thumbnail <?= $index + 1 ?>" 
                                 class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                 onclick="changeMainImage(this, '<?= htmlspecialchars($image) ?>')">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Info -->
                <div class="col-lg-6">
                    <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                    <div class="product-price">Rs. <?= number_format($product['price'], 2) ?></div>
                    <div class="product-category">
                        Category: <a href="shop.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></a>
                    </div>
                    
                    <div class="product-description">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>
                    
                    <form method="POST" action="product-details.php?id=<?= $product_id ?>">
                        <input type="hidden" name="add_to_cart" value="1">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        
                        <div class="quantity-selector">
                            <button type="button" class="btn btn-outline-secondary" onclick="decrementQuantity()">-</button>
                            <input type="number" name="quantity" class="form-control quantity-input" value="1" min="1" max="10">
                            <button type="button" class="btn btn-outline-secondary" onclick="incrementQuantity()">+</button>
                        </div>
                        
                        <button type="submit" class="btn btn-add-to-cart">
                            <i class="bi-cart"></i> Add to Cart
                        </button>
                    </form>
                    
                    <div class="product-meta">
                        <p><strong>Availability:</strong> 
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="text-success">In Stock (<?= $product['stock_quantity'] ?> available)</span>
                            <?php else: ?>
                                <span class="text-danger">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Related Products -->
    <section class="related-products">
        <div class="container">
            <h2 class="text-center mb-5">You May Also Like</h2>
            
            <div class="row">
                <?php if (!empty($related_products)): ?>
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="related-product-card">
                                <a href="product-details.php?id=<?= $related['product_id'] ?>">
                                    <img src="<?= !empty($related['images']) ? '../assets/uploads/products/'.htmlspecialchars($related['images'][0]) : '../assets/img/placeholder-product.jpg' ?>" 
                                         alt="<?= htmlspecialchars($related['name']) ?>" 
                                         class="related-product-img w-100">
                                </a>
                                <div class="p-3">
                                    <h5 class="mb-1"><?= htmlspecialchars($related['name']) ?></h5>
                                    <div class="text-primary">Rs. <?= number_format($related['price'], 2) ?></div>
                                    <a href="product-details.php?id=<?= $related['product_id'] ?>" class="btn btn-sm btn-outline-primary mt-2">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No related products found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Change main product image when thumbnail is clicked
        function changeMainImage(thumbnail, imageSrc) {
            document.getElementById('mainProductImage').src = '../assets/uploads/products/' + imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.product-thumbnail').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
        
        // Quantity selector functions
        function incrementQuantity() {
            const input = document.querySelector('.quantity-input');
            if (parseInt(input.value) < 10) {
                input.value = parseInt(input.value) + 1;
            }
        }
        
        function decrementQuantity() {
            const input = document.querySelector('.quantity-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>
</body>
</html>