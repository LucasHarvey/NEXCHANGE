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
    
    // Set the cookie for JWT
    // TODO: change the fourth and fifth parameters once uploaded to server
    // Argument 3: The cookie will expire when the web browser closes
    // Arguments 6 and 7 are Secure and HTTPOnly respectively

    setcookie("authToken", $token, 0, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN'], true, true);
    
    
    // Set the cookie for xsrf token
    // HTTPOnly must be false to access the token on the client side
    setcookie("xsrfToken", $xsrf, 0, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN'], true, false);
    
    return array($token, $xsrf);

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

function authorized(){
    $token = getAuthToken();
    
    if($token == null){
        return array(false, null);
    }
    
    $encTokenPieces = explode(".", $token);
    
    $header = $encTokenPieces[0];
    $payload = $encTokenPieces[1];
    
    $signature = base64_encode(hash_hmac("sha256", $header . "." . $payload, $GLOBALS['NEXCHANGE_SECRET'], true));
    
    // Check if the token signature and the new signature are the same
    if($encTokenPieces[2] !== $signature)
        return array(false, null);
    
    // Decode the token
    $decTokenPieces = decodeToken($token);
    
    
    /* Defend against CSRF */
    
    // Get the xsrf token from the headers
    $headers = apache_request_headers();
    
    if(!in_array("x-csrftoken", array_keys(apache_request_headers())))
        return array(false, null);
    
    
    $xsrf = $headers["x-csrftoken"];
    
    // Check that $xsrf is the same as the xsrfToken inside the payload
    if($xsrf !== $decTokenPieces[1]["xsrfToken"])
        return array(false, null);
    
    
        
    // Check that the token is not expired
    if(intval($decTokenPieces[1]["exp"]) < time())
        return array(false, $token);
    

    return array(
       true, $token
    );
}


function getUserFromToken($conn){
    $token = getAuthToken();
    
    if($token == null){
        return null;
    }
    
    $decTokenPieces = decodeToken($token);
    
    // Verify that the token is valid
    $payload = $decTokenPieces[1];
    
    $userId = $payload["sub"];
    
    // Get the id of the user
    $id = database_get_row($conn, "SELECT id FROM users WHERE login_id=?", "s", $userId);
    
    return $id;
}

function isTokenExpired($token){
    
    if($token == null){
        return true;
    }
    
    $decTokenPieces = decodeToken($token);
    
    $payload = $decTokenPieces[1];
    
     // Check that the token is not expired
    if(intval($payload["exp"]) < time())
        return true;
    
    return false;
}

function getUserPrivilege(){
    
    $token = getAuthToken();
    
    if($token == null){
        return false;
    }
    
    $decTokenPieces = decodeToken($token);
    
    $payload = $decTokenPieces[1];
    
    $privilege = $payload["privilege"];
    
    return $privilege;
}

function decodeToken($token){
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

function retrieveUserInfo(){
    $token = getAuthToken();
    $decTokenPieces = decodeToken($token);
    $payload = $decTokenPieces[1];
    return array(
        "user_id" => $payload["sub"],
        "privilege" => $payload["privilege"]
        );
}

?>