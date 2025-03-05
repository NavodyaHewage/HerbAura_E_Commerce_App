<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
        if (isAdmin()) {
            header("Location: ../admin/index.php");
        } elseif (isCompany()) {
            header("Location: ../company/index.php");
        } elseif (isCandidate()) {
            header("Location: ../candidate/index.php");
        }
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="../assets/index/css/styles.css" rel="stylesheet">
</head>
<body>
    <section class="page-section" id="login">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-6">
                <div class="col-lg-8 col-xl-12 text-center">
                    <h2 class="mt-0">Log with FindJob</h2>
                    <hr class="divider" />
                    <p class="text-muted mb-5">Fill in the form below to login your account.</p>
                        
                    </div>
                    <form action="login.php" method="post">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                            <label for="username">Username</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-xl">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and dependencies (Optional, if needed for specific components) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
