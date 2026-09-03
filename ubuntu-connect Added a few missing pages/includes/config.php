<?php   
   //Site information when on local
   /*define('host_DB', 'localhost');
    define('db_Name', 'ubuntuconnect'); // Name of the database
    define('db_User', 'root'); 
    define('db_Password', ''); 
    define('base_URL', 'http://localhost/ubuntu-connect'); // Base URL
    define('siteName', 'Ubuntu Connect');   // Site name */

    //Site information when live
  // Site information when live
    define('host_DB', 'sql313.infinityfree.com');
    define('db_Name', 'if0_42103990_ubuntuconnect');
    define('db_User', 'if0_42103990');
    define('db_Password', 'Meruudeh231');
    define('base_URL', 'https://ubuntuconnect.rf.gd/ubuntu-connect/');
    define('siteName', 'Ubuntu Connect');

       // ---- PayFast settings ----
    // Sandbox credentials below are PayFast's shared public test account -
    // fine for testing, but the ITN check in payfast-notify.php will only
    // accept notifications from PayFast's sandbox host while this is true.
    // Get your own free sandbox account at https://sandbox.payfast.co.za/
    // and swap these for your own Merchant ID/Key when you're ready to test
    // properly, then swap PAYFAST_SANDBOX to false + use live credentials
    // when you actually go live.
    define('PAYFAST_SANDBOX', true);
    define('PAYFAST_MERCHANT_ID', '10000100');
    define('PAYFAST_MERCHANT_KEY', '46f0cd694581a');
    define('PAYFAST_PASSPHRASE', ''); // set this if you configure one in your PayFast account settings
    define('PAYFAST_PROCESS_URL', PAYFAST_SANDBOX ? 'https://sandbox.payfast.co.za/eng/process' : 'https://www.payfast.co.za/eng/process');
    define('PAYFAST_VALIDATE_HOST', PAYFAST_SANDBOX ? 'sandbox.payfast.co.za' : 'www.payfast.co.za');

    //This code enables so that all error messages are displayed
    //Turn off when deploying
    /*ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
*/
ini_set('display_errors', 0);
error_reporting(0);
?>