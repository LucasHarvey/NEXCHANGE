<?php

if ($_SERVER["REQUEST_METHOD"] == "DELETE"){
  $NO_AUTH_CHECKS = true; //Disable auth checks: we will do them within auth_DELETE_ALL.php
}

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "DELETE"){
  include_once "Auth/auth_DELETE_ALL.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

