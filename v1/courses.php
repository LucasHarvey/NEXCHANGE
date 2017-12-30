<?php
include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "Courses/courses_GET.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "POST"){
  include_once "Courses/courses_POST.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "PUT"){
  include_once "Courses/courses_PUT.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "DELETE"){
  include_once "Courses/courses_DELETE.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

