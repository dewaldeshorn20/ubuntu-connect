<?php
include 'includes/header.php';

if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}
?>

<div class="card p-4 shadow-sm" style="max-width: 500px;">
    <h4>Payment cancelled</h4>
    <p>No worries - nothing was charged. You can try again whenever you're ready.</p>
    <a href="wallet-topup.php" class="btn btn-primary">Try Again</a>
    <a href="connect-dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
</div>

<?php include 'includes/footer.php'; ?>