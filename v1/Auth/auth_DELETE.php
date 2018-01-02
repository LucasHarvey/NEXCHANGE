<?php
$conn = database_connect();

// Delete the JWT
setcookie("authToken", "", time() - 3600);

// Delete the xsrf token
setcookie("xsrfToken", "", time() - 3600);

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>