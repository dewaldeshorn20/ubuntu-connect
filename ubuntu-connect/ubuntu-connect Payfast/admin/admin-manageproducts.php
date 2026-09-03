<?php
if (session_status() === PHP_SESSION_NONE) 
{
    session_start();
}
require_once '../includes/database.php';


// Admins only
//Redirects user to the login page if they are not an admin
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'admin') {
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}

// Gets the products from all the pages
$listings = $pdo->query("SELECT l.*, u.userName FROM tbllistings l JOIN tblusers u 
ON l.sellerID = u.userID ORDER BY l.dateCreated DESC")->fetchAll(PDO::FETCH_ASSOC);


$errorMessage = '';

// Handle Product Status changes (For example: Suspending an inappropriate item)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) 
{
    $targetPID = intval($_POST['listing_id']);
    $newStatus = $_POST['status'];
    
  
    if (in_array($newStatus, ['active', 'sold', 'suspended'])) 
    {
        $stmt = $pdo->prepare("UPDATE tbllistings SET listingStatus = ? WHERE listingID = ?");
     if ($stmt->execute([$newStatus, $targetPID])) 
        {
            $errorMessage = "<div class='alert alert-success'>Product status was changed to: " . ucfirst($newStatus) . "</div>";
        }    
        }

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Listings - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Listings</h2>
        <a href="admin-dashboard.php" class="btn btn-secondary">&larr; Back to Previous Page</a>
    </div>

    <?= $errorMessage ?>

    <div class="card shadow-sm p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Condition</th>
                        <th>Status Control</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($listings) > 0): ?>
                        <?php foreach ($listings as $l): ?>
                        <tr>

                            <td>
                                <img src="../uploads/<?= htmlspecialchars($l['listingImage'] ?: 'placeholder.png') ?>" 
                                alt="Thumb" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($l['listingTitle']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($l['listingCategory']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($l['userName']) ?>
                            </td>

                            <td class="text-success fw-bold">R <?= number_format($l['listingPrice'], 2) ?>
                            </td>

                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($l['listingCondition']) ?></span>
                            </td>

                            <td>
                                <form action="admin-manageproducts.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="listing_id" value="<?= $l['listingID'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width: auto;">
                                        <option value="active" <?= $l['listingStatus'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="sold" <?= $l['listingStatus'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                                        <option value="suspended" <?= $l['listingStatus'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                            <td>
                                <form action="admin-deleteproduct.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                    <input type="hidden" name="id" value="<?= $l['listingID'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No listings were found</td>
                        </tr>
                     <?php endif; ?>
                 </tbody>
              </table>
         </div>
     </div>
 </div>
</body>
</html>