<?php
/*
This script will send reminders to notetakers who have not uploaded any notes for the past x days (see consts below...)
*/
$NOTETAKER_REMINDER_DAYS = 7;
echo "/==================NEXCHANGE EMAIL TASK=================\\".PHP_EOL;
echo "Sending Email Task running...".PHP_EOL;
if (php_sapi_name() == "cli") { //Was this script ran from the commandline ?! Only allow this script to run from the commandline.
                                //We are not checking for credentials but we expect commandline is secure. If an intruder has cmd access
                                //Everything is vulnerable
    
    $to      = 'zackarytherrien@gmail.com';
    $subject = 'the subject';
    $message = 'hello from nexchange';
    $headers = 'From: no-reply@nexchange.johnabbott.qc.ca' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);
    echo "Emails sent.".PHP_EOL;
}
echo "Email Task Ran.".PHP_EOL;
echo "\\==================NEXCHANGE EMAIL TASK=================/".PHP_EOL;
?>