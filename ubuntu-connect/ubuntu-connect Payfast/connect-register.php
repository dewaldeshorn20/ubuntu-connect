<?php
include 'includes/header.php';

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = trim($_POST['username'] ?? '');
    $userEmail = trim($_POST['email'] ?? '') ;
    $userPassword = trim($_POST['password'] ?? '');

    // Check if user already exists
    if (!empty($userName) && !empty($userEmail) && !empty($userPassword)) {
    
        $check = $pdo->prepare("SELECT userID FROM tblusers WHERE userEmail = ?");
        $check->execute([$userEmail]);
        
        //Checks that the email is valid
       if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL))
        {
        $errorMessage = "<div class='alert alert-warning'>Invalid email format.</div>";
        }
        if ($check->rowCount() > 0)
        {
            $errorMessage = "<div class='alert alert-danger'>Email address already registered.</div>";
        }
         else 
        {
            //password length check
            if (strlen($userPassword) < 6) 
            {
                $errorMessage = "<div class='alert alert-warning'>Password must be at least 6 characters.</div>";
            }
            else{
            //Hash the password
            $hashedPass = password_hash($userPassword, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO tblusers (userEmail, userName, userPassWord, userRole) VALUES (?, ?, ?, 'user')");
                if ($ins->execute([$userEmail, $userName, $hashedPass])) {
                $errorMessage = "<div class='alert alert-success'>Registration successful! <a href='connect-login.php'>Click here to login</a></div>";
            } else {
                $errorMessage = "<div class='alert alert-danger'>Database submission error.</div>";
            }
            }

        
        }
    } 
    else 
    {
        $errorMessage = "<div class='alert alert-warning'>All fields are required.</div>";
    }
}
?>

<!--Registration Page-->
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm p-4">

            <h3 class="text-center mb-3">Create an Account</h3>
            <?= $errorMessage ?>

            <form action="connect-register.php" method="POST">
            <!--Username input -->
                <div class="mb-3">
                    <label class="form-label">Full Name / Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <!--Email adress input-->
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <!--password input-->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <!--submit button-->
                <button type="submit" class="btn btn-warning w-100 text-white">Register</button>

            </form>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>