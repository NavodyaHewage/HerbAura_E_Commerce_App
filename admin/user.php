<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../public/login.php');
    exit;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $link->prepare("DELETE FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting user";
    }
    header("Location: users.php");
    exit;
}

// Fetch all users
$users = [];
$query = "SELECT user_id, username, email, role, first_name, last_name, created_at FROM Users ORDER BY created_at DESC";
$result = $link->query($query);
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - HerbAura</title>
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
        
        .badge-admin {
            background-color: var(--accent-orange);
        }
        
        .badge-user {
            background-color: #6c757d;
        }
        
        .btn-view {
            background-color: var(--primary-green);
            color: white;
        }
        
        .btn-view:hover {
            background-color: #1e3d19;
            color: white;
        }
        
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #bb2d3b;
            color: white;
        }
        
        /* Modal styling */
        .modal-header {
            background-color: var(--primary-green);
            color: white;
        }
        
        .user-detail-label {
            font-weight: 600;
            color: var(--primary-green);
        }
        
        /* Address card styling */
        .address-card {
            border-left: 4px solid var(--primary-green);
            transition: all 0.3s ease;
        }
        
        .address-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }
        
        .address-card .card-header {
            font-weight: 600;
            color: var(--primary-green);
            background-color: rgba(45, 90, 39, 0.1);
        }
        
        .address-type-badge {
            background-color: var(--accent-orange);
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../includes/admin_header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">User Management</h1>
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
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['user_id']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td>
                                    <span class="badge rounded-pill <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-view me-2" data-bs-toggle="modal" data-bs-target="#userModal" 
                                        data-userid="<?= $user['user_id'] ?>">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <a href="users.php?delete_id=<?= $user['user_id'] ?>" class="btn btn-sm btn-delete" 
                                        onclick="return confirm('Are you sure you want to delete this user?')">
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

    <!-- User Details Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">User Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="userDetails">
                    <!-- Details will be loaded here via AJAX -->
                    <div class="text-center my-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading user details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    // Load user details when modal is shown
    $('#userModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var userId = button.data('userid');
        var modal = $(this);
        
        // Show loading spinner
        modal.find('#userDetails').html(`
            <div class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading user details...</p>
            </div>
        `);
        
        // AJAX request to get user details
        $.ajax({
            url: 'get_user_details.php',
            type: 'GET',
            data: { user_id: userId },
            success: function(response) {
                modal.find('#userDetails').html(response);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                let errorMsg = "Error loading user details";
                
                // Try to get more specific error message
                if (xhr.responseText) {
                    try {
                        const jsonResponse = JSON.parse(xhr.responseText);
                        if (jsonResponse.error) {
                            errorMsg = jsonResponse.error;
                        }
                    } catch (e) {
                        errorMsg = xhr.responseText;
                    }
                }
                
                modal.find('#userDetails').html(`
                    <div class="alert alert-danger">
                        <strong>Error:</strong> ${errorMsg}
                        ${xhr.status ? `<br><small>Status code: ${xhr.status}</small>` : ''}
                    </div>
                `);
            }
        });
    });
    </script>
</body>
</html>