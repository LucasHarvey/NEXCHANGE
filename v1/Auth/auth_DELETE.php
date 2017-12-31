<?php
$conn = database_connect();

// Set the cookie to null
setcookie("token", null, 0, "/v1/", "https://ide.c9.io", true, true);

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>