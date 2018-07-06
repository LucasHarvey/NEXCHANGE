<?php

$conn = database_connect();

$userId = getUserFromToken();
if($user_id == null)
    echoError($conn, 403, "AuthorizationFailed");
    
if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_GET, array("studentId", "password"));

$student_id = $_GET["studentId"];
$password = $_GET["password"];
$password = base64_decode($password);

$user = database_get_row($conn, "SELECT passwordhash FROM users WHERE id=?", "s", $userId);
if(!password_verify($password, $user["passwordhash"])){
    echoError($conn, 401, "AuthenticationFailed", "UsersDelete");
}

if($student_id == "")
    echoError($conn, 400, "MissingArgumentStudentId");
validateId($conn, $student_id);

$userExists = database_get_row($conn, "SELECT id FROM users WHERE login_id=?", "s", $student_id);
if($userExists == null){
    echoError($conn, 404, "UserNotFound");
}

database_delete($conn, "DELETE FROM users WHERE id=?", "s", $userExists['id']);

echoSuccess($conn, array(
    "messageCode" => "UserDeleted",
    "userId" => $userExists["id"],
    "studentId" => $student_id
));

function validateId($conn, $userId){
    if(strlen($userId) != 7 || !is_numeric($userId)){
        echoError($conn, 400, "UserIdNotValid");
    }
    return;
}

?>