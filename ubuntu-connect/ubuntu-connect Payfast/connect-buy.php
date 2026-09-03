<?php
include 'includes/header.php';
require_once 'includes/payment-tables.php';

if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['listingID'])) {
    header("Location: connect-viewproducts.php");
    exit;
}

$listingID = intval($_POST['listingID']);
$buyerID = $_SESSION['userID'];

// Lock the row while we check it, so two people can't both "win" the same
// item if they click Buy Now at the same moment.
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM tbllistings WHERE listingID = ? FOR UPDATE");
    $stmt->execute([$listingID]);
    $listing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$listing) {
        $pdo->rollBack();
        $_SESSION['flashError'] = "That listing no longer exists.";
        header("Location: connect-viewproducts.php");
        exit;
    }

    if ($listing['listingStatus'] !== 'active') {
        $pdo->rollBack();
        $_SESSION['flashError'] = "This item is no longer available.";
        header("Location: connect-productdetails.php?id=" . $listingID);
        exit;
    }

    if ((int)$listing['sellerID'] === (int)$buyerID) {
        $pdo->rollBack();
        $_SESSION['flashError'] = "You can't buy your own listing.";
        header("Location: connect-productdetails.php?id=" . $listingID);
        exit;
    }

    $buyerStmt = $pdo->prepare("SELECT userBalance FROM tblusers WHERE userID = ? FOR UPDATE");
    $buyerStmt->execute([$buyerID]);
    $buyerBalance = (float)$buyerStmt->fetchColumn();

    $price = (float)$listing['listingPrice'];

    if ($buyerBalance < $price) {
        $pdo->rollBack();
        $_SESSION['flashError'] = "Insufficient wallet balance. You have R" . number_format($buyerBalance, 2)
            . " but this item costs R" . number_format($price, 2) . ". Top up your wallet first.";
        header("Location: connect-productdetails.php?id=" . $listingID);
        exit;
    }

    // Move the money
    $pdo->prepare("UPDATE tblusers SET userBalance = userBalance - ? WHERE userID = ?")
        ->execute([$price, $buyerID]);
    $pdo->prepare("UPDATE tblusers SET userBalance = userBalance + ? WHERE userID = ?")
        ->execute([$price, $listing['sellerID']]);

    // Mark the listing sold so nobody else can buy it
    $pdo->prepare("UPDATE tbllistings SET listingStatus = 'sold' WHERE listingID = ?")
        ->execute([$listingID]);

    // Keep a record of the sale
    $receiptNr = generateReceiptNr();
    $pdo->prepare("INSERT INTO tblpurchases (buyerID, sellerID, listingID, amount, receiptNr) VALUES (?, ?, ?, ?, ?)")
        ->execute([$buyerID, $listing['sellerID'], $listingID, $price, $receiptNr]);

    $pdo->commit();

    $_SESSION['flashSuccess'] = "Purchase complete! Receipt: " . $receiptNr;
    header("Location: connect-dashboard.php");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flashError'] = "Something went wrong processing this purchase. Please try again.";
    header("Location: connect-productdetails.php?id=" . $listingID);
    exit;
}