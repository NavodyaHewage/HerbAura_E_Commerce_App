<?php 
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Fetch all categories with hierarchy
$categories = [];
$query = "SELECT c1.category_id, c1.name, c1.parent_id, c2.name as parent_name 
          FROM Categories c1 
          LEFT JOIN Categories c2 ON c1.parent_id = c2.category_id 
          ORDER BY COALESCE(c1.parent_id, 0), c1.name ASC";
$result = $link->query($query);
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Organize categories into parent-child structure
$category_tree = [];
foreach ($categories as $category) {
    if ($category['parent_id'] === null) {
        $category_tree[$category['category_id']] = [
            'name' => $category['name'],
            'children' => []
        ];
    }
}

foreach ($categories as $category) {
    if ($category['parent_id'] !== null && isset($category_tree[$category['parent_id']])) {
        $category_tree[$category['parent_id']]['children'][] = [
            'category_id' => $category['category_id'],
            'name' => $category['name']
        ];
    }
}

// Get selected category from URL
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : null;

// Build product query
$product_query = "SELECT p.*, c.name as category_name 
                 FROM Products p 
                 LEFT JOIN Categories c ON p.category_id = c.category_id 
                 WHERE 1=1";

if ($selected_category) {
    $product_query .= " AND (p.category_id = $selected_category OR c.parent_id = $selected_category)";
}

// Add sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
switch ($sort) {
    case 'price_asc':
        $product_query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $product_query .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $product_query .= " ORDER BY p.name ASC";
        break;
    default: // newest
        $product_query .= " ORDER BY p.created_at DESC";
}

// Execute product query
$products = [];
$product_result = $link->query($product_query);
if ($product_result) {
    $products = $product_result->fetch_all(MYSQLI_ASSOC);
    
    // Decode images JSON for each product
    foreach ($products as &$product) {
        $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
    }
    unset($product); // Break the reference
}

// Get new arrivals (latest 10 products)
$new_arrivals = [];
$new_arrivals_query = "SELECT p.*, c.name as category_name 
                      FROM Products p 
                      LEFT JOIN Categories c ON p.category_id = c.category_id 
                      ORDER BY p.created_at DESC LIMIT 10";
$new_arrivals_result = $link->query($new_arrivals_query);
if ($new_arrivals_result) {
    $new_arrivals = $new_arrivals_result->fetch_all(MYSQLI_ASSOC);
    
    foreach ($new_arrivals as &$product) {
        $product['images'] = !empty($product['images']) ? json_decode($product['images'], true) : [];
    }
    unset($product);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Shop authentic Ayurvedic products from HerbAura" />
    <title>Shop - HerbAura</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico" />
    <!-- Bootstrap Icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <!-- Slick Carousel CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <style>
        :root {
            --primary-green: #2d5a27;
            --secondary-beige: #f5e6d3;
            --accent-orange: #f4623a;
        }
        
        .shop-header {
            background: linear-gradient(rgba(45, 90, 39, 0.8), rgba(45, 90, 39, 0.8)),
                        url('../assets/img/herbal-bg.jpg') center/cover;
            padding: 5rem 0;
            color: white;
        }
        
        /* Horizontal Category Navigation */
        .category-nav {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .category-menu {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
        }
        
        .category-menu > li {
            position: relative;
        }
        
        .category-menu > li > a {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .category-menu > li > a:hover,
        .category-menu > li > a.active {
            background-color: var(--secondary-beige);
        }
        
        .category-menu > li:hover > .submenu {
            display: block;
        }
        
        .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            min-width: 200px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1000;
            list-style: none;
            padding: 0.5rem 0;
        }
        
        .submenu li a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .submenu li a:hover {
            background-color: var(--secondary-beige);
            color: var(--primary-green);
        }
        
        .product-card {
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        .product-img-container {
            height: 200px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        
        .product-img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-img {
            transform: scale(1.05);
        }
        
        .product-body {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-title {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            color: var(--primary-green);
        }
        
        .product-price {
            font-weight: 600;
            color: var(--accent-orange);
            margin-bottom: 1rem;
        }
        
        .product-category {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .product-rating {
            color: #ffc107;
            margin-bottom: 1rem;
        }
        
        .btn-add-to-cart {
            background-color: var(--primary-green);
            color: white;
            border: none;
            margin-top: auto;
        }
        
        .btn-add-to-cart:hover {
            background-color: #1e3d19;
            color: white;
        }
        
        .new-arrivals {
            background-color: #f8f9fa;
            padding: 3rem 0;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            color: var(--primary-green);
        }
        
        .section-title:after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background-color: var(--accent-orange);
        }
        
        .sort-dropdown {
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        }
        
        /* Slick carousel overrides */
        .slick-slide {
            padding: 0 15px;
        }
        
        .slick-prev:before, .slick-next:before {
            color: var(--primary-green);
        }
        
        .slick-dots li button:before {
            color: var(--primary-green);
        }
        
        .slick-dots li.slick-active button:before {
            color: var(--primary-green);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .category-menu {
                flex-wrap: wrap;
            }
            
            .category-menu > li {
                width: 50%;
            }
            
            .submenu {
                position: static;
                box-shadow: none;
            }
        }
        
        @media (max-width: 768px) {
            .product-img-container {
                height: 150px;
            }
            
            .category-menu > li {
                width: 100%;
            }
        }
    </style>
</head>
<body id="page-top">
    <!-- Include Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Shop Header -->
    <header class="shop-header text-center">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8 mt-5">
                    <h1 class="display-4 fw-bold text-white">HerbAura Shop</h1>
                    <p class="lead text-white-75 mb-0">Discover Authentic Ayurvedic Wellness Products</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <!-- Horizontal Category Navigation -->
            <nav class="category-nav">
                <ul class="category-menu">
                    <li><a href="shop.php" class="<?= !$selected_category ? 'active' : '' ?>">All Products</a></li>
                    <?php foreach ($category_tree as $parent_id => $parent_category): ?>
                        <li>
                            <a href="shop.php?category=<?= $parent_id ?>" 
                               class="<?= $selected_category == $parent_id ? 'active' : '' ?>">
                                <?= htmlspecialchars($parent_category['name']) ?>
                                <?php if (!empty($parent_category['children'])): ?>
                                    <i class="bi bi-chevron-down ms-1"></i>
                                <?php endif; ?>
                            </a>
                            <?php if (!empty($parent_category['children'])): ?>
                                <ul class="submenu">
                                    <?php foreach ($parent_category['children'] as $child): ?>
                                        <li>
                                            <a href="shop.php?category=<?= $child['category_id'] ?>" 
                                               class="<?= $selected_category == $child['category_id'] ? 'active' : '' ?>">
                                                <?= htmlspecialchars($child['name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            
            <div class="row">
                <!-- Product Listing -->
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title">
                            <?= $selected_category ? 
                                htmlspecialchars($categories[array_search($selected_category, array_column($categories, 'category_id'))]['name']) . ' Products' : 
                                'All Products' ?>
                        </h2>
                        <div class="sort-dropdown">
                            <select class="form-select" id="sortSelect" onchange="window.location.href='shop.php?category=<?= $selected_category ?>&sort='+this.value">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name: A-Z</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <?php if (empty($products)): ?>
                            <div class="col-12 text-center py-5">
                                <h4>No products found in this category</h4>
                                <a href="shop.php" class="btn btn-primary">Browse All Products</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="product-card">
                                        <div class="product-img-container">
                                            <?php if (!empty($product['images'])): ?>
                                                <img src="../assets/uploads/products/<?= htmlspecialchars($product['images'][0]) ?>" 
                                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                                     class="product-img">
                                            <?php else: ?>
                                                <img src="../assets/img/placeholder-product.jpg" 
                                                     alt="Product placeholder" 
                                                     class="product-img">
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-body">
                                            <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                                            <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                            <div class="product-rating mb-2">
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-half"></i>
                                            </div>
                                            <div class="product-price">
                                                Rs. <?= number_format($product['price'], 2) ?>
                                            </div>
                                            <a href="product-details.php?id=<?= $product['product_id'] ?>" class="btn btn-add-to-cart mt-3">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="new-arrivals py-5">
        <div class="container px-4 px-lg-5">
            <h2 class="text-center section-title">New Arrivals</h2>
            <p class="text-center mb-5">Discover our latest Ayurvedic wellness products</p>
            
            <div class="new-arrivals-slider">
                <?php foreach ($new_arrivals as $product): ?>
                    <div class="px-2">
                        <div class="product-card">
                            <div class="product-img-container">
                                <?php if (!empty($product['images'])): ?>
                                    <img src="../assets/uploads/products/<?= htmlspecialchars($product['images'][0]) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="product-img">
                                <?php else: ?>
                                    <img src="../assets/img/placeholder-product.jpg" 
                                         alt="Product placeholder" 
                                         class="product-img">
                                <?php endif; ?>
                            </div>
                            <div class="product-body">
                                <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span>
                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="product-rating mb-2">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                                <div class="product-price">
                                    Rs. <?= number_format($product['price'], 2) ?>
                                </div>
                                <a href="product-details.php?id=<?= $product['product_id'] ?>" class="btn btn-add-to-cart mt-3">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Slick Carousel JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    
    <script>
        // Initialize Slick Carousel for new arrivals
        $(document).ready(function(){
            $('.new-arrivals-slider').slick({
                dots: true,
                infinite: true,
                speed: 300,
                slidesToShow: 4,
                slidesToScroll: 1,
                responsive: [
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1
                        }
                    },
                    {
                        breakpoint: 576,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        });
    </script>
</body>
</html>