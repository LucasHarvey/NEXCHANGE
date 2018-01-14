<?php
$conn = database_connect();
$token = getAuthToken($conn);
$user_id = getUserFromToken($conn);

database_delete($conn, "DELETE FROM auth_tokens WHERE token!=? AND user_id=?", "ss", array($token, $user_id));

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>