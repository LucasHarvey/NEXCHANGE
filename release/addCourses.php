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
    <title>Add Course | NEXCHANGE</title>
    <link rel="shortcut icon" type="image/png" href="./img/favicon.png"/>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css?v=4">
    <script src="js/Polyfills/flexibility.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#002b80"/>

    <script async type="text/javascript" src="js/MessageCode.js?v=4"></script>
    <script async type="text/javascript" src="js/Resources.js?v=4"></script>
    <script async type="text/javascript" src="js/app.js?v=4"></script>
    <script async type="text/javascript" src="js/navbar.js?v=4"></script>
    <script async type="text/javascript" src="js/Components/modal.js?v=4"></script>
    <script async type="text/javascript" src="js/Components/user.js?v=4"></script>

    <script async type="text/javascript" src="js/Components/addCourses.js?v=4"></script>
    <script async type="text/javascript" src="js/Components/dateFormatting.js?v=4"></script>

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

            <h1>Add Courses</h1>

            <div class="userform-wrapper">
                <form id="addCourses" class="userform">
    
                    <div class="userfield">
                        <label>Select file: </label>
                        <div>
                            <input type="file" id="file" name="file[]" class="inputFile" required>
                            <label id="fileLabel" for="file"></label>
                        </div>
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
                        <input class="button" type="submit" id="submit" value="Upload CSV of Courses">
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
