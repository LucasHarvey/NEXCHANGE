<?php

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "Semester/semester_GET.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "PUT"){
  include_once "Semester/semester_PUT.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>