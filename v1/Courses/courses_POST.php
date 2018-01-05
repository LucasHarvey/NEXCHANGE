<?php
$conn = database_connect();

$userId = getUserFromToken();

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

requiredParams($conn, $_JSON, array("teacherFullName", "courseName", "courseNumber", "section", "semester"));

$teacher_fullname = $_JSON["teacherFullName"];
$course_name = $_JSON["courseName"];
$course_number = $_JSON["courseNumber"];
$section = $_JSON["section"];
$semester = $_JSON["semester"];

$insertParams = array($teacher_fullname, $course_name, $course_number, $section, $semester);

$selectQuery = "SELECT id FROM courses WHERE teacher_fullname=? AND  course_name=? AND course_number=? AND section=? AND semester=?";

$courseExists = database_get_all($conn, $selectQuery, "sssis", $insertParams);

if($courseExists != null){
    echoError($conn, 409, "CourseAlreadyExists");
}

// Insert the new course into the database

database_insert($conn, "INSERT INTO courses (teacher_fullname, course_name, course_number, section, semester) VALUES (?,?,?,?,?)", "sssis", $insertParams);

echoSuccess($conn, array("messageCode" => "CourseCreated"), 201);


?>