<?php
//simple logout script
//init session
session_start();

//unset all sessions variables
$_SESSION = array();

//destroy session
session_destroy();

//redirect to login
header("location: index.php");
exit;
?>