<?php
$conn = database_connect();
$token = getAuthToken($conn);

database_delete($conn, "DELETE FROM auth_tokens WHERE token=?", "s", array($token));

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>