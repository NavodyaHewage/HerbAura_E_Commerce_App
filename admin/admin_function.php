<?php
session_start();

// Include the database connection file
require_once '../includes/db.php';

// Function to add a new product
function addProduct($conn, $name, $description, $price, $stock_quantity, $category_id, $image_url) {
    $sql = "INSERT INTO Products (name, description, price, stock_quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiis", $name, $description, $price, $stock_quantity, $category_id, $image_url);
    return $stmt->execute();
}

// Function to update an existing product
function updateProduct($conn, $product_id, $name, $description, $price, $stock_quantity, $category_id, $image_url) {
    $sql = "UPDATE Products SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image_url = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiisi", $name, $description, $price, $stock_quantity, $category_id, $image_url, $product_id);
    return $stmt->execute();
}

// Function to delete a product
function deleteProduct($conn, $product_id) {
    $sql = "DELETE FROM Products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    return $stmt->execute();
}

// Function to retrieve all products
function getProducts($conn) {
    $sql = "SELECT * FROM Products";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to add a new category
function addCategory($conn, $name, $description, $parent_category_id = NULL) {
    $sql = "INSERT INTO Categories (name, description, parent_category_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $description, $parent_category_id);
    return $stmt->execute();
}

// Function to update an existing category
function updateCategory($conn, $category_id, $name, $description, $parent_category_id = NULL) {
    $sql = "UPDATE Categories SET name = ?, description = ?, parent_category_id = ? WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $name, $description, $parent_category_id, $category_id);
    return $stmt->execute();
}

// Function to delete a category
function deleteCategory($conn, $category_id) {
    $sql = "DELETE FROM Categories WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    return $stmt->execute();
}

// Function to retrieve all categories
function getCategories($conn) {
    $sql = "SELECT * FROM Categories";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to retrieve all users
function getUsers($conn) {
    $sql = "SELECT user_id, username, email, role, first_name, last_name FROM Users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Close the database connection after all operations
$conn->close();
?>
