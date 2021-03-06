<?php
$GLOBALS['PAGE_SIZES'] = 20; //used for pagination
$GLOBALS['NEXCHANGE_DOMAIN'] = "nexchange.johnabbott.qc.ca"; //used for cookie access
$GLOBALS['NEXCHANGE_SECRET'] = "F/|wL~[M%@r],d;xL+GMLB_8X?fx8xhpM1~5|*xU_?K[+f8<lzCio+'7'~kv[e<";
$GLOBALS['NEXCHANGE_TOKEN_EXPIRY_MINUTES'] = 15; //number of minutes before expiry of JWT
$GLOBALS["NEXCHANGE_BRUTE_INTERVAL"] = 60; // number of seconds for brute force prevention
$GLOBALS["NEXCHANGE_BRUTE_WAIT"] = 2*60; // number of seconds user must wait to retry
$GLOBALS['NEXCHANGE_SECURED_SITE'] = true; //important, if secured is FORCED you must add https to NEXCHANGE DOMAIN
$GLOBALS['MAX_SINGLE_FILE_SIZE'] = 5 * 1024 * 1024; // 5 MB
$GLOBALS['ALLOWED_FILE_EXTENSIONS'] = ['pdf','docx','doc','pptx','ppt','xlsx','csv','jpeg','jpg','png', 'txt', 'zip'];
$GLOBALS['REL_FILES_PATH'] = "./Files/";
$GLOBALS['PASSWORD_LENGTH'] = 9;
$GLOBALS['NEXCHANGE_TIMEZONE'] = "America/Toronto";
?>