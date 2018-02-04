<?php
$conn = database_connect();

if(!array_key_exists("studentId", $_JSON)){
    echoError($conn, 400, "MissingArgumentStudentId");
}

if(!array_key_exists("email", $_JSON)){
    echoError($conn, 400, "MissingArgumentEmail");
}

$studentId = $_JSON["studentId"];
$email = $_JSON["email"];

$select = "SELECT id,email FROM users WHERE login_id = ? AND email = ? AND privilege = 'USER' LIMIT 1";

$user = database_get_row($conn, $select, "ss", array($studentId, $email));
//TODO; user exists
$length = 20;
$true = true;
$token = bin2hex(openssl_random_pseudo_bytes($length, $true));

$update = "UPDATE users SET passresetcode=?, passresetcreated=NOW() WHERE id=? LIMIT 1";
database_update($conn, $update, "ss", array($token, $user["id"]));

include_once("../_EMAIL_TASKS/reset_password_task.php");
reset_password_email($conn, $user['id'], $user['email'], $token);

echoSuccess($conn, array("messageCode" => "PasswordResetRequested"));

?>