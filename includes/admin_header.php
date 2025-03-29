
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
        .bi-cart, .bi-person {
            font-size: 1.2rem;
        }
        .profile-dropdown {
            display: inline-block;
            position: relative;
        }
        .profile-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
        }
        .profile-dropdown:hover .profile-dropdown-menu {
            display: block;
        }
        .profile-dropdown-item {
            color: #2d5a27;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .profile-dropdown-item:hover {
            background-color: #f8f9fa;
            color: #f4623a;
        }
    </style>
</head>
<body id="page-top">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="../admin/admin_dash.php">
                <img src="../assets/assets/img/logo.png" alt="HerbAura Logo" width="40" class="d-inline-block align-text-top">
                HERBAURA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav me-auto my-2 mx-5 my-lg-0">
                    <li class="nav-item"><a class="nav-link" href="../admin/user.php"><i class="bi bi-people"></i>&nbsp;User</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/product.php"><i class="bi bi-box-seam"></i>&nbsp;Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="../admin/category.php"><i class="bi bi-tags"></i>&nbsp;Category</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact"><i class="bi bi-cart-check"></i>&nbsp;Orders</a></li>
                </ul>

                <ul class="navbar-nav ms-auto my-2 my-lg-0">
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact"><i class="bi bi-person-square"></i>&nbsp;Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="../index.php#contact"><i class="bi bi-box-arrow-down"></i>&nbsp;Logout</a></li>
                </ul>
            </div>

        </div>
    </nav>
    <main class="container-fluid px-0">