<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

$offset =  array_key_exists("page", $_GET) ? $_GET['page'] : 0;

$allowedProps = array("teacherFullName", "courseName", "courseNumber", "section", "semester", "courseId", "id", "page", "section");
$changesKeysRemap = array("teacherFullName" => "teacher_fullname", "courseName" => "course_name", "section" => NULL, "courseNumber" => "course_number", "courseId" => "id", "page"=>NULL);
$columnWhereClause = array("teacher_fullname" => "like", "course_name" => "like", "course_number" => "like", "semester" => "like", "id" => "=");

$whereStmt = generateWhereStatement($conn, $allowedProps, $changesKeysRemap, $columnWhereClause, $_GET);

$insertTypes = "";
$insertVals = array();
if(!empty($whereStmt[1])){
    foreach ($whereStmt[1] as $k=>$value) {
        if(substr($k, 0, strlen("section")) === "section"){
            $insertTypes = $insertTypes . "s";
            array_push($insertVals, $value);
        }elseif($k == "id"){
            $insertTypes = $insertTypes . "s";
            array_push($insertVals, $value);
        }else{
            $insertTypes = $insertTypes . "s";
            array_push($insertVals, '%'.$value.'%');
        }
    }
}

$notesCount = "(SELECT count(*) FROM notes WHERE course_id = c.id) as notesAuthored";
$selectQuery = "SELECT id, teacher_fullname as teacherFullName, course_name as courseName, course_number as courseNumber, section, semester, created, $notesCount FROM courses c".$whereStmt[0];
$selectQuery = $selectQuery. " LIMIT ".$GLOBALS['PAGE_SIZES']." OFFSET ". ($offset * $GLOBALS['PAGE_SIZES']);

$courses = database_get_all($conn, $selectQuery, $insertTypes, $insertVals);

//TODO: Must filter by section. Must be done in PHP. Could be done in MySQL but lets not complicate things.

if(in_array("id", array_keys($whereStmt[1]))){
    if(count($courses) == 0){
        echoError($conn, 404, "CourseNotFound");
    }else{
        echoSuccess($conn, array("course" => $courses[0]));
    }
}

echoSuccess($conn, array("courses" => $courses));

?>
