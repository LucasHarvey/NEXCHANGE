<?php

include_once "_generics.php";

if ($_SERVER["REQUEST_METHOD"] == "GET"){
  include_once "NoteFiles/noteFiles_GET.php";
}else{
  echoError(null, 404, "UnknownResourceMethod");
}

?>
  

