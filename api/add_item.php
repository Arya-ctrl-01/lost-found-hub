<?php
/**
 * Add Item (Lost or Found) — api/add_item.php
 * Unified handler for both report_lost.php and report_found.php
 */
session_start();
require_once '../config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

$type        = $_POST['type'] === 'found' ? 'found' : 'lost';
$title       = trim($_POST['title'] ?? '');
$category    = trim($_POST['category'] ?? 'Other');
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location_name'] ?? '');
$date        = $_POST['date'] ?? date('Y-m-d');
$latitude    = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude   = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$userId      = $_SESSION['user_id'];
$redirectPage = $type === 'lost' ? '../report_lost.php' : '../report_found.php';

// Validate required
if (empty($title) || empty($description) || empty($location)) {
    redirectWith($redirectPage, 'error', 'Please fill in all required fields');
}

// ══ Handle Image Upload ══
$image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileSize = $_FILES['image']['size'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if ($fileSize > 5 * 1024 * 1024) {
        redirectWith($redirectPage, 'error', 'Image must be less than 5MB');
    }
    if (!in_array($ext, $allowed)) {
        redirectWith($redirectPage, 'error', 'Only JPG, PNG, GIF, WEBP images allowed');
    }

    $image = uniqid('img_') . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
        redirectWith($redirectPage, 'error', 'Failed to upload image');
    }
}

// ══ Insert ══
$stmt = $conn->prepare(
    "INSERT INTO items (title, description, category, location_name, latitude, longitude, date, image, type, status, user_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
);
$stmt->bind_param('ssssddsssi', $title, $description, $category, $location, $latitude, $longitude, $date, $image, $type, $userId);

if ($stmt->execute()) {
    // Notify user
    $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $msg = "Your " . $type . " item '" . $title . "' has been submitted and is pending admin review.";
    $notifStmt->bind_param('is', $userId, $msg);
    $notifStmt->execute();

    redirectWith($redirectPage, 'success', 'Item reported successfully! It will be reviewed by admin.');
} else {
    redirectWith($redirectPage, 'error', 'Failed to report item. Please try again.');
}
