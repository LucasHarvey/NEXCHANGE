<?php
$conn = database_connect();

$headers = apache_request_headers();

$base = $headers["authorization"];

$creds = explode(':' , base64_decode(substr($base, 6)));

$code = $creds[0];

if($code == null){
    echoError($conn, 400, "KeyNotFound");
}

$password = $creds[1];

$user = database_get_row($dbh, "SELECT id FROM users WHERE passresetcode=? AND privilege='USER' AND DATE_ADD(passresetcreated, INTERVAL 15 MINUTE) < NOW()", "s", $code);

if($user != null){
    
    $value = password_hash($password, PASSWORD_BCRYPT);
    $insertParams = array($value, $user["id"], $code);
    
    database_update($dbh, "UPDATE password=? FROM users WHERE id=? AND passresetcode=? LIMIT 1", $insertParams);
    echoSuccess($conn, array(
        "messageCode" => "PasswordReset"
    ));
}


echoError($conn, 401, "AuthenticationFailed");

?>