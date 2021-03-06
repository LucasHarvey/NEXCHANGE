<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

$offset =  array_key_exists("page", $_GET) ? $_GET['page'] : 0;

$allowedProps = array("studentId", "lastName", "courseName", "courseNumber", "page");
$changesKeysRemap = array("studentId" => "login_id", "lastName" => "last_name", "courseName" => "course_name", "courseNumber" => "course_number", "page" => NULL);
$columnWhereClause = array("login_id" => "=", "last_name" => "LIKE", "course_name" => "LIKE", "course_number" => "LIKE");

$whereStmt = generateWhereStatement($conn, $allowedProps, $changesKeysRemap, $columnWhereClause, $_GET);

$insertTypes = "";
$insertVals = array();
if(!empty($whereStmt[1])){
    foreach ($whereStmt[1] as $k=>$value) {
        if(strpos($k, 'course_') === 0 || ($k == "last_name")){
            array_push($insertVals, '%'.$value.'%');
        }else{
            array_push($insertVals, $value);
        }
        $insertTypes = $insertTypes."s";
    }
}

$notesCount = "(SELECT count(*) FROM notes WHERE user_id = u.id AND course_id = c.id) as notesAuthored";
$selectQuery =  "SELECT u.id as 'userId', c.id as 'courseId', ua.role, ua.expires_on, ua.created, u.first_name as 'firstName', u.last_name as 'lastName', ".
                "c.course_name as 'courseName', c.course_number as 'courseNumber', c.section as 'courseSection', $notesCount ".
                "FROM user_access ua INNER JOIN users u ON ua.user_id=u.id INNER JOIN courses c ON ua.course_id=c.id".$whereStmt[0];
$selectQuery = $selectQuery. " LIMIT ".$GLOBALS['PAGE_SIZES']." OFFSET ". ($offset * $GLOBALS['PAGE_SIZES']);

$accesses = database_get_all($conn, $selectQuery, $insertTypes, $insertVals);

echoSuccess($conn, array("useraccesses" => $accesses));
?>