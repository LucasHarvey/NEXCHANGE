<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN")
    echoError($conn, 403, "AuthorizationFailed");


if(array_key_exists("section", $_GET) && !empty($_GET['section']) && !is_numeric($_GET['section']))
    echoError($conn, 400, "SectionNotValid");

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
$final = array();

#TODO MOVE THIS TO A MySQL FUNCTION. Too many callbacks.
if(array_key_exists("section", $_GET) && !empty($_GET['section'])){
    $_section = intval($_GET['section']);
    foreach($courses as $course){
        $section = $course['section'];
        if(strpos($section, ",") !== false){
            $pieces = explode(",", $section);
            foreach($pieces as $piece){
                if(strpos($piece, "-") !== false){
                    $dashPieces = explode("-", $piece);
                    if($_section >= intval($dashPieces[0]) && $_section <= intval($dashPieces[1])){
                        array_push($final, $course);
                    }
                }else{
                    if(intval($piece) == $_section){
                        array_push($final, $course);
                    }
                }
            }
        }else{
            if(strpos($section, "-") !== false){
                $dashPieces = explode("-", $section);
                if($_section >= intval($dashPieces[0]) && $_section <= intval($dashPieces[1])){
                    array_push($final, $course);
                }
            }else{
                if(intval($section) == $_section){
                    array_push($final, $course);
                }
            }
        }
    }
}else{
    $final = $courses;
}

if(in_array("id", array_keys($whereStmt[1]))){
    if(count($final) == 0){
        echoError($conn, 404, "CourseNotFound");
    }else{
        echoSuccess($conn, array("course" => $final[0]));
    }
}

echoSuccess($conn, array("courses" => $final));

?>
