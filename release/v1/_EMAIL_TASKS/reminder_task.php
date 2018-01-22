<?php
include_once "./_generic_email.php";
include_once "../_database.php";

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
    
    $to      = 'zackarytherrien@gmail.com';
    $subject = 'the subject';
    $message = 'hello from nexchange';
    
    send_email($conn, $to, $subject, $message);
    
    echo "Emails sent.".PHP_EOL;
}
echo "Email Task Ran.".PHP_EOL;
echo "\\==================NEXCHANGE EMAIL TASK=================/".PHP_EOL;
?>