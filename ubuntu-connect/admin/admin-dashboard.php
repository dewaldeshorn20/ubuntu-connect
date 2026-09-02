<?php
// Creates a new session only if there isn't one already created
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}

// Loads the database connection from the shared config file
require_once '../includes/database.php'; 

//Access Control
//Only allows users with the admin role to access 
//this page and redirects non admin users back to the login page
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}


//Initializing variables for the statistics
$totalUsers = 0;
$totalListings = 0;

// Queries to get the totals for the admin dashboard
$totalUsers = $pdo->query("SELECT COUNT(*) FROM tblusers")->fetchColumn();
$totalListings = $pdo->query("SELECT COUNT(*) FROM tbllistings")->fetchColumn();

// Gets the users and listings for a management view
$users = $pdo->query("SELECT userID, userName, userEmail, userRole, isVerified FROM tblusers ORDER BY dateCreated DESC")->fetchAll(PDO::FETCH_ASSOC);
$listings = $pdo->query("SELECT l.listingID, l.listingTitle, l.listingPrice, l.listingStatus, u.userName FROM tbllistings l JOIN tblusers u ON l.sellerID = u.userID ORDER BY l.dateCreated DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <!--This is the page title shown in the browser tab-->
    <title>Admin System Panel</title>

    <!--Bootstrap link for styling and responsive layout-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- This is the main dashboard container -->
<div class="container my-5">

    <!--This is the header section which includes the page title and logout button-->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Ubuntu-Connect Administrative Dashboard</h1>

        <!--This part of the code is used to logout safely by destroying the session and redirecting to the login page-->
        <a href="../connect-logout.php" class="btn btn-outline-danger">Log Out</a>
    </div>

    <!--dashboard statistics-->
    <div class="row mb-5">

        <!--Total users card-->
        <div class="col-md-6">
            <div class="card bg-primary text-white p-4 shadow-sm">
                <h3>Total Users</h3>
                <p class="fs-2 mb-0"><?= $totalUsers ?></p>
            </div>
        </div>

        <!--Total active listings card-->
        <div class="col-md-6">
            <div class="card bg-success text-white p-4 shadow-sm">
                <h3>Total Listings active</h3>
                <p class="fs-2 mb-0"><?= $totalListings ?></p>
            </div>
        </div>
    </div>

    <!--Table where users can be managed-->
    <div class="card p-4 shadow-sm mb-5">
        <h3 class="mb-3 text-secondary">User Administration</h3>

        <div class="table-responsive">
            <table class="table table-striped align-middle">

            <!--defining table headers-->
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <!--For loop to go through all users and display them-->
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= $u['userID'] ?></td>
                        <td><?= htmlspecialchars($u['userName']) ?></td>
                        <td><?= htmlspecialchars($u['userEmail']) ?></td>

                        <!--Displays the role as a badge for clarity-->
                        <td><span class="badge bg-info text-dark"><?= $u['userRole'] ?></span></td>
                       
                       <!--Display the badge-->
                        <td><?= $u['isVerified'] ? 'Verified' : 'Unverified' ?></td>
                        <td>
                            <!--Confirmation form the admin for deletion of a user-->
                            <a href="admin-deleteuser.php?id=<?= $u['userID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!--Table to manage user listings-->
    <div class="card p-4 shadow-sm">

        <h3 class="mb-3 text-secondary">Product & Marketplace Administration</h3>

        <div class="table-responsive">
            <table class="table table-striped align-middle">

            <!--Defining table headers-->
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Listing Title</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!--For loop to go through all listings and display them-->
                    <?php foreach($listings as $l): ?>
                    <tr>
                        <td><?= $l['listingID'] ?></td>
                        <td><?= htmlspecialchars($l['listingTitle']) ?></td>
                        <td><?= htmlspecialchars($l['userName']) ?></td>
                        <!--Format the price so that it displays in Rand-->
                        <td>R <?= number_format($l['listingPrice'], 2) ?></td>

                        <!--Badge for visual clarity-->
                        <td><span class="badge bg-warning text-dark"><?= $l['listingStatus'] ?></span></td>

                        <!--Deleting the listing with confirmation-->
                        <td>
                            <form action="admin-deleteproduct.php" method="POST" onsubmit="return confirm('Delete this listing?');">
                            <input type="hidden" name="id" value="<?= $l['listingID'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>