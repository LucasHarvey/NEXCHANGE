<?php

function outputFileContent($conn, $storage_name, $type, $file_name, $size, $expectedMD5){
    
    if(!file_exists($storage_name)){
        echoError($conn, 404, "NoFilesForNote");
    }
    
    $content = file_get_contents($storage_name);
    
    header('Content-Description: Actual MD5('.md5($content).') - Expected MD5('.$expectedMD5.')');
    header('Content-Type: '.$type);
    header('Content-Disposition: attachment; filename="'.$file_name.'"');
    header('Content-Length: ' . $size);
    ob_clean();
    flush();
    
    ob_start();
    echo $content;
    ob_flush();
}

$conn = database_connect();

$user_id = getUserFromToken();

requiredParams($conn, $_GET, array("noteId"));
$note_id = $_GET["noteId"];

$note = database_get_row($conn, "SELECT * FROM notes WHERE id=?", "s", $note_id);
if(!$note){
    echoError($conn, 404, "NoteNotFound");
}

// Ensure that the user has access to the notes
if(getUserPrivilege() != "ADMIN"){
    $userAccess = database_get_row($conn, "SELECT user_id FROM user_access WHERE user_id=? AND course_id=?", "ss", array($user_id, $note["course_id"]));
    if(!$userAccess){
        echoError($conn, 403, "UserDownloadNotesDenied");
    }
}

$file = database_get_row($conn, "SELECT id, file_name, storage_name, type, size, md5 FROM notefiles WHERE note_id=?", "s", $note_id);

if(!$file){
    echoError($conn, 404, "NoFilesForNote");
}

database_insert($conn, "INSERT INTO notefile_downloads (notefile_id, user_id) VALUES (?,?)", "ss", array($file['id'], $user_id));
outputFileContent($conn, $file["storage_name"], $file["type"], $file["file_name"], $file["size"], $file["md5"]);
?>