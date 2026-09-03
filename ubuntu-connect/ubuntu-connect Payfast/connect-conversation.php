<?php
include 'includes/header.php';

$otherUserID = $_GET['userID'] ?? null;
$listingID = $_GET['listingID'] ?? null;

// Redirect users who are not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: connect-login.php");
    exit;
}

$stmt = $pdo->prepare("
 
");

$stmt->execute([
    $listingID,
    $_SESSION['userID'],
    $otherUserID,
    $otherUserID,
    $_SESSION['userID']
]);

$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Logged in user: " . $_SESSION['userID'] . "<br>";
echo "Other user: " . $otherUserID . "<br>";
echo "Listing: " . $listingID . "<br>";
echo "Messages found: " . count($messages) . "<br>";
?>

<div class="container">

    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Your Messages</h2>
        <span class="badge bg-primary">
            <?= count($messages) ?> Message(s)
        </span>
    </div>

    <!-- Messages Card -->
    <div class="card shadow-sm">
        <div class="card-body">

            <?php if (count($messages) > 0): ?>

                <?php foreach ($messages as $message): ?>

                    <?php
                    $date = $message['dateCreated'] ?? null;

                    if (!empty($date) && $date !== '0000-00-00 00:00:00') {
                        $formattedDate = date('d M Y H:i', strtotime($date));
                    } else {
                        $formattedDate = 'Unknown date';
                    }
                    ?>

                    <div class="border-bottom pb-3 mb-3">

                        <!-- Sender Information -->
                        <h5 class="mb-1">
                            <?= htmlspecialchars($message['userName']) ?>
                        </h5>

                        <!-- Message Content -->
                        <p class="mb-2">
                            <?= nl2br(htmlspecialchars($message['message'])) ?>
                        </p>

                        <!-- Date Sent -->
                        <small class="text-muted">
                            <?= $formattedDate ?>
                        </small>

                        <br>

                        <!-- View Conversation Button -->
                       <a href="connect-conversation.php?userID=<?= $message['senderID'] ?>&listingID=<?= $message['listingID'] ?>"
                            class="btn btn-sm btn-outline-primary mt-2">
                                View Conversation
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="alert alert-info">
                    You currently have no messages.
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>