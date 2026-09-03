<?php
include 'includes/header.php';

// Redirect users who are not logged in
if (!isset($_SESSION['userID'])) {
    header("Location: connect-login.php");
    exit;
}

// Get all messages for the current user
$stmt = $pdo->prepare("
    SELECT m.*, u.userName
    FROM tblmessages m
    JOIN tblusers u ON m.senderID = u.userID
    WHERE m.receiverID = ?
    ORDER BY m.dateCreated DESC
");

$stmt->execute([$_SESSION['userID']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <?php 
                    $date = $message['dateCreated'] ?? null;           
                        if (!empty($date) && $date !== '0000-00-00 00:00:00') {
                          $formattedDate = date('d M Y H:i', strtotime($date));
                         } else {
                         $formattedDate = 'Unknown date';
                         }       
                         ?>
                    </div>

              <a href="connect-sendmessage.php?userID=<?= $message['senderID'] ?>&listingID=<?= $message['listingID'] ?>"
                class="btn btn-sm btn-primary">
                     Reply
             </a>

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