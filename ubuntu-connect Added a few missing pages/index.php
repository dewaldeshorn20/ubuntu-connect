<?php
include 'includes/header.php';

// Get the 6 latest active items up for sale to be displayed on the home screen
$stmt = $pdo->query("SELECT l.*, u.userName FROM tbllistings l
 JOIN tblusers u ON l.sellerID = u.userID
 WHERE l.listingStatus = 'active' 
 ORDER BY l.dateCreated DESC LIMIT 6");

 if (!$stmt) { die("No Listings Were Find"); }

$recentItems = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
?>

<div class="p-5 mb-4 bg-dark text-white rounded-3">
    <div class="container-fluid py-5 text-center">

        <h1 class="display-5 fw-bold">Welcome to Ubuntu Connect</h1>
        <p class="fs-4">The ultimate community-driven C2C marketplace. Buy and sell with trust!</p>
      
        <?php if(!isset($_SESSION['userID'])): ?>
            <a href="connect-register.php" class="btn btn-warning btn-lg">Join the Community</a>

        <?php else: ?>
            <a href="connect-createproduct.php" class="btn btn-success btn-lg">Start Selling Now</a>
        <?php endif; ?>
    </div>
</div>

<h2 class="mb-4">Recent Marketplace Listings</h2>
<div class="row">
    <?php if (count($recentItems) > 0): ?>
        <?php foreach ($recentItems as $item): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="uploads/<?= htmlspecialchars($item['listingImage'] ?: 'placeholder.png') ?>" 
                    class="card-img-top" alt="Product Image" style="height: 200px; object-fit: cover;">

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($item['listingTitle']) ?></h5>

                        <p class="text-muted small">Seller: <?= htmlspecialchars($item['userName']) ?> | Condition: <?= ucfirst($item['listingCondition']) ?></p>
                        <p class="card-text text-truncate"><?= htmlspecialchars($item['listingDescription']) ?></p>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-success">R <?= number_format($item['listingPrice'], 2) ?></span>
                            <a href="connect-productdetails.php?id=<?= $item['listingID'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">No active listings available right now. Be the first to post!</div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>