<?php

include_once "./v1/_globals.php";
include_once "./v1/_authentication.php";

// Verify that the user is an admin
if(getUserPrivilege() != "ADMIN"){
    header('Location: ./login');
    die();
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Course | NEXCHANGE</title>
    <link rel="shortcut icon" type="image/png" href="./img/favicon.png"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=5">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002b80"/>

    <script async type="text/javascript" src="js/MessageCode.js?v=5"></script>
    <script async type="text/javascript" src="js/Resources.js?v=5"></script>
    <script async type="text/javascript" src="js/app.js?v=5"></script>
    <script async type="text/javascript" src="js/navbar.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/modal.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/user.js?v=5"></script>

    <script async type="text/javascript" src="js/Components/editCourse.js?v=5"></script>
    <script async type="text/javascript" src="js/Components/dateFormatting.js?v=5"></script>

</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./signup"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
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
                        <input type="text" id="section" placeholder="Section" maxlength="255">
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
        <a href="https://nexchange.ca" target="_blank"><img src="./img/nexchange_official_logo_white.png" alt="Nexchange Logo"></a>
        <div>
            <small><a href="./license">&copy; Copyright 2018 Lucas Harvey All Rights Reserved</a></small>
            <small>Created in collaboration with Zackary Therrien</small>
        </div>
    </footer>
</body>

</html>
