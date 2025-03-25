<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    // Address fields
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $zip_code = trim($_POST['zip_code']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error_message = 'Required fields are missing.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters.';
    } else {
        // Generate username if not provided
        if (empty($username)) {
            $username = generateUsername($first_name, $last_name);
        }
        
        $register_result = registerCustomer(
            $username,
            $email, 
            $password, 
            $first_name, 
            $last_name, 
            $phone,
            $street,
            $city,
            $state,
            $country,
            $zip_code
        );
        
        if ($register_result === true) {
            header("Location: login.php");
            exit();
        } else {
            $error_message = $register_result;
        }
    }
}

// Helper function to generate username
function generateUsername($first_name, $last_name) {
    $base = strtolower(substr($first_name, 0, 1) . preg_replace('/[^a-z]/', '', strtolower($last_name)));
    $random = rand(100, 999);
    return $base . $random;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HerbAura</title>
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
        body {
            padding-top: 80px;
            background-color: #f8f9fa;
            font-family: 'Merriweather Sans', sans-serif;
        }
        .registration-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            background-color: white;
        }
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .section-title {
            color: #2d5a27;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-family: 'Merriweather', serif;
        }
        .btn-primary {
            background-color: #2d5a27;
            border-color: #2d5a27;
            padding: 0.5rem 2rem;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #1e3d19;
            border-color: #1e3d19;
        }
        a {
            color: #2d5a27;
            text-decoration: none;
        }
        a:hover {
            color: #f4623a;
            text-decoration: underline;
        }
        .form-check-input:checked {
            background-color: #2d5a27;
            border-color: #2d5a27;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="registration-container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="color: #2d5a27;">Create Your HerbAura Account</h2>
                <p class="text-muted">Join us for authentic ayurvedic wellness solutions</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="post" action="register.php">
                <div class="form-section">
                    <h4 class="section-title">Personal Information</h4>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username*</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <small class="text-muted">Choose a unique username</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name*</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name*</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address*</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password*</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title">Shipping Address</h4>
                    <div class="mb-3">
                        <label for="street" class="form-label">Street Address</label>
                        <input type="text" class="form-control" id="street" name="street">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" name="country">
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="India">India</option>
                                <option value="United States">United States</option>
                                <option value="United Kingdom">United Kingdom</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="zip_code" class="form-label">Postal/Zip Code</label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code">
                        </div>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to HerbAura's <a href="#" style="text-decoration: underline;">Terms of Service</a> and <a href="#" style="text-decoration: underline;">Privacy Policy</a>
                    </label>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg py-2">Create Account</button>
                </div>
                
                <div class="text-center">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>