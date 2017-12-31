<?php
$conn = database_connect();

// Set the JWT to null
setcookie("token", null, 0, "/v1/", "https://ide.c9.io", true, true);

// Set the xsrf token to null
setcookie("xsrf", null, 0, "/v1/", "https://ide.c9.io", true, false);

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>