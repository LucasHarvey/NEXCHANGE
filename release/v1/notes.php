<?php

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "Notes/notes_GET.php";
}elseif ($_SERVER["REQUEST_METHOD"] == "POST"){
  include_once "Notes/notes_POST.php";
}
elseif ($_SERVER["REQUEST_METHOD"] == "DELETE"){
  include_once "Notes/notes_DELETE.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

