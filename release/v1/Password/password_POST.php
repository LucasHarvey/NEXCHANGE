<?php
$conn = database_connect();

if(!array_key_exists("studentId", $_GET)){
    echoError($conn, 400, "MissingArgumentStudentId");
}

if(!array_key_exists("email", $_GET)){
    echoError($conn, 400, "MissingArgumentEmail");
}

$studentId = $_GET["studentId"];
$email = $_GET["email"];

$select = "SELECT id,email FROM users WHERE login_id = ? AND email = ? AND privilege = 'USER' LIMIT 1";

$user = database_get_row($conn, $select, "ss", array($studentId, $email));

$length = 40;
$true = true;
$token = bin2hex(openssl_random_pseudo_bytes($length, $true));

$update = "UPDATE users SET passresetcode=?, passresetcreated=NOW() WHERE id=? LIMIT 1";
database_update($conn, $update, "ss", $token, $user["id"]);

include_once("../_EMAIL_TASKS/reset_password_task.php");
reset_password_email($conn, $user['id'], $user['email'], $token);

echoSuccess($conn, "PasswordResetRequested");

?>