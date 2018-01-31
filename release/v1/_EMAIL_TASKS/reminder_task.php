<?php
include_once "v1/_EMAIL_TASKS/_generic_email.php";
include_once "v1/_database.php";

/*
This script will send reminders to notetakers who have not uploaded any notes for the past x days (see consts below...)
*/
$NOTETAKER_REMINDER_DAYS = 7;
echo "/==================NEXCHANGE EMAIL TASK=================\\".PHP_EOL;
echo "Sending Email Task running...".PHP_EOL;
if (php_sapi_name() == "cli") { //Was this script ran from the commandline ?! Only allow this script to run from the commandline.
                                //We are not checking for credentials but we expect commandline is secure. If an intruder has cmd access
                                //Everything is vulnerable
    $conn = database_connect();
    
    $users = "SELECT c.id, c.course_name, c.course_number, u.email 
        FROM courses c INNER JOIN user_access ua ON c.id=ua.course_id
            INNER JOIN users u ON ua.user_id = u.id 
        WHERE ua.role='NOTETAKER'";
        
    //Course added within 7 days
    //If empty, notify user id.
    $notesWithin7Days = "SELECT * FROM notes WHERE DATE_ADD(created, INTERVAL $NOTETAKER_REMINDER_DAYS DAY) > NOW() ORDER BY created DESC";
    $courseWithin7Days = "SELECT * FROM $notesWithin7Days as n GROUP BY n.course_id";
        
    $latestNotes = "SELECT * FROM users u LEFT INNER JOIN ($coursesWithin7Days) as nc ON u.id=nc.user_id";
    
    $subject = 'No-Reply: NEXCHANGE - Reminder to Upload Notes';
    $message = 'Hello '.$user['first_name'].' '.$user['last_name'].",\n\nThis is a reminder that you haven't uploaded any notes to NEXCHANGE in 7 days";
    
    foreach ($users as $user) {
        send_email($conn, $user['id'], $user['email'], $subject, $message);
    }
    
    echo "Emails sent.".PHP_EOL;
}
echo "Email Task Ran.".PHP_EOL;
echo "\\==================NEXCHANGE EMAIL TASK=================/".PHP_EOL;
?>
