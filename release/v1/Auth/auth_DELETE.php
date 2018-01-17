<?php
$conn = database_connect();

// Delete the JWT
setcookie("authToken", false, time() - 3600, "/", $GLOBALS['NEXCHANGE_DOMAIN'], $GLOBALS['NEXCHANGE_SECURED_SITE'], true);

// Delete the xsrf token
setcookie("xsrfToken", false, time() - 3600, "/", $GLOBALS['NEXCHANGE_DOMAIN'], $GLOBALS['NEXCHANGE_SECURED_SITE'], false);

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>