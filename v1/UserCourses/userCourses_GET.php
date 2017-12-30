<?php

$conn = database_connect();

$user_id = getUserFromToken($conn);

if(getUserPrivilege($conn, $user_id) == "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed", "ModuleAccessError");
}

$query = "SELECT id, teacher_fullname as teacherFullName, course_name as courseName, course_number as courseNumber, section, semester, role, notifications ".
         "FROM courses c INNER JOIN user_access ua ON c.id = ua.course_id ".
         "WHERE ua.user_id=? AND ua.expires_on >= NOW()";
         
$courses = database_get_all($conn, $query, "s", $user_id);

echoSuccess($conn, array(
    "courses" => $courses
));

?>