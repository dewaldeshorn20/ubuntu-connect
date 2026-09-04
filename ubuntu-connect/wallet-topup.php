<?php
include 'includes/header.php';
require_once 'includes/payment-tables.php';

if (!isset($_SESSION['userID'])) {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = round(floatval($_POST['amount']), 2);

    if ($amount < 10) {
        $errorMessage = "<div class='alert alert-danger'>Minimum top-up amount is R10.00.</div>";
    } else {
        // Record the pending top-up so payfast-notify.php has something to match
        // the notification against and knows exactly whose balance to credit.
        $stmt = $pdo->prepare("INSERT INTO tblwallettopups (userID, amount, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['userID'], $amount]);
        $topupID = $pdo->lastInsertId();

        // Session doesn't carry the email, so grab it from the DB for PayFast
        $emailStmt = $pdo->prepare("SELECT userEmail FROM tblusers WHERE userID = ?");
        $emailStmt->execute([$_SESSION['userID']]);
        $userEmail = $emailStmt->fetchColumn() ?: '';

        // Build the PayFast payment fields. m_payment_id carries our topupID
        // back to us so the ITN handler knows which row to update.
        $pfData = [
            'merchant_id'  => PAYFAST_MERCHANT_ID,
            'merchant_key' => PAYFAST_MERCHANT_KEY,

            'return_url'   => base_URL . '/wallet-topup-return.php?topup=' . $topupID,
            'cancel_url'   => base_URL . '/wallet-topup-cancel.php?topup=' . $topupID,
            'notify_url'   => base_URL . '/payfast-notify.php',

            'name_first'   => $_SESSION['userName'],
            'email_address'=> $userEmail,
            'm_payment_id' => (string)$topupID,
            'amount'       => number_format($amount, 2, '.', ''),
            'item_name'    => 'Ubuntu Connect wallet top-up',
        ];

        // Drop empty fields (PayFast's signature check fails if you sign a blank field)
        $pfData = array_filter($pfData, fn($v) => $v !== '' && $v !== null);

        // PayFast signature: an MD5 of every field in the exact order given,
        // URL-encoded the same way PayFast itself encodes it, plus the
        // passphrase appended if one is configured.
        $pfParamString = '';
        foreach ($pfData as $key => $val) {
            $pfParamString .= $key . '=' . urlencode(trim($val)) . '&';
        }
        $pfParamString = rtrim($pfParamString, '&');
        if (PAYFAST_PASSPHRASE !== '') {
            $pfParamString .= '&passphrase=' . urlencode(trim(PAYFAST_PASSPHRASE));
        }
        $pfData['signature'] = md5($pfParamString);

        ?>
        <!DOCTYPE html>
        <html>
        <head><title>Redirecting to PayFast...</title></head>
        <body onload="document.forms[0].submit()">
            <p>Redirecting you to PayFast to complete payment...</p>
            <form action="<?= PAYFAST_PROCESS_URL ?>" method="POST">
                <?php foreach ($pfData as $key => $val): ?>
                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
                <?php endforeach; ?>
                <noscript><button type="submit">Continue to PayFast</button></noscript>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}
?>

<h2>Top Up Your Wallet</h2>
<?= $errorMessage ?>
<form action="wallet-topup.php" method="POST" class="card p-4 shadow-sm" style="max-width: 400px;">
    <div class="mb-3">
        <label class="form-label">Amount (ZAR)</label>
        <input type="number" step="0.01" min="10" name="amount" class="form-control" required>
        <small class="text-muted">Minimum R10.00</small>
    </div>
    <button type="submit" class="btn btn-success">Continue to Payment</button>
</form>

<?php include 'includes/footer.php'; ?>
