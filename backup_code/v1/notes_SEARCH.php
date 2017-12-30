<?php

$conn = database_connect();
$user_id = getUserFromToken($conn);

if(getUserPrivilege($conn, $user_id) != "ADMIN"){
    echoError(403, "AuthorizationFailed");
}

$baseQuery= "SELECT n.id, n.user_id as 'author_id', n.course_id, n.created, n.name, n.description, n.taken_on, nf.download_count, nf.distinct_downloads, ".
                "concat(u.first_name, concat(' ', u.last_name)) as 'user_name', c.course_name, c.course_number, c.section FROM notes n ".
            "LEFT JOIN users u ON n.user_id=u.id INNER JOIN courses c ON n.course_id=c.id LEFT JOIN ".
                "(SELECT gnf.note_id, gnfd.totalcount as download_count, gnfd.countdist as distinct_downloads FROM notefiles gnf INNER JOIN ".
                    "(SELECT notefile_id, COUNT(DISTINCT gnfd.user_id) as countdist, COUNT(*) as totalcount FROM notefile_downloads gnfd GROUP BY notefile_id) as gnfd ".
                    "ON gnfd.notefile_id = gnf.id ".
                "GROUP BY gnf.note_id) as nf ".
            "ON n.id=nf.note_id ";


$userKey = array_key_exists('studentId', $_GET);
$courseKey = array_key_exists('courseId', $_GET);
$offset = array_key_exists('page', $_GET) ? $_GET['page'] : 0;
//If both keys dont exist, return error, you cant search for every note.
if(!$userKey && !$courseKey){
    echoError(400, "KeyNotFound");
}

$notes = array();
$limit = " LIMIT ".$GLOBALS['PAGE_SIZES']." OFFSET ". ($offset * $GLOBALS['PAGE_SIZES']);
if($userKey && !$courseKey){
    $select = $baseQuery."WHERE n.user_id=?" .$limit;
    $notes = database_get_all($conn, $select, "s", $_GET['studentId']);
}else if($courseKey && !$userKey){
    $select = $baseQuery."WHERE n.course_id=?" .$limit;
    $notes = database_get_all($conn, $select, "s", $_GET['courseId']);
}else{
    $select = $baseQuery."WHERE n.user_id=? AND n.course_id=?" .$limit;
    $notes = database_get_all($conn, $select, "ss", array($_GET['studentId'], $_GET['courseId']));
}
$conn->close();

echoSuccess(array("notes" => $notes));

?>