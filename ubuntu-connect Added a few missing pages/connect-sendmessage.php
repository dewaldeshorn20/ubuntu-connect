<?php

include 'includes/header.php';

// User must be logged in
if (!isset($_SESSION['userID'])) {
    header("Location: connect-login.php");
    exit;
}

$errorMessage = '';
$receiverID = $_GET['userID'] ?? null;
$listingID = $_GET['listingID' ?? null];

if (!isset($_GET['userID'])) {
    header("Location: connect-messages.php");
    exit;
}

if (!$receiverID) {
    header("Location: connect-messages.php");
    exit;
}

// Get seller details
$stmt = $pdo->prepare("SELECT userID, userName FROM tblusers WHERE userID = ?");
$stmt->execute([$receiverID]);
$seller = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seller) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $receiverID = $_POST['receiverID'];
    $message = trim($_POST['message']);
    $dateCreated = date('Y-m-d H:i:s');


    if (!empty($message)) {

        $stmt = $pdo->prepare("
            INSERT INTO tblmessages
            (listingID, senderID, receiverID, message, dateCreated)
            VALUES (?, ?, ?, ?, NOW())
        ");

        if ($stmt->execute([
            $listingID,
            $_SESSION['userID'],
            $receiverID,
            $message
        ])) {
            header("Location: connect-conversation.php?userID=" . $receiverID . "&listingID=" . $listingID);
            exit;
        }

    } else {

        $errorMessage =
        "<div class='alert alert-danger'>
            Please enter a message.
        </div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">

        <div class="card shadow-sm p-4">

            <h3 class="mb-3">
                Send Message to
                <?= htmlspecialchars($seller['userName']) ?>
            </h3>

            <?= $errorMessage ?>

            <form method="POST">
<input type="hidden" name="receiverID" value="<?= htmlspecialchars($receiverID) ?>">

                <div class="mb-3">
                    <label class="form-label">
                        Message
                    </label>

                    <textarea
                        name="message"
                        rows="5"
                        class="form-control"
                        required
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Send Message
                </button>

            </form>

        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>