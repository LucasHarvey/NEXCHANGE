<?php
/*
This script will send notifications to students that a new note was uploaded.
*/
echo "/==================NEXCHANGE DELETE TOKEN TASK=================\\".PHP_EOL;
echo "Runnning task to delete old tokens from database".PHP_EOL;
if (php_sapi_name() == "cli") { //Was this script ran from the commandline ?! Only allow this script to run from the commandline.
                                //We are not checking for credentials but we expect commandline is secure. If an intruder has cmd access
                                //Everything is vulnerable
    include_once("../_database.php");
    $conn = database_connect();
    $query = "DELETE FROM auth_tokens WHERE DATE_SUB(expires_on, INTERVAL 24 HOUR) < NOW()";
    $retVal = database_delete($conn, $query, "", array());
    if($retVal === true){
        echo "RAN SUCCESSFULLY.".PHP_EOL;
    }else{
        echo "RUN FAILED because: ".$retVal.PHP_EOL;
    }
}
echo "Deleting old tokens task ran.".PHP_EOL;
echo "\\==================NEXCHANGE DELETE TOKEN TASK=================/".PHP_EOL;
?>