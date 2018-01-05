<?php

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "NavBar/navbar_GET.php";
  $conn = database_connect();
  $user_id = getUserFromToken();
  echoSuccess($conn, getNavbarItems($conn, $user_id));
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

