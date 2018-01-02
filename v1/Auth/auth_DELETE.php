<?php
$conn = database_connect();

// Delete the JWT
setcookie("authToken", "", time() - 3600, "/", "https://ide.c9.io", true, true);

// Delete the xsrf token
setcookie("xsrfToken", "", time() - 3600, "/", "https://ide.c9.io", true, false);

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>