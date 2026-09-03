<?php
include 'includes/header.php';

//kick users out if there is no active session
if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

// Fetch user's current account financial stats
$userStmt = $pdo->prepare("SELECT userBalance FROM tblusers WHERE userID = ?");
$userStmt->execute([$_SESSION['userID']]);
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's active listings
$stmt = $pdo->prepare("SELECT * FROM tbllistings WHERE sellerID = ? ORDER BY dateCreated DESC");
$stmt->execute([$_SESSION['userID']]);
$myListings = $stmt->fetchAll(PDO::FETCH_ASSOC);

//safety check on database fetch
if (!$userData) {
    $userData = ['userBalance' => 0];
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
?>

<div class="row mb-4"> 
    <div class="col-12">
        <!--Welcome heading whichdisplays logged in user name-->
        <h2>Welcome back, <?= htmlspecialchars($_SESSION['userName']) ?>!</h2> 

        <!--Balance summary and quick action button-->
        <div class="p-3 bg-white border rounded shadow-sm d-flex justify-content-between align-items-center mt-3">
        <div>
                <span class="text-muted d-block text-uppercase small font-weight-bold">Balance</span>
                <span class="fs-3 fw-bold text-success">R <?= number_format($userData['userBalance'] ?? 0, 2) ?></span>
            </div>
            <a href="wallet-topup.php" class="btn btn-outline-success">Top Up Wallet</a>
            <a href="connect-createproduct.php" class="btn btn-success">Create New Listing</a>
        </div>
    </div>
</div>

<!-- User Listings Table-->
<h3 class="mb-3">Your Posts</h3>
<div class="card shadow-sm p-3 bg-white">
    <?php if (count($myListings) > 0): ?> <!--Displays the listings if the user has any-->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Item</th><th>Price</th><th>Status</th><th>Date Posted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myListings as $list): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($list['listingTitle']) ?></strong></td>
                            <td>R <?= number_format($list['listingPrice'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $list['listingStatus'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($list['listingStatus']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d', strtotime($list['dateCreated'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted mb-0">You haven't listed any items for sale yet.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>