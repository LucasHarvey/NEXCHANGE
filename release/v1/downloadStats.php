<?php

header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include_once "./_modified_generics.php";

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    http_response_code(404);
    die();
}

// validate filename input
if (!isset($_GET['type'])) {
    http_response_code(400);
    die();
}

$statType = $_GET["type"];
if($statType != "global" && $statType != "user"){
    http_response_code(400);
    die();
}

$date = date('Y-m-d');
$storage_name = "./Statistics/".$statType."_statistics_".$date.".csv";
$file_name = $statType."_statistics_".$date.".csv";

exec("mysql nexchange -uroot --password=THE_PASSWORD < ./Admin/".$statType."_statistics.sql | sed 's/\t/,/g' > ".$storage_name." 2>&1", $output, $result);

if(!file_exists($storage_name)){
    http_response_code(500);
    die();
}

header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false); // required for certain browsers
header('Content-Type: text/csv');
header('Content-Disposition: inline; filename='.$file_name);
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($storage_name));

readfile($storage_name);
die();

?>