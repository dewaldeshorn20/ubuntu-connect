<?php
include 'includes/header.php';
require_once 'includes/payment-tables.php';

if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

$topupID = isset($_GET['topup']) ? intval($_GET['topup']) : 0;
$topup = null;
if ($topupID > 0) {
    $stmt = $pdo->prepare("SELECT * FROM tblwallettopups WHERE topupID = ? AND userID = ?");
    $stmt->execute([$topupID, $_SESSION['userID']]);
    $topup = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="card p-4 shadow-sm" style="max-width: 500px;">
    <?php if ($topup && $topup['status'] === 'complete'): ?>
        <h4 class="text-success">Payment received!</h4>
        <p>R<?= number_format($topup['amount'], 2) ?> has been added to your wallet.</p>
    <?php else: ?>
        <h4>Payment received - processing</h4>
        <p>Thanks! PayFast is confirming your payment with us now. Your balance updates automatically
        as soon as that confirmation comes through, usually within a few seconds. Refresh your
        <a href="connect-dashboard.php">dashboard</a> shortly to see the updated balance.</p>
    <?php endif; ?>
    <a href="connect-dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>

<?php include 'includes/footer.php'; ?>