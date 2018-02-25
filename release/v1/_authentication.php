<?php
function generateAuthToken($userid, $privilege){
    $header = base64_encode(json_encode([
        "alg" => "HS256",
        "typ" => "JWT"
    ]));
    
    // Create an expiry time
    $expiry = time() + $GLOBALS['NEXCHANGE_TOKEN_EXPIRY_MINUTES']*60;
    
    // Create a new xsrf token
    $length = 16; 
    $true = true; 
    $xsrf = bin2hex(openssl_random_pseudo_bytes($length, $true));
    // JSON encode the xsrf token to store it as a cookie
    $xsrf = json_encode($xsrf);
    
    // The xsrfToken is used to prevent CSRF (Cross-Site Request Forgery)
    $payload = base64_encode(json_encode([
        "sub" => $userid,
        "iat" => time(),
        "exp" => $expiry,
        "privilege" => $privilege,
        "xsrfToken" => $xsrf
    ]));
    
    $signature = base64_encode(hash_hmac("sha256", $header . "." . $payload, $GLOBALS['NEXCHANGE_SECRET'], true));
    
    $token = $header . "." . $payload . "." . $signature;
    
    //fourth and fifth parameters once uploaded to server
    // Argument 3: The cookie will expire when the web browser closes
    // Arguments 6 and 7 are Secure and HTTPOnly respectively
    setcookie("authToken", $token, 0, "/", $GLOBALS['NEXCHANGE_DOMAIN'], $GLOBALS['NEXCHANGE_SECURED_SITE'], true);
    
    // Set the cookie for xsrf token
    // HTTPOnly must be false to access the token on the client side
    setcookie("xsrfToken", $xsrf, 0, "/", $GLOBALS['NEXCHANGE_DOMAIN'], $GLOBALS['NEXCHANGE_SECURED_SITE'], false);
    
    return $token;
}

function getAuthToken(){
    
    if(!isset($_COOKIE["authToken"]))
        return null;

    // $token is the JWT
    $token = $_COOKIE["authToken"];
    
    if(empty($token)){
        return null;
    }
    
    return $token;
}

function authorized($conn){
    $token = getAuthToken();
    if(!validateTokenAuthenticity($token))
        return array(false, null);
    
    // Decode the token
    $decTokenPieces = decodeToken($token);
    
    /* Defend against CSRF */
    
    // Get the xsrf token from the headers
    $headers = apache_request_headers();
    
    if(!in_array("x-csrftoken", array_keys($headers)))
        return array(false, null);

    $xsrf = $headers["x-csrftoken"];
    
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
    if(intval($decTokenPieces[1]["exp"]) < time())
        return array(false, $token);

    return array(
       true, $token
    );
}

function validateTokenAuthenticity($token){
    if($token == null || empty($token))
        return false;
    
    $encTokenPieces = explode(".", $token);
    if(count($encTokenPieces) != 3){
        error_log("validateTokenAuthenticity did not receive 3 token pieces... Received: ".$token);
        return false;
    }
    
    $header = $encTokenPieces[0];
    $payload = $encTokenPieces[1];
    
    $signature = base64_encode(hash_hmac("sha256", $header . "." . $payload, $GLOBALS['NEXCHANGE_SECRET'], true));
    
    // Check if the token signature and the new signature are the same
    return ($encTokenPieces[2] !== $signature);
}

function decodeToken($token){
    if(!validateTokenAuthenticity($token))
        return null;
        
    // Explode the token into its three components
    $encTokenPieces = explode(".", $token);
    
    // Get the signature
    $signature = $encTokenPieces[2];
    
    // Slice the signature off
    $encHeadPay = array_slice($encTokenPieces, 0, 2);
    
    $decHead = base64_decode($encHeadPay[0]);
    $decHead = json_decode($decHead, true);
    
    $decPay = base64_decode($encHeadPay[1]);
    $decPay = json_decode($decPay, true);

    // Created the decoded token array
    $decTokenPieces = array($decHead, $decPay, $signature);

    return $decTokenPieces;
}

function getUserFromToken($token){
    $payload = _getTokenPayload($token);
    if($payload != null)
        return $payload['sub'];
    return null;
}

function isTokenExpired($token){
    $payload = _getTokenPayload($token);
    if($payload != null)
        return (intval($payload["exp"])) < time();
    return null;
}

function getUserPrivilege($token){
    $payload = _getTokenPayload($token);
    if($payload != null)
        return $payload['privilege'];
    return null;
}

function _getTokenPayload($token){
    if($token == null)
        $token = getAuthToken();
    if(!validateTokenAuthenticity($token))
        return null;
    
    $decTokenPieces = decodeToken($token);
    
    return $decTokenPieces[1];
}

?>