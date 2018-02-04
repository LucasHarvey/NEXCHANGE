<!DOCTYPE html>
<html>

<head>
    <title>Edit Note | NEXCHANGE</title>
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
    
    <script async type="text/javascript" src="js/Polyfills/DatePolyfill.js"></script>
    <script async type="text/javascript" src="js/Components/editNote.js"></script>
</head>

<body>

    <!-- Header -->
    <header class="header">
        <a class="logo" href="./home.html"><img src="./img/jac_logo.png" alt="John Abbott Logo"></a>
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

            <h1>Edit Notes</h1>

            <div class="userform-wrapper">
                <form id="noteData" class="userform">
                    <div class="userfield">
                          <label for="noteName">Note Name: </label>
                          <input type="text" id="noteName" name="noteName" maxlength="60" required>
                    </div>

                    <div class="userfield">
                        <label for="lastName">Description: </label>
                        <textarea id="description" class="description" name="description" maxlength="500"></textarea>
                    </div>
                    <div class="userfield">
                        <span id="characterCount"></span>
                    </div>

                    <div class="userfield">
                        <label for="date">Notes Taken On: </label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    
                    <div class="userfield">
                        <label>Select file: </label>
                        <input type="file" id="file" name="file[]" class="inputFile" multiple>
                        <label id="fileLabel" for="file"></label>
                    </div>

                    <div class="userfield">
                        <input class="button" type="submit" id="submit" name="submit" value="Save Changes">
                    </div>

                    <div class="userfield">
                        <input type="button" class="button warning" id="deleteNote" value="Delete Note">
                    </div>
                    
                    <div class="userfield">
                        <div id="barContainer" class="bar" style="display: none;">
                            <span class="bar-fill" id="pb"><span class="bar-fill-text" id="pt"></span></span>
                        </div>
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