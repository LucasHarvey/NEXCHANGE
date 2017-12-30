<?php

function generateAuthToken($conn, $userid){
    $length = 16;
    $true = true;
    $token = bin2hex(openssl_random_pseudo_bytes($length, $true));
    
    database_insert($conn, 
        "INSERT INTO auth_tokens (user_id, token) VALUES (?,?)", "ss", array($userid, $token)
    );
    
    return "Basic ".base64_encode($token);
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
}

function authorized($conn){
    $token = getAuthToken();
    if($token == null){
        return array(false, null);
    }
    
    $queryStr = "SELECT * FROM auth_tokens WHERE token=? AND expires_on >= NOW()";
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
    
    $queryStr = "SELECT * FROM auth_tokens WHERE token=? AND expires_on >= NOW() AND user_id=?";
    $result = database_get_row($conn, $queryStr, "ss", array($token, $userId));
    return ($result != null);
}

function getUserFromToken($conn){
    $token = getAuthToken();
    if($token == null){
        return null;
    }
    
    
    $queryStr = "SELECT user_id FROM auth_tokens WHERE token=? AND expires_on >= NOW()";
    $result = database_get_row($conn, $queryStr, "s", array($token));

    if($result != null){
        return $result["user_id"];
    }
    return null;
}

function isTokenExpired($conn, $token){
    if($token == null){
        return true;
    }
    $queryStr = "SELECT expires_on <= NOW() FROM auth_tokens WHERE token=?";
    $result = database_get_row($conn, $queryStr, "s", array($token));
    return ($result != null);
}

function refreshUserToken($conn){
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