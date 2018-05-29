<?php

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

$NO_AUTH_CHECKS = false; //Enable auth checks
if($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "DELETE"){
    $NO_AUTH_CHECKS = true; //Disable auth checks when creating a user (The user doesnt exist how would they be logged in?)
}
include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  include_once "Auth/auth_POST.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "DELETE"){
  include_once "Auth/auth_DELETE.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

