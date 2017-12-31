<?php

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function base64url_decode($data) { 
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
}
    
function generateAuthToken($conn, $userid){
    
    // generate a proper private key...
    $secret = "super_secure_private_key";

    $header = base64url_encode(json_encode([
        "alg" => "HS256",
        "typ" => "JWT"
    ]));

    $payload = base64url_encode(json_encode([
        "sub" => $userid,
        "iat" => time(),
        "exp" => time() + (15*60)
        // Add code for admin ex: $payload["admin"] = true;
    ]));

    $signature = base64url_encode(hash_hmac("sha256", $header . "." . $payload, $secret, true));

    $token = $header . "." . $payload . "." . $signature;
    
    return $token;

}

function getAuthToken(){
    $headers = apache_request_headers();
    
    if(!in_array("authorization", array_keys($headers))){
        return null;
    }

    $base = $headers["authorization"];
    if(empty($base)){
        return null;
    }
    
    return base64_decode(substr($base, 6));
    
    // Change the substring
}

function authorized($conn){
    $token = getAuthToken();
    if($token == null){
        return array(false, null);
    }
    
    // Verify that the token is valid
    $result = database_get_row($conn, $queryStr, "s", array($token));
    return array(
        $result != null, $token
    );
}

function tokenForUser($conn, $userId){
    $token = getAuthToken();
    if($token == null){
        return false;
    }
    
    // cannot be used anymore
    $queryStr = "SELECT * FROM auth_tokens WHERE token=? AND expires_on >= NOW() AND user_id=?";
    $result = database_get_row($conn, $queryStr, "ss", array($token, $userId));
    return ($result != null);
}

function getUserFromToken($conn){
    $token = getAuthToken();
    if($token == null){
        return null;
    }
    
    // Get the user info from inside the token
    $queryStr = "SELECT user_id FROM auth_tokens WHERE token=? AND expires_on >= NOW()";
    $result = database_get_row($conn, $queryStr, "s", array($token));

    if($result != null){
        return $result["user_id"];
    }
    return null;
}

function isTokenExpired($conn, $token){
    
    // Check in the JWT if it is expired
    if($token == null){
        return true;
    }
    $queryStr = "SELECT expires_on <= NOW() FROM auth_tokens WHERE token=?";
    $result = database_get_row($conn, $queryStr, "s", array($token));
    return ($result != null);
}

function refreshUserToken($conn){
    
    //update the JWT and send it back in each response
    $token = getAuthToken();
    if($token == null){
        return false;
    }
    
    $queryStr = "UPDATE auth_tokens SET expires_on = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE token=?";
    return database_update($conn, $queryStr, "s", array($token));
}

function getUserPrivilege($conn, $userId){
    if($userId == null) return null;
    
    $queryStr = "SELECT privilege FROM users WHERE id=? LIMIT 1";
    $row = database_get_row($conn, $queryStr, "s", array($userId));
    if($row != null){
        return $row["privilege"];
    }
    return null;
}
?>