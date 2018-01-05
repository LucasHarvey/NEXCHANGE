<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

$offset =  array_key_exists("page", $_GET) ? $_GET['page'] : 0;

$allowedProps = array("studentId", "name", "page");
$changesKeysRemap = array("studentId" => "login_id", "name" => "last_name", "page" => NULL);
$columnWhereClause = array("login_id" => "=", "last_name" => "LIKE");

$whereStmt = generateWhereStatement($conn, $allowedProps, $changesKeysRemap, $columnWhereClause, $_GET);

$insertTypes = "";
$insertVals = array();
if(!empty($whereStmt[1])){
    foreach ($whereStmt[1] as $k=>$value) {
        if($k == 'last_name'){
            array_push($insertVals, '%'.$value.'%');
        }else{
            array_push($insertVals, $value);
        }
        $insertTypes = $insertTypes.'s';
    }
}

$notesCount = "(SELECT count(*) FROM notes WHERE user_id = u.id) as notesAuthored";
$selectQuery = "SELECT id, login_id as 'studentId', first_name as 'firstName', last_name as 'lastName', email, created, $notesCount FROM users u ".$whereStmt[0] . " ORDER BY login_id ASC";
$selectQuery = $selectQuery. " LIMIT ".$GLOBALS['PAGE_SIZES']." OFFSET ". ($offset * $GLOBALS['PAGE_SIZES']);

$users = database_get_all($conn, $selectQuery, $insertTypes, $insertVals);
//Remove all "Admin" users.
foreach($users as $k=>$us){
    if(strpos($us["studentId"], 'Admin') === 0){
        array_splice($users, array_search($us, $users), 1);
        break;
    }
}
echoSuccess($conn, array("users" => $users));
?>