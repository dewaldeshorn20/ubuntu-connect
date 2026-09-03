<?php
if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}
require_once '../includes/database.php';

// Admins only
//Redirects user to the login page if they are not an admin
if (!isset($_SESSION['userID']) || $_SESSION['userRole'] !== 'admin')
{
    header("Location: " . base_URL . "/connect-login.php");
    exit;
}
$errorMessage = '';

// Pick up a flash message left behind by admin-deleteuser.php, then clear it
if (!empty($_SESSION['flashError'])) {
    $errorMessage = "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['flashError']) . "</div>";
    unset($_SESSION['flashError']);
}
if (!empty($_SESSION['flashSuccess'])) {
    $errorMessage = "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['flashSuccess']) . "</div>";
    unset($_SESSION['flashSuccess']);
}

// Handle Role Update (RBAC)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {

    $targetUID = (int)$_POST['user_id'];
    $newRole = $_POST['role'];

    if (!in_array($newRole, ['user', 'admin'])) {
        die("Invalid role.");
    }

    if ($targetUID === $_SESSION['userID']) {
        die("You cannot change your own role.");
    }

    $stmt = $pdo->prepare("UPDATE tblusers SET userRole = ? WHERE userID = ?");
    $stmt->execute([$newRole, $targetUID]);
}

// Gets all users that are on the database
$users = $pdo->query("SELECT userID, userName, userEmail, userRole, isVerified, dateCreated FROM tblusers ORDER BY userName ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>UMAC</h2> 
        <a href="admin-dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
    </div>

    <?= $errorMessage ?>

    <div class="card shadow-sm p-4 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th>Change Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <?= $u['userID'] ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($u['userName']) ?></strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($u['userEmail']) ?>
                        </td>
                       
                        <td>
                            <span class="badge bg-<?= $u['userRole'] === 'admin' ? 'danger' : 'primary' ?>">
                                <?= strtoupper($u['userRole']) ?>
                            </span>
                        </td>
                        <td>
                            <form action="admin-manageusers.php" method="POST" class="d-flex gap-2">
                                <input type="hidden" name="user_id" value="<?= $u['userID'] ?>">
                        <!--Admin selects the new role of the user-->
                                <select name="role" class="form-select form-select-sm" style="width: auto;">
                                    <option value="user" <?= $u['userRole'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $u['userRole'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>

                                <button type="submit" name="update_role" class="btn btn-sm btn-outline-dark">Save</button>
                            </form>
                        </td>
                        <td>
                    <!--Deleting user-->
                            <?php if ($u['userID'] !== $_SESSION['userID']): ?>
                                  <!--Confirm deletetion of the user-->
                                <a href="admin-deleteuser.php?id=<?= $u['userID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user account?');">Delete</a>
                            <?php else: ?>
                                <span class="text-muted small">You (Active)</span>
                            <?php endif; ?>
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