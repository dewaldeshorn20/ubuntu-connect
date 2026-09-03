<?php
include 'includes/header.php';

//Immediately capture any empty or unset routing query signatures.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    header("Location: connect-viewproducts.php");
    exit;
}

//validating id 
if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}


//Get all the product details
$stmt = $pdo->prepare("SELECT l.*, u.userName, u.userEmail FROM tbllistings l JOIN tblusers u ON l.sellerID = u.userID WHERE l.listingID = ?");
$listingID = (int)$_GET['id'];
$stmt->execute([$listingID]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

//Error message if no products were found
if (!$item) {
    echo "<div class='alert alert-danger'>Product listing not found.</div>";
    include 'includes/footer.php';
    exit;
}

$flashError = '';
$flashSuccess = '';

if (!empty($_SESSION['flashError'])) {
    $flashError = $_SESSION['flashError'];
    unset($_SESSION['flashError']);
}
if (!empty($_SESSION['flashSuccess'])) {
    $flashSuccess = $_SESSION['flashSuccess'];
    unset($_SESSION['flashSuccess']);
}

$isOwnListing = (int)$item['sellerID'] === (int)($_SESSION['userID'] ?? 0);
?>

<?php if ($flashError): ?><div class="alert alert-danger"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>
<?php if ($flashSuccess): ?><div class="alert alert-success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>

<!--Product Layout-->
<div class="row mt-4">

    <div class="col-md-6 mb-4">
        <img src="uploads/<?= htmlspecialchars($item['listingImage'] ?: 'placeholder.png') ?>" class="img-fluid rounded shadow-sm w-100" alt="Product Image">
    </div>

    <div class="col-md-6">

<!--Product Category-->
        <span class="badge bg-primary mb-2"><?= htmlspecialchars($item['listingCategory']) ?></span>

<!--Price and Title-->
        <h1 class="mb-2"><?= htmlspecialchars($item['listingTitle']) ?></h1>
        <h2 class="text-success mb-4">R <?= number_format($item['listingPrice'], 2) ?></h2>
       
        <!--Product Description-->
        <div class="card p-3 bg-white mb-4">
            <h5>Product Description</h5>
            <p class="text-secondary"><?= nl2br(htmlspecialchars($item['listingDescription'])) ?></p>
            <hr>
            <p class="mb-1"><strong>Condition:</strong> <?= ucfirst($item['listingCondition']) ?></p>
            <p class="mb-0"><strong>Listed on:</strong> <?= date('F j, Y', strtotime($item['dateCreated'])) ?></p>
        </div>

       <!--Seller information-->
        <div class="card p-3 bg-light border-0">
            <h5>Seller Information</h5>
            <p class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($item['userName']) ?></p>
            <?php if (isset($_SESSION['userID'])): ?>
            <a href="connect-sendmessage.php?userID=<?= $item['sellerID'] ?>&listingID=<?= $item['listingID'] ?>">
                Message Seller
            </a>
            <?php else: ?>
                <a href="connect-login.php" class="btn btn-outline-dark">Log in to view Contact Details</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>