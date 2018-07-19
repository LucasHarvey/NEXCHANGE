<?php
$conn = database_connect();

$user_id = getUserFromToken();
if($user_id == null)
    echoError($conn, 403, "AuthorizationFailed");

if(array_key_exists("id", $_GET)){
    $note = getNoteById($conn, $_GET['id']);
    if($note == null){
        echoError($conn, 404, "NoteNotFound");
    }
    echoSuccess($conn, $note);
}

//$sortMethod = array_key_exists("sortMethod", $_GET) ? $_GET["sortMethod"] : "date";
$sortMethod = array_key_exists("sortMethod", $_GET) ? $_GET["sortMethod"] : "n.created";
$sortDirection = array_key_exists("sortDirection", $_GET) ? $_GET['sortDirection'] : "DESC";
$hideDownloaded = array_key_exists("hideDownloaded", $_GET) ? filter_var($_GET['hideDownloaded'], FILTER_VALIDATE_BOOLEAN) : false;
$offset =  array_key_exists("page", $_GET) ? $_GET['page'] : 0;

$studentNotes = getNotesForStudent($conn, $user_id, $sortMethod, $sortDirection, $hideDownloaded, $offset); //will be empty if not a student in any courses.
$notetakerNotes = getNotesForNotetaker($conn, $user_id, $sortMethod, $sortDirection, $hideDownloaded, $offset); // will be empty if not a notetaker in any courses

echoSuccess($conn, array("notes" => array_merge($studentNotes, $notetakerNotes)));

function getNoteById($conn, $noteId){
    $selectQuery = "SELECT n.id, n.name, n.description, n.taken_on, n.created, c.id as courseId, c.course_name as courseName ".
                   "FROM notes n INNER JOIN users u ON n.user_id=u.id INNER JOIN courses c ON n.course_id=c.id ".
                   "WHERE n.id=?";
    return database_get_row($conn, $selectQuery, "s", $noteId);
}

function getNotesForNotetaker($conn, $userId, $_sortMethod, $_sortDirection, $hideDownloaded, $offset){
    
    $selectQuery = "SELECT  n.id, n.name, n.description, n.taken_on, n.created, nf.extension, nf.size, nfd.lastDownloaded, ".
                            "u.first_name, u.last_name, c.id as courseId, c.course_name as courseName, c.semester as semester, ua.role ".
                    "FROM notes n INNER JOIN users u ON n.user_id=u.id INNER JOIN courses c ON n.course_id=c.id ".
                        "INNER JOIN user_access ua ON ua.course_id=n.course_id ".
                            "INNER JOIN notefiles nf ON n.id=nf.note_id ".
                            "LEFT OUTER JOIN ( ".
                                    "SELECT notefile_id, user_id, MAX(downloaded_at) as lastDownloaded FROM notefile_downloads group by notefile_id, user_id ".
                                ") as nfd ON nfd.notefile_id=nf.id AND nfd.user_id=ua.user_id ".
                    "WHERE ua.user_id=? AND n.user_id = ua.user_id AND ua.role='NOTETAKER' AND ua.expires_on >= NOW()";
                    
    if($hideDownloaded){
        $selectQuery = $selectQuery . " AND nfd.lastDownloaded IS NULL";
    }
    
    $selectQuery = $selectQuery . getSortQuery($_sortMethod, $_sortDirection, $offset);
    
    return database_get_all($conn, $selectQuery, "s", $userId);
}

function getNotesForStudent($conn, $userId, $_sortMethod, $_sortDirection, $hideDownloaded, $offset){
    //Get the notes a student is allowed to access,
    //Retrieve note information, course information, author information, and the last time user downloaded the note.
    
    //Entire query including modifications: 5 hours of work.
    $selectQuery = "SELECT  n.id, n.name, n.description, n.taken_on, n.created, nf.extension, nf.size, nfd.lastDownloaded, ".
                            "c.id as courseId, c.course_name as courseName, c.semester as semester, ua.role ".
                    "FROM notes n LEFT JOIN users u ON n.user_id=u.id INNER JOIN courses c ON n.course_id=c.id ".
                        "INNER JOIN user_access ua ON ua.course_id=n.course_id ".
                            "INNER JOIN notefiles nf ON n.id=nf.note_id ".
                            "LEFT OUTER JOIN ( ".
                                    "SELECT notefile_id, user_id, MAX(downloaded_at) as lastDownloaded FROM notefile_downloads group by notefile_id, user_id ".
                                ") as nfd ON nfd.notefile_id=nf.id AND nfd.user_id=ua.user_id ".
                    "WHERE ua.user_id=? AND n.user_id IS NULL OR n.user_id != ua.user_id AND ua.role='STUDENT' AND ua.expires_on >= NOW()";
                    
    if($hideDownloaded){
        $selectQuery = $selectQuery . " AND nfd.lastDownloaded IS NULL";
    }
    $selectQuery = $selectQuery . getSortQuery($_sortMethod, $_sortDirection, $offset);
    
    return database_get_all($conn, $selectQuery, "s", $userId);
}

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
        case "taken_on": 
            $sortMethod = " ORDER BY n.taken_on";
            break;
        default:
            $sortMethod = " ORDER BY n.created";
    }
    
    return $sortMethod . $sortDirection . " LIMIT ".$GLOBALS['PAGE_SIZES']." OFFSET ". ($offset * $GLOBALS['PAGE_SIZES']);
}
?>