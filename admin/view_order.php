<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    $status_description = trim($_POST['status_description']);
    
    $stmt = $link->prepare("UPDATE Orders SET status = ?, status_description = ? WHERE order_id = ?");
    $stmt->bind_param("ssi", $status, $status_description, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Order status updated successfully";
    } else {
        $_SESSION['error'] = "Error updating order status: " . $link->error;
    }
    header("Location: orders.php");
    exit;
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$tab = isset($_GET['tab']) && $_GET['tab'] === 'completed' ? 'completed' : 'pending';

// Build query for orders
$query = "SELECT o.*, u.username, u.email 
          FROM Orders o
          JOIN Users u ON o.user_id = u.user_id
          WHERE 1=1";

$params = [];
$types = '';

// Add filters based on tab
if ($tab === 'completed') {
    $query .= " AND o.payment_status = 'completed'";
} else {
    $query .= " AND o.payment_status != 'completed'";
}

// Add status filter
if (!empty($status_filter)) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Add search filter
if (!empty($search_term)) {
    $query .= " AND (o.order_id = ? OR u.username LIKE ? OR u.email LIKE ?)";
    $params[] = $search_term;
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= 'iss';
}

$query .= " ORDER BY o.created_at DESC";

// Prepare and execute query
$stmt = $link->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Function to get order items
function getOrderItems($order_id) {
    global $link;
    $stmt = $link->prepare("SELECT oi.*, p.name, p.image_url 
                           FROM Order_Items oi
                           JOIN Products p ON oi.product_id = p.product_id
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - HerbAura</title>
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
        
        /* Status badges */
        .badge-pending {
            background-color: #6c757d;
        }
        
        .badge-processing {
            background-color: #17a2b8;
        }
        
        .badge-shipped {
            background-color: #007bff;
        }
        
        .badge-delivered {
            background-color: #28a745;
        }
        
        .badge-cancelled {
            background-color: #dc3545;
        }
        
        .badge-payment-completed {
            background-color: #28a745;
        }
        
        .badge-payment-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-payment-failed {
            background-color: #dc3545;
        }
        
        /* Order items styling */
        .order-item {
            border-left: 3px solid var(--primary-green);
            padding-left: 10px;
            margin-bottom: 10px;
        }
        
        .order-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        /* Nav tabs styling */
        .nav-tabs .nav-link {
            color: #495057;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-green);
            font-weight: bold;
            border-bottom: 2px solid var(--primary-green);
        }
        
        /* Order card styling */
        .order-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-green);
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        /* Modal styling */
        .modal-header {
            background-color: var(--primary-green);
            color: white;
        }
        
        .order-details-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Order Management</h1>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="ordersTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" 
                           href="orders.php?tab=pending" 
                           aria-controls="pending" 
                           aria-selected="<?= $tab === 'pending' ? 'true' : 'false' ?>">
                            Pending Payments
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $tab === 'completed' ? 'active' : '' ?>" 
                           href="orders.php?tab=completed" 
                           aria-controls="completed" 
                           aria-selected="<?= $tab === 'completed' ? 'true' : 'false' ?>">
                            Completed Payments
                        </a>
                    </li>
                </ul>

                <!-- Search and Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <input type="hidden" name="tab" value="<?= $tab ?>">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Orders</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Order ID, Username or Email" value="<?= htmlspecialchars($search_term) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Filter by Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                                <a href="orders.php?tab=<?= $tab ?>" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="table-responsive">
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info">No orders found matching your criteria.</div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($orders as $order): ?>
                                <?php $order_items = getOrderItems($order['order_id']); ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card order-card h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0">Order #<?= $order['order_id'] ?></h5>
                                                <div>
                                                    <span class="badge bg-<?= $order['payment_status'] === 'completed' ? 'success' : ($order['payment_status'] === 'pending' ? 'warning' : 'danger') ?> me-1">
                                                        Payment: <?= ucfirst($order['payment_status']) ?>
                                                    </span>
                                                    <span class="badge bg-<?= strtolower($order['status']) ?>">
                                                        <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <p class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
                                                <p class="mb-1"><strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                                                <p class="mb-1"><strong>Total:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <button class="btn btn-sm btn-primary view-order-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#orderModal"
                                                            data-order-id="<?= $order['order_id'] ?>"
                                                            data-order-details='<?= json_encode($order) ?>'
                                                            data-order-items='<?= json_encode($order_items) ?>'>
                                                        <i class="bi bi-eye"></i> View Order
                                                    </button>
                                                </div>
                                                <div>
                                                    <button class="btn btn-sm btn-warn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#statusModal"
                                                            data-order-id="<?= $order['order_id'] ?>"
                                                            data-current-status="<?= $order['status'] ?>"
                                                            data-status-description="<?= htmlspecialchars($order['status_description'] ?? '') ?>">
                                                        <i class="bi bi-pencil"></i> Update Status
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderModalLabel">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="orderModalBody">
                    <!-- Content will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="modalOrderId">
                        <div class="mb-3">
                            <label for="modalStatus" class="form-label">Status</label>
                            <select class="form-select" id="modalStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalStatusDescription" class="form-label">Status Description</label>
                            <textarea class="form-control" id="modalStatusDescription" name="status_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Order Details Modal
        document.querySelectorAll('.view-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderDetails = JSON.parse(this.getAttribute('data-order-details'));
                const orderItems = JSON.parse(this.getAttribute('data-order-items'));
                
                // Format order date
                const orderDate = new Date(orderDetails.created_at);
                const formattedDate = orderDate.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Build modal content
                let modalContent = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Order Summary</h6>
                            <table class="table table-sm order-details-table">
                                <tr>
                                    <th>Order ID:</th>
                                    <td>${orderDetails.order_id}</td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td>${formattedDate}</td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>${orderDetails.username} (${orderDetails.email})</td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td>$${parseFloat(orderDetails.total_amount).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td><span class="badge bg-${orderDetails.payment_status === 'completed' ? 'success' : (orderDetails.payment_status === 'pending' ? 'warning' : 'danger')}">
                                        ${orderDetails.payment_status.charAt(0).toUpperCase() + orderDetails.payment_status.slice(1)}
                                    </span></td>
                                </tr>
                                <tr>
                                    <th>Order Status:</th>
                                    <td><span class="badge bg-${orderDetails.status}">
                                        ${orderDetails.status.charAt(0).toUpperCase() + orderDetails.status.slice(1)}
                                    </span></td>
                                </tr>
                            </table>
                            
                            ${orderDetails.status_description ? `
                            <div class="alert alert-info mt-3">
                                <strong>Status Notes:</strong><br>
                                ${orderDetails.status_description.replace(/\n/g, '<br>')}
                            </div>
                            ` : ''}
                        </div>
                        <div class="col-md-6">
                            <h6>Shipping Information</h6>
                            <div class="bg-light p-3 mb-3 rounded">
                                ${orderDetails.shipping_address ? orderDetails.shipping_address.replace(/\n/g, '<br>') : 'Not specified'}
                            </div>
                            
                            <h6>Billing Information</h6>
                            <div class="bg-light p-3 rounded">
                                ${orderDetails.billing_address ? orderDetails.billing_address.replace(/\n/g, '<br>') : 'Not specified'}
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mt-4">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                // Add order items
                orderItems.forEach(item => {
                    modalContent += `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    ${item.image_url ? `<img src="${item.image_url}" alt="${item.name}" class="order-item-img me-2">` : ''}
                                    <div>${item.name}</div>
                                </div>
                            </td>
                            <td>$${parseFloat(item.price).toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>$${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                modalContent += `
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>$${parseFloat(orderDetails.total_amount).toFixed(2)}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                
                document.getElementById('orderModalBody').innerHTML = modalContent;
            });
        });
        
        // Status Update Modal
        document.querySelectorAll('[data-bs-target="#statusModal"]').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('modalOrderId').value = this.getAttribute('data-order-id');
                document.getElementById('modalStatus').value = this.getAttribute('data-current-status');
                document.getElementById('modalStatusDescription').value = this.getAttribute('data-status-description');
            });
        });
    </script>
</body>
</html>