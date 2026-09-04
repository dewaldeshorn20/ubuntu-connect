<?php
session_start();
require_once '../includes/database.php';

// Redirect if user is not an admin
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_URL . "/admin/admin-manageproducts.php");
    exit;
}

// Check that the listing ID exists
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid listing ID.");
}

// Get the listing ID BEFORE using it
$listingID = (int)$_POST['id'];

// Delete messages associated with this listing first
$stmt = $pdo->prepare("DELETE FROM tblmessages WHERE listingID = ?");
$stmt->execute([$listingID]);

// Now delete the listing
$stmt = $pdo->prepare("DELETE FROM tbllistings WHERE listingID = ?");
$stmt->execute([$listingID]);

// Return to product management
header("Location: " . base_URL . "/admin/admin-manageproducts.php");
exit;
?>