<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_JSON, array("studentId", "coursesId", "role", "expiryDate"));

$student_id = $_JSON["studentId"];
$courses_id = $_JSON["coursesId"];
$role = strtoupper($_JSON["role"]);
$expires = $_JSON["expiryDate"];

if($student_id == "")
    echoError($conn, 400, "MissingArgumentStudentId");
validateId($conn, $student_id);

if(empty($courses_id))
    echoError($conn, 400, "MissingArgumentCourse");
    
if($role == "")
    echoError($conn, 400, "MissingArgumentRole");
// Verify that the role is valid
$ROLES = array("STUDENT", "NOTETAKER");
if (!in_array($role, $ROLES)){
    echoError($conn, 404, "RoleNotFound");
}

if($expires == "")
    echoError($conn, 400, "MissingArgumentExpiryDate");
if(strlen($expires) > 10)
    echoError($conn, 400, "ExpiryDateNotValid");
if(strtotime($expires) < time())
    echoError($conn, 400, "PastSemester");

$user = database_get_row($conn, "SELECT * FROM users WHERE login_id=?", "s", $student_id);
if($user == null){
    echoError($conn, 404, "StudentNotFound" );
}

$accessGrantedCourses = array();
$previousAccess = array();
foreach($courses_id as $courseId){
    
    if(!database_contains($conn, "courses", $courseId)){
        echoError($conn, 404, "CourseNotFound");
    }
    
    $course = database_get_row($conn, "SELECT course_name as courseName, course_number as courseNumber, section FROM courses WHERE id=?", "s", $courseId);
  
    // Verify that the access doesn't already exist
    $hasAccess = database_get_row($conn, "SELECT role, course_name as courseName, course_number as courseNumber, section FROM user_access ua".
                                            " INNER JOIN courses c ON ua.course_id=c.id WHERE ua.user_id=? AND ua.course_id=?", "ss", array($user["id"], $courseId));
    
    // If the user already has access, add the course to the $previousAccess array
    if($hasAccess){
        array_push($previousAccess, $hasAccess);
        // Skip to the next course
        continue;
    }
    
    $insertParams = array($user["id"], $courseId, $role, $expires);
    database_insert($conn, "INSERT INTO user_access (user_id, course_id, role, expires_on) VALUES (?, ?, ?, ?)", "ssss", $insertParams);
    
    array_push($accessGrantedCourses, $course);
}

echoSuccess($conn, array(
    "userId" => $user["id"],
    "courses" => $accessGrantedCourses,
    "previousAccess" => $previousAccess,
    "role" => $role
), 201);

function validateId($conn, $userId){
    if(strlen($userId) != 7 || !is_numeric($userId)){
        echoError($conn, 400, "UserIdNotValid");
    }
    return;
}
?>