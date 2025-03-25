<?php
/**
 * Register a new customer with address
 */
function registerCustomer($username, $email, $password, $first_name, $last_name, $phone, 
                         $street, $city, $state, $country, $postal_code) {  // Changed parameter name
    $link = dbConnect();
    $link->begin_transaction();

    try {
        // Check if username or email exists
        $stmt = $link->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Username or email already exists");
        }

        // Hash password
        $hashed_pw = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $link->prepare("INSERT INTO Users (username, email, password_hash, first_name, last_name, phone_number, role) 
                              VALUES (?, ?, ?, ?, ?, ?, 'user')");
        $stmt->bind_param("ssssss", $username, $email, $hashed_pw, $first_name, $last_name, $phone);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create user account");
        }

        $user_id = $link->insert_id;

        // Insert address if provided
        if ($street && $city) {
            $stmt = $link->prepare("INSERT INTO Addresses (user_id, street, city, state, country, postal_code, is_default)
                                  VALUES (?, ?, ?, ?, ?, ?, 1)");  // Changed to postal_code
            $stmt->bind_param("isssss", $user_id, $street, $city, $state, $country, $postal_code);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to save address: " . $link->error);
            }
        }

        $link->commit();
        
        // Auto-login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = 'user';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        
        return true;
    } catch (Exception $e) {
        $link->rollback();
        return $e->getMessage();
    }
}

/**
 * Get customer details
 */
function getCustomerDetails($user_id) {
    $link = dbConnect();
    $stmt = $link->prepare("SELECT u.*, a.street, a.city, a.state, a.country, a.postal_code as zip_code
                          FROM Users u
                          LEFT JOIN Addresses a ON u.user_id = a.user_id AND a.is_default = 1
                          WHERE u.user_id = ?");  // Changed to postal_code and aliased as zip_code
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Update customer profile
 */
function updateCustomerProfile($user_id, $first_name, $last_name, $phone, 
                             $street = null, $city = null, $state = null, $country = null, $postal_code = null) {
    $link = dbConnect();
    $link->begin_transaction();

    try {
        // Update user
        $stmt = $link->prepare("UPDATE Users SET first_name = ?, last_name = ?, phone_number = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $first_name, $last_name, $phone, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update profile");
        }

        // Update or insert address
        if ($street && $city) {
            // Check if default address exists
            $stmt = $link->prepare("SELECT address_id FROM Addresses WHERE user_id = ? AND is_default = 1");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing address
                $stmt = $link->prepare("UPDATE Addresses SET street = ?, city = ?, state = ?, country = ?, postal_code = ?
                                      WHERE user_id = ? AND is_default = 1");  // Changed to postal_code
                $stmt->bind_param("sssssi", $street, $city, $state, $country, $postal_code, $user_id);
            } else {
                // Insert new address
                $stmt = $link->prepare("INSERT INTO Addresses (user_id, street, city, state, country, postal_code, is_default)
                                      VALUES (?, ?, ?, ?, ?, ?, 1)");  // Changed to postal_code
                $stmt->bind_param("isssss", $user_id, $street, $city, $state, $country, $postal_code);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update address: " . $link->error);
            }
        }

        $link->commit();
        return true;
    } catch (Exception $e) {
        $link->rollback();
        return $e->getMessage();
    }
}


/**
 * Authenticate a user
 */
function loginUser($username, $password) {
    $link = dbConnect();
    
    // Prepare SQL to prevent SQL injection
    $stmt = $link->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
    }
    
    return "Invalid username or password.";
}

/**
 * Check if logged in user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

?>