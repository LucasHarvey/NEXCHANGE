<?php

//Authenticate with credentials studentid/password
function authenticate($dbh){
    $headers = apache_request_headers();
    
    $base = $headers["authorization"];
    
    $creds = explode(':' , base64_decode(substr($base, 6)));
    
    $studentId = $creds[0];
    $password = $creds[1];
    
    $user = database_get_row($dbh, "SELECT passwordhash FROM users WHERE login_id=?", "s", $studentId);
    
    if($user != null){
        return password_verify($password, $user["passwordhash"]);
    }
    
    return false;
}

$conn = database_connect();

//Authenticate the user
if(authenticate($conn)){
    $headers = apache_request_headers();
    $base = $headers["authorization"];
    $creds = explode(':' , base64_decode(substr($base, 6)));
    if(count($creds) != 2){
        echoError($conn, 400, "MalformedBody");
    }
    $loginId = $creds[0];
    
    $user = database_get_row($conn, "SELECT id, login_id, last_login IS NULL as 'changepass', privilege FROM users WHERE login_id=?", "s", $loginId);
    if($user == null){
        echoError($conn, 404, "UserNotFound");
    }
    
    // Determine if the user is an admin
    $isAdmin = false;
    if($user['privilege'] == "ADMIN") $isAdmin = true;
    
    $userid = $user["id"];
    $token = generateAuthToken($conn, $userid, $isAdmin);
    
    include_once "./NavBar/navbar_GET.php";
    $navbar = getNavbarItems($conn, $userid);

    echoSuccess($conn, array(
        "redirect" => $navbar[0],
        "token" => $token,
        "userId" => $userid,
        "loginId" => $user["login_id"],
        "mustChangePass" => $user["changepass"],
        "messageCode" => "UserAuthenticated",
    ));
}
echoError($conn, 401, "AuthenticationFailed");

?>