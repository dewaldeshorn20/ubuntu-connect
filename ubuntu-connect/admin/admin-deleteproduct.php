<?php
session_start();
require_once '../includes/database.php';

// Redirects the user to the login page if they are not an admin
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

//This code avoids access to the site via manipilation of the url by only accepting post requests 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_URL . "/admin/admin-manageproducts.php");
    exit;
}

//This code checks that the ID exists before deleting
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid listing ID.");
}

    //Deleting the listing via the ID column
    $listingID = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM tbllistings WHERE listingID = ?");
    $stmt->execute([$listingID]);

header("Location: " . base_URL . "/admin/admin-manageproducts.php");

exit;
?>