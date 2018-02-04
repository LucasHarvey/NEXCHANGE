<?php
$GLOBALS['PAGE_SIZES'] = 20; //used for pagination
$GLOBALS['NEXCHANGE_DIR'] = "/release"; //Used mainly for C9 development set to "/release";
$GLOBALS['NEXCHANGE_DOMAIN'] = "nexchange.johnabbott.qc.ca" + $GLOBALS['NEXCHANGE_DIR']; //used for cookie access
$GLOBALS['NEXCHANGE_SECRET'] = "F/|wL~[M%@r],d;xL+GMLB_8X?fx8xhpM1~5|*xU_?K[+f8<lzCio+'7'~kv[e<";
$GLOBALS['NEXCHANGE_TOKEN_EXPIRY_MINUTES'] = 15; //number of minutes before expiry of JWT
$GLOBALS['NEXCHANGE_SECURED_SITE'] = true; //important, if secured is FORCED you must add https to NEXCHANGE DOMAIN
$GLOBALS['NEXCHANGE_LANDING_PAGES'] = array("ADMIN" =>"signup", "USER" => "home", "NONE" => "login");
?>