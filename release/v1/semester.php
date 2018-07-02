<?php

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "Semester/semester_GET.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "POST"){
  include_once "Semester/semester_POST.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>