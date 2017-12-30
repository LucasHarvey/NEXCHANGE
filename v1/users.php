<?php

include_once "_generics.php";

if($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "Users/users_GET.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "POST"){
  include_once "Users/users_POST.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "PUT"){
  include_once "Users/users_PUT.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "DELETE"){
  include_once "Users/users_DELETE.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

