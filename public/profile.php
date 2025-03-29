<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user details
$user_id = $_SESSION['user_id'];
$user = [];
$addresses = [];

// Fetch user details
$stmt = $link->prepare("SELECT * FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
}

// Fetch user addresses
$stmt = $link->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, address_type ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $addresses = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone_number = trim($_POST['phone_number']);
    
    $stmt = $link->prepare("UPDATE Users SET first_name = ?, last_name = ?, phone_number = ? WHERE user_id = ?");
    $stmt->bind_param("sssi", $first_name, $last_name, $phone_number, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully";
        // Refresh user data
        $stmt = $link->prepare("SELECT * FROM Users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
        }
    } else {
        $_SESSION['error'] = "Error updating profile: " . $link->error;
    }
    header("Location: profile.php");
    exit;
}

// Handle address update/add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_address'])) {
    $address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : null;
    $street = trim($_POST['street']);
    $apartment = trim($_POST['apartment']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postal_code = trim($_POST['postal_code']);
    $country = trim($_POST['country']);
    $address_type = trim($_POST['address_type']);
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // If setting this as default, first unset any existing default
    if ($is_default) {
        $stmt = $link->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    if ($address_id) {
        // Update existing address
        $stmt = $link->prepare("UPDATE addresses SET 
            street = ?, apartment = ?, city = ?, state = ?, postal_code = ?, 
            country = ?, address_type = ?, is_default = ? 
            WHERE address_id = ? AND user_id = ?");
        $stmt->bind_param("sssssssiii", $street, $apartment, $city, $state, $postal_code, 
            $country, $address_type, $is_default, $address_id, $user_id);
    } else {
        // Add new address
        $stmt = $link->prepare("INSERT INTO addresses 
            (user_id, street, apartment, city, state, postal_code, country, address_type, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssi", $user_id, $street, $apartment, $city, $state, 
            $postal_code, $country, $address_type, $is_default);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Address " . ($address_id ? "updated" : "added") . " successfully";
        // Refresh addresses
        $stmt = $link->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, address_type ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $_SESSION['error'] = "Error " . ($address_id ? "updating" : "adding") . " address: " . $link->error;
    }
    header("Location: profile.php");
    exit;
}

// Handle address deletion
if (isset($_GET['delete_address'])) {
    $address_id = intval($_GET['delete_address']);
    $stmt = $link->prepare("DELETE FROM addresses WHERE address_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Address deleted successfully";
        // Refresh addresses
        $stmt = $link->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, address_type ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $_SESSION['error'] = "Error deleting address: " . $link->error;
    }
    header("Location: profile.php");
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $stmt = $link->prepare("SELECT password_hash FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($current_password, $user['password_hash'])) {
            if ($new_password === $confirm_password) {
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $link->prepare("UPDATE Users SET password_hash = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_password_hash, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Password changed successfully";
                } else {
                    $_SESSION['error'] = "Error changing password: " . $link->error;
                }
            } else {
                $_SESSION['error'] = "New passwords do not match";
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect";
        }
    } else {
        $_SESSION['error'] = "User not found";
    }
    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HerbAura</title>
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
        
        .profile-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-header {
            color: var(--primary-green);
            border-bottom: 2px solid var(--primary-green);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        .btn-primary:hover {
            background-color: #1e3d19;
            border-color: #1e3d19;
        }
        
        .btn-outline-primary {
            color: var(--primary-green);
            border-color: var(--primary-green);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-green);
            color: white;
        }
        
        .address-card {
            border-left: 3px solid var(--primary-green);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .address-card:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }
        
        .default-badge {
            background-color: var(--primary-green);
        }
        
        .address-type-badge {
            background-color: var(--accent-orange);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-green);
        }
        
        .nav-pills .nav-link {
            color: var(--primary-green);
        }
    </style>
</head>
<body>
    <?php 
    // Check user role and include appropriate header
    if (isLoggedIn() && isAdmin()) {
        include '../includes/admin_header.php';
    } else {
        include '../includes/header.php';
    }
    ?>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3">
                <div class="profile-card">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-person" style="font-size: 3rem; color: var(--primary-green);"></i>
                        </div>
                        <h4 class="mt-3"><?= htmlspecialchars($user['first_name'] . ' ' . htmlspecialchars($user['last_name'])) ?></h4>
                        <span class="badge rounded-pill bg-secondary"><?= ucfirst($user['role']) ?></span>
                    </div>
                    
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#profile" data-bs-toggle="tab">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#addresses" data-bs-toggle="tab">Addresses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#password" data-bs-toggle="tab">Password</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="profile-card">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    
                    <div class="tab-content">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="profile">
                            <h3 class="profile-header">Personal Information</h3>
                            <form method="POST" action="profile.php">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" 
                                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" 
                                               value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                           value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                                </div>
                                
                                <button type="submit" class="btn btn-primary" name="update_profile">Update Profile</button>
                            </form>
                        </div>
                        
                        <!-- Addresses Tab -->
                        <div class="tab-pane fade" id="addresses">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="profile-header">My Addresses</h3>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                    <i class="bi bi-plus"></i> Add Address
                                </button>
                            </div>
                            
                            <?php if (empty($addresses)): ?>
                                <div class="alert alert-info">You haven't added any addresses yet.</div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($addresses as $address): ?>
                                    <div class="col-md-6">
                                        <div class="card address-card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge address-type-badge"><?= ucfirst($address['address_type']) ?></span>
                                                    <?php if ($address['is_default']): ?>
                                                        <span class="badge default-badge">Default</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <p class="card-text mb-1">
                                                    <?= htmlspecialchars($address['street']) ?>
                                                    <?= !empty($address['apartment']) ? ', ' . htmlspecialchars($address['apartment']) : '' ?>
                                                </p>
                                                <p class="card-text mb-1">
                                                    <?= htmlspecialchars($address['city']) ?>, 
                                                    <?= htmlspecialchars($address['state']) ?> 
                                                    <?= htmlspecialchars($address['postal_code']) ?>
                                                </p>
                                                <p class="card-text"><?= htmlspecialchars($address['country']) ?></p>
                                                
                                                <div class="d-flex gap-2 mt-3">
                                                    <button class="btn btn-sm btn-outline-primary edit-address" 
                                                            data-bs-toggle="modal" data-bs-target="#editAddressModal"
                                                            data-addressid="<?= $address['address_id'] ?>"
                                                            data-street="<?= htmlspecialchars($address['street']) ?>"
                                                            data-apartment="<?= htmlspecialchars($address['apartment']) ?>"
                                                            data-city="<?= htmlspecialchars($address['city']) ?>"
                                                            data-state="<?= htmlspecialchars($address['state']) ?>"
                                                            data-postalcode="<?= htmlspecialchars($address['postal_code']) ?>"
                                                            data-country="<?= htmlspecialchars($address['country']) ?>"
                                                            data-addresstype="<?= htmlspecialchars($address['address_type']) ?>"
                                                            data-isdefault="<?= $address['is_default'] ?>">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <a href="profile.php?delete_address=<?= $address['address_id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Are you sure you want to delete this address?')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Password Tab -->
                        <div class="tab-pane fade" id="password">
                            <h3 class="profile-header">Change Password</h3>
                            <form method="POST" action="profile.php">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" name="change_password">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="profile.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="street" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="street" name="street" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="apartment" class="form-label">Apartment, Suite, etc. (Optional)</label>
                            <input type="text" class="form-control" id="apartment" name="apartment">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="United States" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address_type" class="form-label">Address Type</label>
                            <select class="form-select" id="address_type" name="address_type" required>
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="billing">Billing</option>
                                <option value="shipping">Shipping</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                            <label class="form-check-label" for="is_default">Set as default address</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="update_address">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="profile.php">
                    <input type="hidden" name="address_id" id="edit_address_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_street" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="edit_street" name="street" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_apartment" class="form-label">Apartment, Suite, etc. (Optional)</label>
                            <input type="text" class="form-control" id="edit_apartment" name="apartment">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="edit_city" name="city" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="edit_state" name="state">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="edit_postal_code" name="postal_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="edit_country" name="country" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_address_type" class="form-label">Address Type</label>
                            <select class="form-select" id="edit_address_type" name="address_type" required>
                                <option value="home">Home</option>
                                <option value="work">Work</option>
                                <option value="billing">Billing</option>
                                <option value="shipping">Shipping</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="edit_is_default" name="is_default">
                            <label class="form-check-label" for="edit_is_default">Set as default address</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="update_address">Update Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit address modal data population
        document.querySelectorAll('.edit-address').forEach(button => {
            button.addEventListener('click', function() {
                const addressId = this.getAttribute('data-addressid');
                const street = this.getAttribute('data-street');
                const apartment = this.getAttribute('data-apartment');
                const city = this.getAttribute('data-city');
                const state = this.getAttribute('data-state');
                const postalCode = this.getAttribute('data-postalcode');
                const country = this.getAttribute('data-country');
                const addressType = this.getAttribute('data-addresstype');
                const isDefault = this.getAttribute('data-isdefault') === '1';
                
                document.getElementById('edit_address_id').value = addressId;
                document.getElementById('edit_street').value = street;
                document.getElementById('edit_apartment').value = apartment;
                document.getElementById('edit_city').value = city;
                document.getElementById('edit_state').value = state;
                document.getElementById('edit_postal_code').value = postalCode;
                document.getElementById('edit_country').value = country;
                document.getElementById('edit_address_type').value = addressType;
                document.getElementById('edit_is_default').checked = isDefault;
            });
        });
    </script>
</body>
</html>