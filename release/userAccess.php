<!DOCTYPE html>
<html>

<head>
    <title>User Access | NEXCHANGE</title>
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

    <script async type="text/javascript" src="js/Components/userAccess.js"></script>
    <script async type="text/javascript" src="js/Components/postCourseSearch.js"></script>
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

            <h1>Course Search</h1>

            <div class="userform-wrapper">
                <form id="courseSearch" class="userform">

                    <div class="userfield">
                        <input type="text" id="courseName" placeholder="Course Name" maxlength="100">
                    </div>

                    <div class="userfield">
                        <input type="text" id="courseNumber" placeholder="Course Code" maxlength="10">
                    </div>

                    <div class="userfield">
                        <input type="text" id="section" placeholder="Section" maxlength="5">
                    </div>
                    
                    <div class="userfield">
                        <input type="text" id="teacherFullName" placeholder="Teacher Name" maxlength="255">
                    </div>

                    <div class="userfield">
                        <div class="subfield">
                            <select id="season">
                                <option value="allSemesters" selected>
                                    All Semesters
                                </option>
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
                        <input class="button" type="submit" id="submit" value="Search For Course">
                    </div>
                </form>
            </div>

            <div id="resultsTray" style="display: none">
                <h1>Search Results</h1>

                <div id="tableResults">
                    <table id="results">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Course Code</th>
                                <th>Section</th>
                                <th class="resultsTeacher">Teacher</th>
                                <th>Semester</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                
                <div id="noResults" class="noResults" style="display: none">No Results</div>

                <button id="addCourses" class="submitButton" style="display:none;">Add Course(s)</button>
            </div>

            <h1>Grant User Access</h1>
            <div class="userform-wrapper">
                <form id="userData" class="userform">
                    <div class="userfield">
                        <input type="number" id="studentId" placeholder="Student ID" min="1000000" max="9999999" required>
                    </div>

                    <div class="userfield">
                        <select id="role" placeholder="Role">
                            <option value="" disabled selected>Role</option>
                            <option value="STUDENT">Student in need of notes</option>
                            <option value="NOTETAKER">Notetaker</option>
                        </select>
                    </div>

                    <div class="userfield">
                        <div class="subfield">
                            <select id="seasonExpiry">
                                <option value="" disabled selected>
                                    Access Expiry:
                                </option>
                                <option value="F">
                                    Expiry: Fall
                                </option>
                                <option value="I">
                                    Expiry: Intersession
                                </option>
                                <option value="W">
                                    Expiry: Winter
                                </option>
                                <option value="S">
                                    Expiry: Summer
                                </option>
                            </select>
                            <input type="number" id="yearExpiry" placeholder="Please enter a year" required/>
                        </div>
                        
                    </div>

                    <div class="userfield nocenteralign">
                        <h3 class="courseContainerHeader">Courses:</h3>
                        <div id="courseContainer" class="courseContainer"></div>
                    </div>


                    <div class="userfield">
                        <input class="button" type="submit" id="submitAccess" value="Grant Access">
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