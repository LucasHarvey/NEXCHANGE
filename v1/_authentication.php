<?php

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64url_decode($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
}
    
function generateAuthToken($userid, $privilege){
    
    // TODO: generate a proper private key...
    $secret = "super_secure_private_key";

    $header = base64url_encode(json_encode([
        "alg" => "HS256",
        "typ" => "JWT"
    ]));
    
    // Create an expiry time
    $expiry = time() + (15*60);
    
    // Create an xsrf token
    $xsrf = uniqid();
    
    // The xsrfToken is used to prevent CSRF (Cross-Site Request Forgery)
    $payload = base64url_encode(json_encode([
        "sub" => $userid,
        "iat" => time(),
        "exp" => $expiry,
        "privilege" => $privilege,
        "xsrfToken" => $xsrf
    ]));

    $signature = base64url_encode(hash_hmac("sha256", $header . "." . $payload, $secret, true));

    $token = $header . "." . $payload . "." . $signature;
    
    // Set the cookie for JWT
    // TODO: change the fourth and fifth parameters once uploaded to server
    // Argument 3: The cookie will expire when the web browser closes
    // Arguments 6 and 7 are Secure and HTTPOnly respectively
    // setrawcookie() because the token pieces are already url encoded

    setrawcookie("token", $token, 0, "/v1/", "https://ide.c9.io", true, true);
    
    // Set the cookie for xsrf token
    // HTTPOnly must be false to access the token on the client side
    setcookie("xsrf", $xsrf, 0, "/v1/", "https://ide.c9.io", true, false);

}

function getAuthToken(){
    
    if(!isset($_COOKIE["token"]))
        return null;

    // $token is the JWT
    $token = $_COOKIE["token"];
    
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
    
    $tokenPieces = $token.explode(".");
    
    $header = $tokenPieces[0];
    $payload = $tokenPieces[1];
    
    // TODO: generate a proper private key...
    $secret = "super_secure_private_key";
    
    $signature = base64url_encode(hash_hmac("sha256", $header . "." . $payload, $secret, true));
    
    // Check if the token signature and the new signature are the same
    if($tokenPieces[2] !== $signature)
        return array(false, null);
    
    // Decode the token
    $decTokenPieces = decodeToken($token);
    
    /* Defend against CSRF */
    
    // Get the xsrf token from the headers
    $headers = apache_request_headers();
    
    if(!in_array("X-CSRFToken", array_keys($headers)))
        return array(false, null);
    
    $xsrf = $headers["X-CSRFToken"];
    
    // Check that $xsrf is the same as the xsrfToken inside the payload
    if($xsrf !== $decTokenPieces[1]["xsrfToken"])
        return array(false, null);
        
    // Check that the token is not expired
    if($decTokenPieces[1]["exp"] < time())
        return array(false, $token);
    
    return array(
       true, $token
    );
}

/* Is this function used anywhere...? becomes obsolete with JWT

function tokenForUser($userId){
    $token = getAuthToken();
    
    if($token == null){
        return false;
    }
    
    // Explode the token into: header, payload and signature
    $tokenPieces = $base.explode(".");
    
    // Decode the pieces 
    $tokenPieces = array_map(function($x) {
            return base64url_decode($x);
        }, $tokenPieces);
    
    
    // cannot be used anymore
    $queryStr = "SELECT * FROM auth_tokens WHERE token=? AND expires_on >= NOW() AND user_id=?";
    $result = database_get_row($conn, $queryStr, "ss", array($token, $userId));
    return ($result != null);
}

*/

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
    if($payload["exp"] < time())
        return true;
    
    return false;
}

function refreshUserToken(){
    
    $token = getAuthToken();
    
    if($token == null){
        return false;
    }
    
    $decTokenPieces = decodeToken($token);
    
    $header = $decTokenPieces[0];
    $payload = $decTokenPieces[1];
    
    // Change the expiry date by adding 15 minutes
    $payload["exp"] = time() + (15*60);
    
    // Create a new xsrf token
    $xsrf = uniqid();
    
    $payload["xsrfToken"] = $xsrf;
    
    /* Reconstruct the token with the new expiry date */
    
    // TODO: generate a proper private key...
    $secret = "super_secure_private_key";

    $header = base64url_encode(json_encode($header));
    
    $payload = base64url_encode(json_encode($payload));
    
    $signature = base64url_encode(hash_hmac("sha256", $header . "." . $payload, $secret, true));

    $token = $header . "." . $payload . "." . $signature;
    
    // Set the cookie for JWT
    // TODO: change the fourth and fifth parameters once uploaded to server
    // Argument 3: The cookie will expire when the web browser closes
    // Arguments 6 and 7 are Secure and HTTPOnly respectively
    // setrawcookie() because the token pieces are already url encoded

    setrawcookie("token", $token, 0, "/v1/", "https://ide.c9.io", true, true);
    
    // Set the cookie for xsrf token
    // HTTPOnly must be false to access the token on the client side
    setcookie("xsrf", $xsrf, 0, "/v1/", "https://ide.c9.io", true, false);
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
    $encTokenPieces = $token.explode(".");
    
    // Get the signature
    $signature = $encTokenPieces[2];
    
    // Slice the signature off
    $encHeadPay = array_slice($encTokenPieces, 0, 2);
    
    // base64url_decode and json_decode the header and payload
    $decHeadPay = array_map(function($x){
        base64url_decode(json_decode(($x)));
    }, $encHeadPay);
    
    // Add the signature to the end of the decoded token pieces
    $decTokenPieces = array_push($decHeadPay, $signature);
    
    return $decTokenPieces;
}

?>