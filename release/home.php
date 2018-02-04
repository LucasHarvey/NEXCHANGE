<!DOCTYPE html>
<html>

<head>
    <title>Home | NEXCHANGE</title>
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

    <script async type="text/javascript" src="js/Components/home.js"></script>
    <script async type="text/javascript" src="js/Components/dateFormatting.js"></script>
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
            
            <div class="userform-wrapper">
                <form id="userData" class="userform">
                    <div class="userfield">
                        <label for="sortDrop" class="sortByLabel">Sort By</label>
                        <select id="sortDrop">
                            <option name="sortMethod" value="newestUpload" selected>
                                Newest by Upload Date
                            </option>
                            <option name="sortMethod" value="oldestUpload">
                                Oldest by Upload Date
                            </option>
                            <option name="sortMethod" value="newestTakenOn">
                                Newest by Taken On Date
                            </option>
                            <option name="sortMethod" value="oldestTakenOn">
                                Oldest by Taken On Date
                            </option>
                            <option name="sortMethod" value="noteNameAscending">
                                Note Name A-Z
                            </option>
                            <option name="sortMethod" value="noteNameDescending">
                                Note Name Z-A
                            </option>
                        </select>
                    </div>
                    <div class="userfield" id="hideDownloadedField"> 
                        <label for="hideDownloaded" class="checkboxLabel">Hide Downloaded Notes</label>
                        <input type="checkbox" id="hideDownloaded">
                    </div>
                </form>
            </div>
            
            <div id="notesContainer"></div>

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