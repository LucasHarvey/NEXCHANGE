<?php

include_once "_generics.php";

if($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "Users/users_SEARCH.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

