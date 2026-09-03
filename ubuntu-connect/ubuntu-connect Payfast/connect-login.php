<?php
include 'includes/header.php';

$errorMessage = '';

//This code checks if the user has filled out the form and triggered submission logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ensures that fields are not empty 
    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM tblusers WHERE userEmail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

// validation check against secure database security hash
        if ($user && password_verify($password, $user['userPassWord']))
             {
            // populate active session variables
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['userName'] = $user['userName'];
            $_SESSION['userRole'] = $user['userRole'];
            

            //system access permissions
            if ($user['userRole'] === 'admin') {
                header("Location: admin/admin-dashboard.php");
            } else {
                header("Location: connect-dashboard.php");
            }
            exit;

        } else 
        {
            $errorMessage = "Invalid email or password.";
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }
}
?>
<!--Login Form-->
<div class="row justify-content-center">
    <div class="col-md-5">

        <div class="card shadow-sm p-4">
            <h2 class="text-center mb-4">Login to Ubuntu Connect</h2>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?= $errorMessage ?></div>
            <?php endif; ?>
            <form action="connect-login.php" method="POST" id="loginForm">
                <div class="mb-3">

                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-all form-control" required>

                </div>
                <div class="mb-3">

                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>

                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>

<script src="js/code-behind.js"></script>
<?php include 'includes/footer.php'; ?>