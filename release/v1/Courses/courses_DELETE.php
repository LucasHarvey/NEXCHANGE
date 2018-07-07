<?php

$conn = database_connect();

$userId = getUserFromToken();
if($userId == null)
    echoError($conn, 403, "AuthorizationFailed");

if(getUserPrivilege() != "ADMIN")
    echoError($conn, 403, "AuthorizationFailed");

requiredParams($conn, $_GET, array("courseId", "password"));

$course_id = $_GET["courseId"];
$password = $_GET["password"];
$password = base64_decode($password);

$user = database_get_row($conn, "SELECT passwordhash FROM users WHERE id=?", "s", $userId);
if(!password_verify($password, $user["passwordhash"])){
    echoError($conn, 401, "AuthenticationFailed", "CoursesDelete");
}

if($course_id == "")
    echoError($conn, 400, "MissingArgumentCourseId");

$courseExists = database_get_row($conn, "SELECT id, course_name as courseName, section FROM courses WHERE id=?", "s", $course_id);
if($courseExists == null){
    echoError($conn, 404, "CourseNotFound");
}

database_delete($conn, "DELETE FROM courses WHERE id=?", "s", $courseExists['id']);

echoSuccess($conn, array(
    "messageCode" => "CourseDeleted",
    "course" => $courseExists
));

?>