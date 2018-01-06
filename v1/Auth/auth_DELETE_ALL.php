<?php
$conn = database_connect();

// Get the user's ID
$userId = getUserFromToken();

// Get the iat time of the newest token
$latestTokenIAT = retrieveIAT();

database_update($conn, "UPDATE users SET most_recent_token_IAT=? WHERE id=?", "is", array($latestTokenIAT, $userId));

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>