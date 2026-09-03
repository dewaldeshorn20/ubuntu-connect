<?php
include 'includes/header.php';

// Display all active products
$stmt = $pdo->query("SELECT l.*, u.userName FROM tbllistings l
 JOIN tblusers u ON l.sellerID = u.userID
  WHERE l.listingStatus = 'active' 
  ORDER BY l.dateCreated DESC");

$allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flashError = '';
if (!empty($_SESSION['flashError'])) {
    $flashError = $_SESSION['flashError'];
    unset($_SESSION['flashError']);
}
?>

<!--page header-->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Browse All Products</h2>
    <span class="badge bg-secondary"><?= count($allItems) ?> Items Available</span>
</div>

<!--Product table-->
<div class="row">

    <?php foreach ($allItems as $item): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">

                <img src="uploads/<?= htmlspecialchars($item['listingImage'] ?: 'placeholder.png') ?>" class="card-img-top" alt="Product Image" style="height: 180px; object-fit: cover;">
                
                <div class="card-body d-flex flex-column">

                    <!--Category lable-->
                    <span class="badge bg-light text-dark align-self-start mb-2"><?= htmlspecialchars($item['listingCategory']) ?></span>

                    <!--Product card-title-->
                    <h6 class="card-title text-truncate"><?= htmlspecialchars($item['listingTitle']) ?></h6>
                    <p class="fs-6 fw-bold text-success mb-3">R <?= number_format($item['listingPrice'], 2) ?></p>

                    <!--Link to product details page-->
                    <a href="connect-productdetails.php?id=<?= $item['listingID'] ?>" class="btn btn-sm btn-primary w-100 mt-auto">View Item</a>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>