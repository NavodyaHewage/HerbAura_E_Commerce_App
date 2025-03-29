<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
// $admin = getAdminDetailsByUserId($user_id);

// if ($admin === null) {
//     die('Admin not found.');
// }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HerbAura</title>
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
        
        .navbar-admin {
            background-color: var(--primary-green);
        }
        
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 60px);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: var(--secondary-beige);
            color: var(--primary-green);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .card-primary {
            border-left: 4px solid var(--primary-green);
        }
        
        .card-warning {
            border-left: 4px solid var(--accent-orange);
        }
        
        .card-success {
            border-left: 4px solid #28a745;
        }
        
        .card-info {
            border-left: 4px solid #17a2b8;
        }
        
        .recent-table {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        
        .btn-herbaura {
            background-color: var(--primary-green);
            color: white;
        }
        
        .btn-herbaura:hover {
            background-color: #1e3d19;
            color: white;
        }
    </style>
</head>
<body>
<?php include '../includes/admin_header.php'; ?>


    <div class="container-fluid">
        <div class="row"> 

            <!-- Main Content -->
            <main class="col-12 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Overview</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="bi bi-calendar"></i> This week
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card card-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-box-seam card-icon text-primary"></i>
                                <h5 class="card-title">Total Products</h5>
                                <a href="products.php" class="btn btn-sm btn-herbaura">View Products</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-success">
                            <div class="card-body text-center">
                                <i class="bi bi-tags card-icon text-success"></i>
                                <h5 class="card-title">Categories</h5>
                                <a href="categories.php" class="btn btn-sm btn-herbaura">View Categories</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-cart-check card-icon text-warning"></i>
                                <h5 class="card-title">Total Orders</h5>
                                <a href="orders.php" class="btn btn-sm btn-herbaura">View Orders</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-info">
                            <div class="card-body text-center">
                                <i class="bi bi-people card-icon text-info"></i>
                                <h5 class="card-title">Customers</h5>
                                <a href="customers.php" class="btn btn-sm btn-herbaura">View Customers</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: [120000, 190000, 150000, 200000, 180000, 220000, 250000],
                    backgroundColor: 'rgba(45, 90, 39, 0.2)',
                    borderColor: 'rgba(45, 90, 39, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>