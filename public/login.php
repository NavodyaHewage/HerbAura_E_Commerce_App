<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validation
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        $login_result = loginUser($username, $password);
        
        if ($login_result === true) {
            // Redirect based on user role
            if (isAdmin()) {
                header("Location: ../admin/admin_dash.php");
            } else {
                //redirect to the customer dashboard
                header("Location: shop.php");
                console.log("Login successful", $login_result);
            }
            exit();
        } else {
            $error_message = $login_result ?? "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HerbAura</title>
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
        .login-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            background-color: white;
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
        .divider {
            height: 3px;
            background-color: #2d5a27;
            width: 100px;
            margin: 1.5rem auto;
            opacity: 1;
        }
        .form-floating label {
            color: #6c757d;
        }
        .form-floating > .form-control:focus ~ label {
            color: #2d5a27;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div class="login-container">
            <div class="text-center mb-5">
                <h2 class="section-title">Welcome Back to HerbAura</h2>
                <p class="text-muted">Sign in to access your personalized wellness experience</p>
                <div class="divider"></div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    <label for="username">Username or Email</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <label for="password">Password</label>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php">Forgot password?</a>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg py-2">Sign In</button>
                </div>
                
                <div class="text-center">
                    <p>Don't have an account? <a href="register.php">Create one</a></p>
                </div>
            </form>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>