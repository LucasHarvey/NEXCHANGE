<?php

include_once("./_generic_email.php");

function notify_note_upload_email_task($conn, $users, $noteId){
    if(empty($noteId)){
        echoError(null, 500, "InternalServerError", "No note id sent to upload note notify task.");
    }
    
    $note = database_get_row($conn, "SELECT n.name, description, n.taken_on, c.course_name, c.course_number FROM notes n INNER JOIN courses c ON n.course_id = c.id WHERE n.id=?", $noteId);
    
    $link = $GLOBALS['NEXCHANGE_DOMAIN'] . "/home.html";
    
    $subject = 'No-Reply: NEXCHANGE - Notes Uploaded';
    $message = "New notes were uploaded by a note taker.\n\nName: ".$note['name'].
        "\nDescription: ".$note['description'].
        "\nTaken On: ".$note['taken_on'].
        "\nFor: ".$note['course_name']."(".$note['course_number'].")\n\n You can login at: $link";
    
    foreach ($users as $user) {
        send_email($conn, $user['id'], $user['email'], $subject, $message);
    }
}


?>