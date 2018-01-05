<?php

$conn = database_connect();

$userId = getUserFromToken();
if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_JSON, array("firstName", "lastName", "studentId", "email"));

$student_id = $_JSON["studentId"];
$first_name = $_JSON["firstName"];
$last_name = $_JSON["lastName"];
$email = $_JSON['email'];
$password = generateRandomPassword();

if(!empty($email)){
    validateEmail($email);
}
validateId($student_id);
validateName($first_name . ' ' . $last_name);

$userExists = database_get_row($conn, "SELECT id FROM users WHERE login_id=?", "s", $student_id);
if($userExists != null){
    echoError($conn, 409, "UserAlreadyExists");
}

$passwordhash = password_hash($password, PASSWORD_BCRYPT);


$insertParams = array($student_id, $first_name, $last_name, $email, $passwordhash);

database_insert($conn, "INSERT INTO users (login_id, first_name, last_name, email, passwordhash) VALUES (?,?,?,?,?)", "sssss", $insertParams);

$email_password_email = $email;
$email_password_token = $password;
include_once("./_EMAIL_TASKS/temporary_password_task.php");

echoSuccess($conn, array(
    "loginId" => $student_id,
    "firstName" => $first_name,
    "lastName" => $last_name,
    "email" => $email,
    "password" => $password,
    "messageCode" => "UserRegistered"
), 201);

function generateRandomPassword() {
    $PASSWORD_LENGTH = 9;
    //IF CHANGING PASSWORD LENGTH, CHANGE IN:
    //users_PUT.php, users.js, settings.js

    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$_-+=';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $PASSWORD_LENGTH; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function validateEmail($email){
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echoError($conn, 400, "EmailNotValid");
    }
    return;
}

function validateId($userId){
    if(strlen($userId) != 7 || !is_numeric($userId)){
        echoError($conn, 400, "UserIdNotValid");
    }
    return;
}
function validateName($name){
    if(preg_match("[0-9]", $name) === 1){
        echoError($conn, 400, "UserNameNotValid");
    }
    return;
}

?>