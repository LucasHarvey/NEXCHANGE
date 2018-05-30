<?php

$NO_AUTH_CHECKS = true;

include_once "_generics.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
  include_once "UiLog/uilog_POST.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

