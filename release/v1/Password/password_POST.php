<?php
$conn = database_connect();

requiredParams($conn, $_JSON, array("studentId", "email"));

$studentId = $_JSON["studentId"];
$email = $_JSON["email"];

if($studentId == "")
    echoError($conn, 400, "MissingArgumentStudentId");
validateId($conn, $studentId);
    
if($email == "")
    echoError($conn, 400, "MissingArgumentEmail");
validateEmail($conn, $email);

$select = "SELECT id,email FROM users WHERE login_id = ? AND email = ? AND privilege = 'USER' LIMIT 1";

$user = database_get_row($conn, $select, "ss", array($studentId, $email));

if($user == null){
    echoError($conn, 404, "PasswordResetFailed");
}

$length = 20;
$true = true;
$token = bin2hex(openssl_random_pseudo_bytes($length, $true));

$update = "UPDATE users SET passresetcode=?, passresetcreated=NOW() WHERE id=? LIMIT 1";
database_update($conn, $update, "ss", array($token, $user["id"]));

include_once("./_EMAIL_TASKS/reset_password_task.php");
reset_password_email($conn, $user['id'], $user['email'], $token);

echoSuccess($conn, array("messageCode" => "PasswordResetRequested"));

function validateId($conn, $userId){
    if(strlen($userId) != 7 || !is_numeric($userId)){
        echoError($conn, 400, "UserIdNotValid");
    }
    return;
}

function validateEmail($conn, $email){
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echoError($conn, 400, "EmailNotValid");
    }
    return;
}

?>