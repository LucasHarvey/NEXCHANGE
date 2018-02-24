<?php

include_once("./_EMAIL_TASKS/_generic_email.php");

function notify_note_upload_email_task($conn, $users, $noteId){
    if(empty($noteId)){
        echoError(null, 500, "InternalServerError", "No note id sent to upload note notify task.");
    }
    
    $note = database_get_row($conn, "SELECT n.name, description, n.taken_on, c.course_name, c.course_number, n.course_id FROM notes n INNER JOIN courses c ON n.course_id = c.id WHERE n.id=?", "s", $noteId);
    
    $users = database_get_all($conn, "SELECT u.id, u.email FROM users u INNER JOIN user_access ua ON ua.user_id = u.id WHERE ua.course_id=? AND role='STUDENT'", "s", $note['course_id']);
    
    $link = $GLOBALS['NEXCHANGE_DOMAIN'] . "/home";
    
    $subject = 'No-Reply: NEXCHANGE - Notes Uploaded';
    $message = "New notes were uploaded by a note taker.\n\nNote Details\n\nName: ".$note['name'].
        "\nDescription: ".$note['description'].
        "\nTaken On: ".$note['taken_on'].
        "\nFor: ".$note['course_name']."(".$note['course_number'].")\n\n You can login at: $link";
    
    $resp = array();
    foreach ($users as $user) {
        $re = send_email($conn, 1, $user['id'], $user['email'], $subject, $message);
        array_push($resp, array($user['id'], $re));
    }
    return $resp;
}


?>