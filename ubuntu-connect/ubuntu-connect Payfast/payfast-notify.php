<?php
// PayFast calls this URL directly (server-to-server), not the user's browser.
// Never trust wallet-topup-return.php to credit money - a user could hit that
// URL themselves without paying. Only this file, after all checks below pass,
// is allowed to add money to a balance.
require_once 'includes/database.php';
require_once 'includes/payment-tables.php';

function pfLog(string $msg): void {
    file_put_contents(__DIR__ . '/payfast-itn.log', date('c') . ' ' . $msg . PHP_EOL, FILE_APPEND);
}

http_response_code(200); // acknowledge receipt regardless of outcome, per PayFast's requirements

$pfData = $_POST;
if (empty($pfData)) {
    pfLog('Empty POST received - ignoring.');
    exit;
}

// STEP 1: verify the signature PayFast sent matches what we'd compute,
// so the payload wasn't tampered with in transit.
$pfParamString = '';
foreach ($pfData as $key => $val) {
    if ($key !== 'signature') {
        $pfParamString .= $key . '=' . urlencode(trim($val)) . '&';
    }
}
$pfParamString = rtrim($pfParamString, '&');
if (PAYFAST_PASSPHRASE !== '') {
    $pfParamString .= '&passphrase=' . urlencode(trim(PAYFAST_PASSPHRASE));
}
$signatureMatch = isset($pfData['signature']) && md5($pfParamString) === $pfData['signature'];

if (!$signatureMatch) {
    pfLog('Signature mismatch for m_payment_id=' . ($pfData['m_payment_id'] ?? '?'));
    exit;
}

// STEP 2: confirm the request actually came from PayFast's own servers.
// NOTE: this requires an outbound connection from this server back out to
// PayFast. Some free hosts (this project is on InfinityFree's free tier)
// block outbound connections entirely, which would make this check silently
// fail closed (safe, but top-ups would never complete). If that happens here,
// it's a hosting limitation, not a bug in this code - the fix is either a
// host that allows outbound connections, or (for coursework demo purposes
// only, NOT for anything handling real money) relaxing this one check.
$pfHost = PAYFAST_VALIDATE_HOST;
$validated = false;
$ch = curl_init("https://{$pfHost}/eng/query/validate");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pfData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($response !== false && trim($response) === 'VALID') {
    $validated = true;
} else {
    pfLog('PayFast validate call failed or returned invalid. curl_error=' . $curlError . ' response=' . var_export($response, true));
}

if (!$validated) {
    exit;
}

// STEP 3: only trust "COMPLETE" payments, and match the amount to what we
// actually asked for - never trust the amount purely from the POST body.
$topupID = intval($pfData['m_payment_id'] ?? 0);
$paymentStatus = $pfData['payment_status'] ?? '';
$pfPaymentId = $pfData['pf_payment_id'] ?? null;

if ($topupID <= 0 || $paymentStatus !== 'COMPLETE') {
    pfLog("Ignoring non-complete or invalid payment. topupID=$topupID status=$paymentStatus");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tblwallettopups WHERE topupID = ?");
$stmt->execute([$topupID]);
$topup = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$topup) {
    pfLog("No matching topup row for topupID=$topupID");
    exit;
}

if ($topup['status'] === 'complete') {
    pfLog("Topup $topupID already processed - ignoring duplicate notification.");
    exit;
}

$expectedAmount = number_format((float)$topup['amount'], 2, '.', '');
$paidAmount = number_format((float)($pfData['amount_gross'] ?? 0), 2, '.', '');

if ($expectedAmount !== $paidAmount) {
    pfLog("Amount mismatch for topup $topupID: expected $expectedAmount got $paidAmount");
    $pdo->prepare("UPDATE tblwallettopups SET status = 'failed' WHERE topupID = ?")->execute([$topupID]);
    exit;
}

// All checks passed - credit the wallet and mark the topup complete, atomically.
try {
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE tblusers SET userBalance = userBalance + ? WHERE userID = ?")
        ->execute([$topup['amount'], $topup['userID']]);
    $pdo->prepare("UPDATE tblwallettopups SET status = 'complete', pfPaymentId = ? WHERE topupID = ?")
        ->execute([$pfPaymentId, $topupID]);
    $pdo->commit();
    pfLog("Topup $topupID complete - credited R{$topup['amount']} to user {$topup['userID']}");
} catch (PDOException $e) {
    $pdo->rollBack();
    pfLog("DB error crediting topup $topupID: " . $e->getMessage());
}