<?php
$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) { 
    die("Config file was not found.");
}

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . host_DB . ';dbname=' . db_Name . ";charset=utf8",
        db_User,
        db_Password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $r) {
    die("Database connection error: " . $r->getMessage());
}

function requireLogin(): void 
{
    global $pdo;
    if (!isset($_SESSION['userID'])) //Checks if the user is logged in 
    {
    header("Location: " . base_URL . "/connect-login.php");//redirects them to the login page if not
    exit();
    }
}

//Checks if the page needs the user to be an admin
function requireAdmin(): void 
{
    if (!isset($_SESSION['userID']) || $_SESSION['userRole'] != 'admin')
    {
    header("Location: " . base_URL . "/connect-login.php");
    exit();
    }
}

//Checkss if the user is an admin 
function isAdmin(): bool 
{
    return isset($_SESSION['userRole']) && $_SESSION['userRole'] == 'admin';  
}

function isLoggedIn(): bool {
    return isset($_SESSION['userID']);
}

//generates a unique receipt number for every phurchase
//based on date and a unique id
function generateReceiptNr(): string 
{
    return 'RN-' . strtoupper(uniqid()) . '' . date('Ymd');
}


function updateUserRating(int $sellerID): void
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT AVG(userRating) as avgRating, COUNT(*) as Total FROM tblreviews WHERE sellerID = ?");
    $stmt->execute([$sellerID]);
    $result = $stmt->fetch();
    $pdo->prepare("UPDATE tblusers SET userRating = ? WHERE userID = ?")->execute([$result['avgRating'] ?? 0, $sellerID]);//Updates the seller rating based on the reviews they got 
}

    function getUnreadMessages() {
    global $pdo;

    if (!isset($_SESSION['userID'])) {
        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM tblmessages 
        WHERE receiverID = ? 
        AND is_read = 0 
    ");
    
    


    $stmt->execute([$_SESSION['userID']]);

    return $stmt->fetchColumn();
    }
    
?>