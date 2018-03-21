<?php
include_once "v1/_errorHandlers.php";
include_once "v1/_EMAIL_TASKS/_generic_email.php";
include_once "v1/_database.php";
include_once "v1/_globals.php";

/*
This script will send reminders to notetakers who have not uploaded any notes for the past x days (see consts below...)
*/
$NOTETAKER_REMINDER_DAYS = 7;
echo "/==================NEXCHANGE EMAIL TASK=================\\".PHP_EOL;
echo "Reminder Email Task Running...".PHP_EOL;
echo "Date: ".date('l jS \of F Y h:i:s A').PHP_EOL;
if (php_sapi_name() == "cli") { //Was this script ran from the commandline ?! Only allow this script to run from the commandline.
                                //We are not checking for credentials but we expect commandline is secure. If an intruder has cmd access
                                //Everything is vulnerable
    $conn = database_connect();
        
    $notesWithin7Days = "SELECT n.user_id, n.course_id FROM notes n WHERE DATE_ADD(created, INTERVAL $NOTETAKER_REMINDER_DAYS DAY) > NOW() ORDER BY created DESC";
    $coursesWithin7Days = "SELECT n.user_id, n.course_id FROM ($notesWithin7Days) n GROUP BY n.course_id,n.user_id";
    $selectUsers = "SELECT u.id,u.email,u.first_name,u.last_name,c.course_name,c.course_number ".
                    "FROM user_access ua INNER JOIN users u ON ua.user_id = u.id INNER JOIN courses c ON ua.course_id=c.id ".
                    "WHERE ua.role='NOTETAKER' AND (ua.user_id, ua.course_id) NOT IN ($notesWithin7Days)";
    
    $usersAndCourses = database_get_all($conn, $selectUsers, "", array());
    
    $link = $GLOBALS['NEXCHANGE_DOMAIN'] . "/login";
    $subject = 'No-Reply: NEXCHANGE - Reminder to Upload Notes';
    
    foreach ($usersAndCourses as $user) {
        $message = 'Hello '.$user['first_name'].' '.$user['last_name'].
            ",\n\nThis is a reminder that you haven't uploaded any notes to NEXCHANGE for a course you're registered in.".
            "\n\nYou've reached $NOTETAKER_REMINDER_DAYS days without uploading notes for ".$user['course_number'] . " (".$user['course_name'].").".
            "\n\nYou can login at: ".$link;
            
        //echo "Sending email to ".$user['email']. " because of ".$user['course_name'].PHP_EOL;
        
        send_email($conn, 2, $user['id'], $user['email'], $subject, $message, false);
    }
    echo count($usersAndCourses)." reminders sent.".PHP_EOL;
}
echo "Reminder Email Task Ran.".PHP_EOL;
echo "\\==================NEXCHANGE EMAIL TASK=================/".PHP_EOL;
?>
