<?php

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64url_decode($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
}
    
function generateAuthToken($userid, $admin = false){
    
    // TODO: generate a proper private key...
    $secret = "super_secure_private_key";

    $header = base64url_encode(json_encode([
        "alg" => "HS256",
        "typ" => "JWT"
    ]));
    
    // Create an expiry time
    $expiry = time() + (15*60);
    
    // The xsrfToken is used to prevent CSRF (Cross-Site Request Forgery)
    $payload = base64url_encode(json_encode([
        "sub" => $userid,
        "iat" => time(),
        "exp" => $expiry,
        "admin" => $admin,
        "xsrfToken" => uniqid()
    ]));

    $signature = base64url_encode(hash_hmac("sha256", $header . "." . $payload, $secret, true));

    $token = $header . "." . $payload . "." . $signature;
    
    // Set the cookie in the response header
    // TODO: change the fourth and fifth parameters once uploaded to server
    // Arguments 6 and 7 are Secure and HTTPOnly respectively
    setcookie("token", $token, $expiry, "/v1/", "https://ide.c9.io", true, true);

}

function getAuthToken(){
    $headers = apache_request_headers();
    
    if(empty($_COOKIE))
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
        
    // Decode the pieces 
    $tokenPieces = array_map(function($x) {
        return base64url_decode($x);
    }, $tokenPieces);
    
    // $xsrf is the token used to protect against CSRF
    $xsrf = $_COOKIE["xsrfToken"];
    
    // Check that $xsrf is the same as the xsrfToken inside the payload
    if($xsrf !== $tokenPieces[1]["xsrfToken"])
        return array(false, null);
        
    // Check that the token is not expired
    if($tokenPieces[1]["exp"] < time())
        return array(false, null);
    
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
    
    $tokenPieces = $token.explode(".");
    
    // Decode the pieces 
    $tokenPieces = array_map(function($x) {
        return base64url_decode($x);
    }, $tokenPieces);
    
    // Verify that the token is valid
    $payload = $tokenPieces[1];
    
    $userId = $payload["sub"];
    
    // Get the id of the user
    $id = database_get_row($conn, "SELECT id FROM users WHERE login_id=?", "s", $userId);
    
    return $id;
}

function isTokenExpired($token){
    
    if($token == null){
        return true;
    }
    
    $tokenPieces = $token.explode(".");
    
    // Decode the pieces 
    $tokenPieces = array_map(function($x) {
        return base64url_decode($x);
    }, $tokenPieces);
    
    $payload = $tokenPieces[1];
    
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
    
    // Decode the pieces 
    $tokenPieces = array_map(function($x) {
        return base64url_decode($x);
    }, $tokenPieces);
    
    $payload = $tokenPieces[1];
    
    // Change the expiry date by adding 15 minutes
    $payload["exp"] = time() + (15*60);
    
    /* Reconstruct the token with the new expiry date */
    
    // TODO: generate a proper private key...
    $secret = "super_secure_private_key";

    $header = base64url_encode($tokenPieces[0]);
    
    $payload = base64url_encode(json_encode($payload));
    
    $signature = base64url_encode(hash_hmac("sha256", $header . "." . $payload, $secret, true));

    $token = $header . "." . $payload . "." . $signature;
    
    // Set the cookie in the response header
    // TODO: change the fourth and fifth parameters once uploaded to server
    // Arguments 6 and 7 are Secure and HTTPOnly respectively
    setcookie("token", $token, $expiry, "/v1/", "https://ide.c9.io", true, true);
}

function getUserPrivilege(){
    
    $token = getAuthToken();
    
    if($token == null){
        return false;
    }
    
    // Decode the pieces 
    $tokenPieces = array_map(function($x) {
        return base64url_decode($x);
    }, $tokenPieces);
    
    $payload = $tokenPieces[1];
    
    $isAdmin = $payload["admin"];
    
    return $isAdmin;
}

?>