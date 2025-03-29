
<?php
// Include database connection
require_once '../db.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price = (float) $_POST['price'];
    $category_id = (int) $_POST['category'];

    // Handle file upload
    $image_name = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];
    $image_error = $_FILES['image']['error'];

    // Define allowed image types
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $image_type = mime_content_type($image_tmp_name);

    // Validate image
    if (!in_array($image_type, $allowed_types)) {
        die("Invalid image type. Only JPG, PNG, and GIF are allowed.");
    }

    // Define upload directory
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate a unique filename
    $image_new_name = uniqid('', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
    $image_destination = $upload_dir . $image_new_name;

    // Move the uploaded file
    if (!move_uploaded_file($image_tmp_name, $image_destination)) {
        die("Failed to upload image.");
    }

    // Insert product into the database
    $query = "INSERT INTO products (name, description, price, category_id, image) VALUES ('$name', '$description', $price, $category_id, '$image_new_name')";
    if (mysqli_query($conn, $query)) {
        echo "Product added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
