<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

// Get database connection
$link = dbConnect();

// Get dashboard statistics
$stats = [];
try {
    // Total Products
    $result = mysqli_query($link, "SELECT COUNT(*) FROM Products");
    $row = mysqli_fetch_row($result);
    $stats['total_products'] = $row[0];
    
    // Total Categories
    $result = mysqli_query($link, "SELECT COUNT(*) FROM Categories");
    $row = mysqli_fetch_row($result);
    $stats['total_categories'] = $row[0];
    
    // Total Orders
    $result = mysqli_query($link, "SELECT COUNT(*) FROM Orders");
    $row = mysqli_fetch_row($result);
    $stats['total_orders'] = $row[0];
    
    // Total Customers
    $result = mysqli_query($link, "SELECT COUNT(*) FROM Users WHERE role = 'user'");
    $row = mysqli_fetch_row($result);
    $stats['total_customers'] = $row[0];
    
    // Recent Orders
    $result = mysqli_query($link, "SELECT o.order_id, u.username, o.total_amount, o.status, o.created_at 
                         FROM Orders o JOIN Users u ON o.user_id = u.user_id 
                         ORDER BY o.created_at DESC LIMIT 5");
    $recent_orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Revenue data for chart (last 7 days)
    $result = mysqli_query($link, "SELECT DATE(created_at) as date, SUM(total_amount) as daily_revenue 
                         FROM Orders 
                         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                         GROUP BY DATE(created_at) 
                         ORDER BY DATE(created_at)");
    $revenue_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    // Prepare chart data
    $chart_labels = [];
    $chart_values = [];
    $date = new DateTime();
    for ($i = 6; $i >= 0; $i--) {
        $date->modify("-1 day");
        $date_str = $date->format('Y-m-d');
        $chart_labels[] = $date->format('M d');
        
        $found = false;
        foreach ($revenue_data as $row) {
            if ($row['date'] == $date_str) {
                $chart_values[] = $row['daily_revenue'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $chart_values[] = 0;
        }
    }
    $chart_labels = array_reverse($chart_labels);
    $chart_values = array_reverse($chart_values);
    
} catch (Exception $e) {
    // Handle database errors gracefully
    die("Database error: " . $e->getMessage());
}
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
        
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.25rem;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-processing {
            background-color: #17a2b8;
            color: #fff;
        }
        
        .badge-shipped {
            background-color: #007bff;
            color: #fff;
        }
        
        .badge-delivered {
            background-color: #28a745;
            color: #fff;
        }
        
        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include '../includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
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
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-primary h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-box-seam card-icon text-primary"></i>
                                <h5 class="card-title">Total Products</h5>
                                <h2 class="mb-3"><?= number_format($stats['total_products']) ?></h2>
                                <a href="products.php" class="btn btn-sm btn-herbaura">View Products</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-success h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-tags card-icon text-success"></i>
                                <h5 class="card-title">Categories</h5>
                                <h2 class="mb-3"><?= number_format($stats['total_categories']) ?></h2>
                                <a href="categories.php" class="btn btn-sm btn-herbaura">View Categories</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-warning h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-cart-check card-icon text-warning"></i>
                                <h5 class="card-title">Total Orders</h5>
                                <h2 class="mb-3"><?= number_format($stats['total_orders']) ?></h2>
                                <a href="orders.php" class="btn btn-sm btn-herbaura">View Orders</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-info h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-people card-icon text-info"></i>
                                <h5 class="card-title">Customers</h5>
                                <h2 class="mb-3"><?= number_format($stats['total_customers']) ?></h2>
                                <a href="customers.php" class="btn btn-sm btn-herbaura">View Customers</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Orders -->
                <div class="row">
                    <!-- Revenue Chart -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Revenue (Last 7 Days)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Today's Orders
                                        <span class="badge bg-primary rounded-pill">14</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Pending Orders
                                        <span class="badge bg-warning rounded-pill">8</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Low Stock Products
                                        <span class="badge bg-danger rounded-pill">5</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        New Customers (Week)
                                        <span class="badge bg-success rounded-pill">12</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card recent-table">
                    <div class="card-header">
                        <h5 class="card-title">Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['order_id'] ?></td>
                                        <td><?= htmlspecialchars($order['username']) ?></td>
                                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                        <td>Rs. <?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge badge-<?= strtolower($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order_details.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="orders.php" class="btn btn-herbaura">View All Orders</a>
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
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: <?= json_encode($chart_values) ?>,
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rs. ' + context.raw.toLocaleString();
                            }
                        }
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