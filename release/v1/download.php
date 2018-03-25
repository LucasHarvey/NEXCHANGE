<?php

function outputFileContent($storage_name, $file_name, $type, $size){
    
    $storage_name = "./Files/".$storage_name;

    if(!file_exists($storage_name)){
        http_response_code(404);
        die();
    }

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false); // required for certain browsers
    header('Content-Type: '.$type);
    header('Content-Disposition: inline; filename="'.$file_name.'"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.$size);

    readfile($storage_name);

}

if (!isset($_GET['xsrfToken'])) {
    $conn -> close();
    http_response_code(400);
    die();
}

include_once "./_modified_generics.php";

$conn = database_connect();

$user_id = getUserFromToken();

// validate filename input
if (!isset($_GET['noteId'])) {
    $conn -> close();
    http_response_code(400);
    die();
}

$note_id = $_GET["noteId"];

$note = database_get_row($conn, "SELECT * FROM notes WHERE id=?", "s", $note_id);

if(!$note){
    $conn -> close();
    http_response_code(400);
    die();
}

// Ensure that the user has access to the notes
if(getUserPrivilege() != "ADMIN"){
    $userAccess = database_get_row($conn, "SELECT user_id FROM user_access WHERE user_id=? AND course_id=?", "ss", array($user_id, $note["course_id"]));
    if(!$userAccess){
        $conn -> close();
        http_response_code(403);
        die();
    }
}

$file = database_get_row($conn, "SELECT id, file_name, storage_name, type, size, md5 FROM notefiles WHERE note_id=?", "s", $note_id);

if(!$file){
    $conn -> close();
    http_response_code(404);
    die();
}

database_insert($conn, "INSERT INTO notefile_downloads (notefile_id, user_id) VALUES (?,?)", "ss", array($file['id'], $user_id));

$conn -> close();

outputFileContent($file["storage_name"], $file["file_name"], $file['type'], $file["size"]);

?>