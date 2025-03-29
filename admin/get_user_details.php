<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    die(json_encode(['error' => 'Unauthorized access']));
}

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    
    // Get user details
    $user_stmt = $link->prepare("SELECT * FROM Users WHERE user_id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows === 1) {
        $user = $user_result->fetch_assoc();
        
        // Get user addresses
        $address_stmt = $link->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, address_type ASC");
        $address_stmt->bind_param("i", $user_id);
        $address_stmt->execute();
        $address_result = $address_stmt->get_result();
        $addresses = $address_result->fetch_all(MYSQLI_ASSOC);
        ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <span class="user-detail-label">User ID:</span>
                    <span><?= htmlspecialchars($user['user_id']) ?></span>
                </div>
                <div class="mb-3">
                    <span class="user-detail-label">Username:</span>
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
                <div class="mb-3">
                    <span class="user-detail-label">Email:</span>
                    <span><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div class="mb-3">
                    <span class="user-detail-label">Role:</span>
                    <span class="badge rounded-pill <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <span class="user-detail-label">Full Name:</span>
                    <span><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                </div>
                <div class="mb-3">
                    <span class="user-detail-label">Phone:</span>
                    <span><?= htmlspecialchars($user['phone_number'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Address Section -->
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Address Information</h5>
                
                <?php if (count($addresses) > 0): ?>
                    <div class="row">
                        <?php foreach ($addresses as $address): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card address-card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= ucfirst($address['address_type']) ?> Address</strong>
                                            <?= $address['is_default'] ? '<span class="badge bg-primary ms-2">Default</span>' : '' ?>
                                        </div>
                                        <span class="badge address-type-badge"><?= $address['address_type'] ?></span>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">
                                            <strong>Street:</strong> <?= htmlspecialchars($address['street']) ?>
                                            <?= !empty($address['apartment']) ? '<br><strong>Apartment:</strong> ' . htmlspecialchars($address['apartment']) : '' ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>City:</strong> <?= htmlspecialchars($address['city']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>State:</strong> <?= htmlspecialchars($address['state']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Postal Code:</strong> <?= htmlspecialchars($address['postal_code']) ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Country:</strong> <?= htmlspecialchars($address['country']) ?>
                                        </p>
                                    </div>
                                    <div class="card-footer small text-muted">
                                        Last updated: <?= date('M d, Y', strtotime($address['updated_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No addresses found for this user</div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="mb-3">
                    <span class="user-detail-label">Account Created:</span>
                    <span><?= date('M d, Y h:i A', strtotime($user['created_at'])) ?></span>
                </div>
                <div class="mb-3">
                    <span class="user-detail-label">Last Updated:</span>
                    <span><?= date('M d, Y h:i A', strtotime($user['updated_at'])) ?></span>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">User not found</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request</div>';
}
?>