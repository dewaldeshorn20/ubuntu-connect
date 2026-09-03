<?php
session_start();
//Clear session
$_SESSION = [];
session_destroy();

// Redirect users to the login page
header("Location: connect-login.php");
exit();
?>