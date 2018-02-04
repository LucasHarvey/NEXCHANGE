<?php
include_once "v1/_globals.php";
include_once "v1/_authentication.php";

$priv = getUserPrivilege($token);
if($priv != "ADMIN"){
    header("Location: https://".$GLOBALS['NEXCHANGE_DOMAIN']."/".$GLOBALS['NEXCHANGE_LANDING_PAGES'][$priv]);
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Course | NEXCHANGE</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002b80"/>

    <script async type="text/javascript" src="js/MessageCode.js"></script>
    <script async type="text/javascript" src="js/Resources.js"></script>
    <script async type="text/javascript" src="js/app.js"></script>
    <script async type="text/javascript" src="js/navbar.js"></script>
    <script async type="text/javascript" src="js/Components/modal.js"></script>
    <script async type="text/javascript" src="js/Components/user.js"></script>

    <script async type="text/javascript" src="js/Components/editCourse.js"></script>
    <script async type="text/javascript" src="js/Components/dateFormatting.js"></script>

</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./signup.html"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
        <nav>
            <div id="navbar">
                <aside></aside>
                <div id="navmain">
                    <a id="logout">LOG OUT</a>
                </div>
                <aside></aside>
            </div>
        </nav>
    </header>

    <div>
        <aside class="aside1"></aside>
        <div class="main">
            
            <div class="userform-wrapper">
                <a class="feedbackLink" href="https://goo.gl/forms/QbWXdBqwvv6H0Tkq1">Feedback and Bug Report</a>
            </div>

            <h1>Edit Course</h1>

            <div class="userform-wrapper">
                <form id="editCourse" class="userform">

                    <div class="userfield">
                        <input type="text" id="courseName" placeholder="Course Name" maxlength="100">
                    </div>

                    <div class="userfield">
                        <input type="text" id="courseNumber" placeholder="Course Code" maxlength="10">
                    </div>

                    <div class="userfield">
                        <input type="text" id="sectionStart" placeholder="Section From" maxlength="5">
                    </div>
                    <div class="userfield">
                        <input type="text" id="sectionEnd" placeholder="Section To (inclusive)" maxlength="5">
                    </div>
                    
                    <div class="userfield">
                        <input type="text" id="teacherFullName" placeholder="Teacher Full Name" maxlength="255">
                    </div>

                    <div class="userfield">
                        <div class="subfield">
                            <select id="season">
                                <option value="F">
                                    Fall
                                </option>
                                <option value="I">
                                    Intersession
                                </option>
                                <option value="W">
                                    Winter
                                </option>
                                <option value="S">
                                    Summer
                                </option>
                            </select>
                            <input type="number" id="year" min="2017">
                        </div>
                    </div>


                    <div class="userfield">
                        <input class="button" type="submit" id="submit" value="Edit Course">
                    </div>
                </form>
            </div>

        </div>
        <aside class="aside2"></aside>
    </div>
    
    <footer>
        <img src="./img/nexchange_official_logo.png" alt="Nexchange Logo">
        <div>
            <small><a href="./license.html">&copy; Copyright 2018 Lucas Harvey All Rights Reserved</a></small>
            <small>Created in collaboration with Zackary Therrien</small>
        </div>
    </footer>
</body>

</html>
