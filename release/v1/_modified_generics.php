<?php
include_once "_globals.php";
include_once "_overrides.php";

//Error handler helpers
include_once "_errorHandlers.php"; //Should always be first thing.

// Database helper
include_once "_database.php";

//Authentication helpers
include_once "_authentication.php";


function modified_authorized($conn){
    $token = getAuthToken();
    if(!validateTokenAuthenticity($token))
        return array(false, null);
    
    // Decode the token
    $decTokenPieces = decodeToken($token);
    
    // Get the xsrf token from the GET request
    
    if (!isset($_GET['xsrfToken'])) {
        http_response_code(400);
        die();
    }

    $xsrf = $_GET["xsrfToken"];
    
    // Check that $xsrf is the same as the xsrfToken inside the payload
    if($xsrf !== $decTokenPieces[1]["xsrfToken"])
        return array(false, null);
        
    // Check that the user exists
    $user = database_get_row($conn, "SELECT id FROM users WHERE id=?", "s", $decTokenPieces[1]["sub"]);
    if(!$user)
        return array(false, null);
        
    // Check that the token is not older than the IAT date of latest token
    $userId = getUserFromToken($token);
    $expiry = database_get_row($conn, "SELECT most_recent_token_IAT FROM users WHERE id=?", "s", $userId);
    if($expiry["most_recent_token_IAT"]){
        // if iat == expiry, then the token is valid
        if(intval($decTokenPieces[1]["iat"]) < $expiry["most_recent_token_IAT"]){
            // This is not a token expiry error: the token is just not valid anymore
            return array(false, null);
        }
            
    }
    
    // Check that the JWT isn't expired
    if(isTokenExpired($token) == true){
        return array(false, $token); //false = token not valid. $token = token.
    }
    
    return array(
       true, $token
    );
}

$conn = database_connect();
$authed = modified_authorized($conn);

if($authed[0] === true){ //Is authorized??
    // Get the user ID and privilege from the old token
    $userInfo = retrieveUserInfo($authed[1]);
    if($userInfo)
       generateAuthToken($userInfo[0], $userInfo[1]);// Generate a new JWT and xsrfToken
    
    $conn->close();
    return;
} else {
    http_response_code(401);
    die();
}



?>