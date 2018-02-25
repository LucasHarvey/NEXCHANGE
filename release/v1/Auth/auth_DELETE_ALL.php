<?php
$conn = database_connect();

// Do the authorization in this file to get the most recent token ($JWT)
$authed = authorized($conn);
$JWT = null;
$userId = null;
    
if($authed[0] === true){ //Is authorized??
    // Get the user ID and privilege from the old token
    $userInfo = retrieveUserInfo();
    $userId = $userInfo[0];
    // Generate a new JWT and xsrfToken
    $JWT = generateAuthToken($userInfo[0], $userInfo[1]);
} elseif($authed[1] != null && isTokenExpired($authed[1])){ //was the token once valid
    echoError($conn, 401, "AuthenticationExpired");
} else {
    echoError($conn, 401, "AuthorizationFailed");
}

// Get the iat time of the newest token
$latestTokenIAT = getIATFromToken($JWT);

database_update($conn, "UPDATE users SET most_recent_token_IAT=? WHERE id=?", "is", array($latestTokenIAT, $userId));

echoSuccess($conn, array(
    "messageCode" => "UserUnauthenticated"
));
?>