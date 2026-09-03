<?php
$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) 
    { 
        die("Config file was not found."); //Kills the process if the database doesn't exist
        
    }
require_once __DIR__ . '/config.php'; // will load the config file is it doesnt exist
/*try {
    $pdo = new PDO(
        'mysql:host=' . host_DB . ';dbname=' . db_Name . ";charset=utf8",  //This is the database's conenction string
        db_User,
        db_Password
    ); //This is the local host's database conenction string */
try {
    $pdo = new PDO(
    "mysql:host=" . host_DB . ";dbname=" . db_Name . ";charset=utf8mb4",
    db_User,
    db_Password
);////This is the live host's database conenction string 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} 
// Gives an error message if the connection has failed
catch (PDOException $r) {
    die("Database connection error: " . $r->getMessage());
}


?>