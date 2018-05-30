<?php

$conn = database_connect();

$creds = retrieveUserData($conn);

$studentId = $creds[0];
    
$user = database_get_row($conn, "SELECT id FROM users WHERE login_id=?", "s", $studentId);

if($user == null){
    echoError($conn, 404, "UserNotFound");
}
    
$userid = $user["id"];

$ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);

// Check brute force before looking at the user data
$bruteStatusOK = getBruteStatus($conn, $userid);

if($bruteStatusOK){
    //Authenticate the user
    if(authenticate($conn, $creds)){
        
        if(count($creds) != 2){
            echoError($conn, 400, "MalformedBody");
        }
        $loginId = $creds[0];
        
        $user = database_get_row($conn, "SELECT id, login_id, last_login IS NULL as 'changepass', privilege FROM users WHERE login_id=?", "s", $loginId);
        if($user == null){
            echoError($conn, 404, "UserNotFound");
        }
        
        // Get the user's privilege
        $privilege = $user["privilege"];
        
        $userid = $user["id"];
        $token = generateAuthToken($userid, $privilege);
        
        // Update the last_login field
        database_execute($conn, "UPDATE users SET last_login=NOW() WHERE id=?", "s", $userid);
        
        database_insert($conn, "INSERT INTO log_user_logins (user_id, ip_address) values (?,?)", "ss", array($userid, $ip));
        
        // The user logged in successfully, so delete all of the previous login attempts
        database_delete($conn, "DELETE FROM login_attempts WHERE user_id=?", "s", $userid);
        
        include_once "./NavBar/navbar_GET.php";
        $navbar = getNavbarItems($conn, $token);
    
        echoSuccess($conn, array(
            "redirect" => $navbar[0],
            "userId" => $userid,
            "loginId" => $user["login_id"],
            "mustChangePass" => $user["changepass"],
            "messageCode" => "UserAuthenticated",
        ));
    } else {

        // Insert the login attempt into the database 
        database_insert($conn, "INSERT INTO login_attempts (user_id, ip_address) values (?,?)", "ss", array($userid, $ip));
        
        echoError($conn, 401, "AuthenticationFailed", "AuthPost");
    }
} else {
    // Too many attempts: login attempt denied.
    echoError($conn, 403, "AuthenticationDenied");
}

function retrieveUserData($conn){
    $headers = apache_request_headers();
    
    $base = $headers["authorization"];
    
    $creds = explode(':' , base64_decode(substr($base, 6)));
    
    return $creds;
}

//Authenticate with credentials studentid/password
function authenticate($conn, $creds){
    
    $studentId = $creds[0];
    $password = $creds[1];
    
    $user = database_get_row($conn, "SELECT passwordhash FROM users WHERE login_id=?", "s", $studentId);
    
    if($user != null){
        return password_verify($password, $user["passwordhash"]);
    }
    
    return false;
}

function getBruteStatus($conn, $userid){
    
    $attempts = database_get_all($conn, "SELECT UNIX_TIMESTAMP(attempt_at) as loginAttemptTime FROM login_attempts WHERE user_id=? ORDER BY attempt_at DESC LIMIT 5", "s", $userid);
    
    if(count($attempts) < 5)
        return true;
        
    $intervalBetweenAttempts = $attempts[0]["loginAttemptTime"] - $attempts[4]["loginAttemptTime"];
    $now = time();
    $latestAttempt = $attempts[0]["loginAttemptTime"];
    $waitTime = $now - $latestAttempt;
    
    // Check if:
    //- First and most recent attempt are at least 1 minute from each other OR
    //- The latest attempt was at least 2 minutes ago
    if($intervalBetweenAttempts >= $GLOBALS["NEXCHANGE_BRUTE_LOGIN_INTERVAL"] || $waitTime >= $GLOBALS["NEXCHANGE_BRUTE_LOGIN_WAIT"])
        return true;
    
    return false;
}

?>