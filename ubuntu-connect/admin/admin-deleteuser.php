<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/database.php';

// Checks the roles of the user to make sure they are an admin
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . base_URL . "admin/admin-manageusers.php");
    exit;
}

$id = intval($_GET['id']);

// Avoid accidentally deleting the admin account that is logged in
if ($id === $_SESSION['userID']) {
    $_SESSION['flashError'] = "You can't delete the account you're currently logged in as.";
    header("Location: " . base_URL . "admin/admin-manageusers.php");
    exit;
}

// Deleting a user fails with a foreign key constraint error if they have
// listings, messages, or other related rows pointing at them - so this
// cleans up dependent rows first and runs the whole thing as one
// transaction so nothing is left half-deleted if a step fails.
try {
    $pdo->beginTransaction();

    // Remove listings this user created (and anything that references those listings)
    $stmt = $pdo->prepare("SELECT listingID FROM tbllistings WHERE sellerID = ?");
    $stmt->execute([$id]);
    $listingIDs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($listingIDs) {
        $placeholders = implode(',', array_fill(0, count($listingIDs), '?'));
        $pdo->prepare("DELETE FROM tblmessages WHERE listingID IN ($placeholders)")->execute($listingIDs);
        $pdo->prepare("DELETE FROM tbllistings WHERE listingID IN ($placeholders)")->execute($listingIDs);
    }

    // Remove messages this user sent or received on other people's listings
    $pdo->prepare("DELETE FROM tblmessages WHERE senderID = ? OR receiverID = ?")->execute([$id, $id]);

    $pdo->prepare("DELETE FROM tblusers WHERE userID = ?")->execute([$id]);

    $pdo->commit();
    $_SESSION['flashSuccess'] = "User account deleted.";
} catch (PDOException $e) {
    $pdo->rollBack();
    // Most likely cause left: the user has a completed transaction/purchase
    // history, which we deliberately keep for record-keeping rather than delete.
    $_SESSION['flashError'] = "Couldn't delete this user - they still have transaction history on record. Remove or reassign that first.";
}

header("Location: " . base_URL . "admin/admin-manageusers.php");
exit;
