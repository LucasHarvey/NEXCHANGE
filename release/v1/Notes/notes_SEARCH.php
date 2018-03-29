<?php

$conn = database_connect();

if(getUserPrivilege() != "ADMIN"){
    echoError($conn, 403, "AuthorizationFailed");
}

$baseQuery = "SELECT n.id, n.user_id as 'author_id', n.course_id, n.created, n.name, n.description, n.taken_on, nf.download_count, nf.distinct_downloads, ".
                "concat(u.first_name, concat(' ', u.last_name)) as 'user_name', c.course_name, c.course_number, c.section_start as sectionStart, c.section_end as sectionEnd FROM notes n ".
            "LEFT JOIN users u ON n.user_id=u.id INNER JOIN courses c ON n.course_id=c.id LEFT JOIN ".
                "(SELECT gnf.note_id, gnfd.totalcount as download_count, gnfd.countdist as distinct_downloads FROM notefiles gnf INNER JOIN ".
                    "(SELECT notefile_id, COUNT(DISTINCT gnfd.user_id) as countdist, COUNT(*) as totalcount FROM notefile_downloads gnfd GROUP BY notefile_id) as gnfd ".
                    "ON gnfd.notefile_id = gnf.id ".
                "GROUP BY gnf.note_id) as nf ".
            "ON n.id=nf.note_id ";


$sortMethod = array_key_exists("sortMethod", $_GET) ? $_GET["sortMethod"] : "n.created";
$sortDirection = array_key_exists("sortDirection", $_GET) ? $_GET['sortDirection'] : "DESC";
$hideDownloaded = array_key_exists("hideDownloaded", $_GET) ? filter_var($_GET['hideDownloaded'], FILTER_VALIDATE_BOOLEAN) : false;
$userKey = array_key_exists('studentId', $_GET);
$courseKey = array_key_exists('courseId', $_GET);
$offset = array_key_exists('page', $_GET) ? $_GET['page'] : 0;

$noteCountQuery = "SELECT COUNT(*) as number FROM notes n ";

//If both keys dont exist, return error, you cant search for every note.
if(!$userKey && !$courseKey){
    echoError($conn, 400, "KeyNotFound");
}

$sortQuery = getSortQuery($sortMethod, $sortDirection, $offset);
$notes = array();
$noteCount["number"] = 0;

if($userKey && !$courseKey){
    $select = $baseQuery."WHERE n.user_id=?" .$sortQuery;
    $notes = database_get_all($conn, $select, "s", $_GET['studentId']);
    $selectNoteCount = $noteCountQuery."WHERE n.user_id=?";
    $noteCount = database_get_row($conn, $selectNoteCount, "s", $_GET['studentId']);
}else if($courseKey && !$userKey){
    $select = $baseQuery."WHERE n.course_id=?" .$sortQuery;
    $notes = database_get_all($conn, $select, "s", $_GET['courseId']);
    $selectNoteCount = $noteCountQuery."WHERE n.course_id=?";
    $noteCount = database_get_row($conn, $selectNoteCount, "s", $_GET['courseId']);
}else{
    $select = $baseQuery."WHERE n.user_id=? AND n.course_id=?" .$sortQuery;
    $notes = database_get_all($conn, $select, "ss", array($_GET['studentId'], $_GET['courseId']));
    $selectNoteCount = $noteCountQuery."WHERE n.user_id=? AND n.course_id=?";
    $noteCount = database_get_row($conn, $selectNoteCount, "ss", array($_GET['studentId'], $_GET['courseId']));
}

echoSuccess($conn, array(
    "notes" => $notes,
    "noteCount" => $noteCount["number"]
    ));


function getSortQuery($_sortMethod, $_sortDirection, $offset){
    $sortMethod = " ORDER BY n.created";
    $sortDirection = " DESC";
    
    if($_sortDirection == "ASC"){
        $sortDirection = " ASC";
    }
    
    
    switch ($_sortMethod) {
        case "course":
            $sortMethod = " ORDER BY c.course_name";
            break;
        case "noteName": 
            $sortMethod = " ORDER BY n.name";
            break;
        case "author":
            $sortMethod = " ORDER BY u.last_name";
            break;
        case "taken_on": 
            $sortMethod = " ORDER BY n.taken_on";
            break;
        default:
            $sortMethod = " ORDER BY n.created";
    }
    
    return $sortMethod . $sortDirection . " LIMIT ".$GLOBALS['PAGE_SIZES']." OFFSET ". ($offset * $GLOBALS['PAGE_SIZES']);
}
?>