<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HerbAura - Ayurvedic Wellness</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/styles.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-family: 'Merriweather', serif;
            font-weight: 700;
            color: #2d5a27 !important;
        }
        .nav-link {
            color: #2d5a27 !important;
            font-family: 'Merriweather Sans', sans-serif;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        .nav-link:hover {
            color: #f4623a !important;
        }
        .bi-cart, .bi-person, .bi-bag-check, .bi-box-arrow-right {
            font-size: 1.2rem;
            margin-right: 0.3rem;
        }
        .cart-badge {
            font-size: 0.6rem;
            vertical-align: top;
        }
        .main-nav-items {
            margin-right: auto; /* Pushes user items to the right */
        }
        .user-nav-items {
            display: flex;
            align-items: center;
        }
        @media (max-width: 992px) {
            .user-nav-items {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(0,0,0,0.1);
            }
        }
    </style>
</head>
<body id="page-top">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="../index.php">
                <img src="../assets/assets/img/logo.png" alt="HerbAura Logo" width="40" class="d-inline-block align-text-top">
                HERBAURA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <!-- Main navigation items -->
                <ul class="navbar-nav main-nav-items">
                    <li class="nav-item"><a class="nav-link" href="../index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="../public/shop.php"><i class="bi bi-shop"></i> Shop</a></li>
                </ul>
                
                <!-- User navigation items (right-aligned) -->
                <ul class="navbar-nav user-nav-items">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi-cart"></i> Cart
                                <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                                    <span class="badge bg-primary rounded-pill cart-badge"><?= count($_SESSION['cart']) ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="orders.php"><i class="bi bi-basket2"></i> Orders</a></li>
                        <li class="nav-item"><a class="nav-link" href="../public/profile.php"><i class="bi-person"></i> Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="../public/logout.php"><i class="bi-box-arrow-right"></i> Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="../public/login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="../public/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container-fluid px-0">