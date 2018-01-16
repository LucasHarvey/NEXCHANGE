<?php
$conn = database_connect();

// Delete the JWT
setcookie("authToken", false, time() - 3600, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN'], true, true);

// Delete the xsrf token
setcookie("xsrfToken", false, time() - 3600, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN'], true, false);

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>