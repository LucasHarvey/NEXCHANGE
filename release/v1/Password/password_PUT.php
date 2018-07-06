<?php
$PASSWORD_LENGTH = 9;

$conn = database_connect();

$headers = apache_request_headers();

$base = $headers["authorization"];

$creds = explode(':' , base64_decode(substr($base, 6)));

$code = $creds[0];

if($code == null){
    echoError($conn, 400, "KeyNotFound");
}

$password = $creds[1];

$user = database_get_row($conn, "SELECT id FROM users WHERE passresetcode=? AND privilege='USER' AND DATE_ADD(passresetcreated, INTERVAL 15 MINUTE) > NOW()", "s", $code);

if($user != null){
    if(strlen($password) < $PASSWORD_LENGTH){
        echoError($conn, 400, "PasswordTooSmall");
    }
    $value = password_hash($password, PASSWORD_BCRYPT);
    $insertParams = array($value, $user["id"], $code);

    database_update($conn, "UPDATE users SET passwordhash=?,passresetcreated=DATE_SUB(passresetcreated, INTERVAL 15 MINUTE) WHERE id=? AND passresetcode=? LIMIT 1", "sss", $insertParams);
    echoSuccess($conn, array(
        "messageCode" => "PasswordReset"
    ));
}


echoError($conn, 400, "PasswordUpdateLinkFailure", "PasswordPut");

?>
