<?php

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "NavBar/navbar_GET.php";
  $conn = database_connect();
  echoSuccess($conn, getNavbarItems($conn));
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

