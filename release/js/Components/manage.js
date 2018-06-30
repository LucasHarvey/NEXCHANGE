/* global Resources,MessageCode,Modal,generateDeleteConfirmationModal,debounce,location,generatePTag */
var app = app || {
    startup: [],
    afterStartup: []
};

app.manage = {
    pagesLoaded: 0,
    paginationEnd: false,
    
    _generateArticle: function(numButtons) {
        if(!numButtons) numButtons = 2;
        let article = document.createElement("ARTICLE");

        let articleHeader = document.createElement("HEADER");
        let articleHeaderp = document.createElement("p");
        articleHeaderp.className = "title";
        articleHeader.appendChild(articleHeaderp);
        article.appendChild(articleHeader);

        let articleSection = document.createElement("SECTION");
        article.appendChild(articleSection);

        let descriptionP = document.createElement("P");
        descriptionP.className = "description";
        articleSection.appendChild(descriptionP);
        
        let buttonSection = document.createElement("SECTION");
        buttonSection.className = "buttonfield";
        articleSection.appendChild(buttonSection);
        
        var articleObject = {
            section: articleSection,
            article: article,
            header: articleHeaderp,
            description: descriptionP
        }
        
        for(var i = 0; i<numButtons; i++){
            let moreInfoBtn = document.createElement("BUTTON");
            buttonSection.appendChild(moreInfoBtn);
            var name = "button";
            if(i != 0){
                name += i+1;
            }
            articleObject[name] = moreInfoBtn;
        };
        
        return articleObject;
    },
    _generateUsers: function(container, users) {
        if (users.length == 0) {
            app.manage.noResults(container, "Users");
            return;
        }
        
        // Only increment pagesLoaded if there are results
        app.manage.pagesLoaded++;
        
        for (var i = 0; i < users.length; i++) {
            let user = users[i];
            let article = this._generateArticle();
            article.article.id = "UA_A_" + user.id;
            article.header.innerText = user.firstName + " " + user.lastName;
            
            article.description.appendChild(generatePTag("Student ID", user.studentId));
            article.description.appendChild(generatePTag("Email", user.email));
            if(user.isNoteTaker) article.description.appendChild(generatePTag("Author of", user.notesAuthored + " note".pluralize(user.notesAuthored)));
            article.description.appendChild(generatePTag("User Created On", (new Date(user.created.replace(/-/g, '\/'))).toPrettyDate()));
            
            article.button.innerHTML = "View Student Notes";
            article.button.id = "UA" + i + "_" + user.id;
            article.button.onclick = function() {
                var studentId = this.id.split("_")[1];
                location.assign("./notes?studentId=" + studentId);
            };
            article.button2.innerHTML = "Delete Account";
            article.button2.className = "warning";
            article.button2.id = "UA" + i + "_" + user.id;
            article.button2.dataset.studentId = user.studentId;
            article.button2.onclick = function() {
                var studentId = this.dataset.studentId;
                var button2 = this;
                new Modal("Delete Account",
                    "Are you sure you want to delete the user account for Student ID: " + studentId + " ?" +
                    "<br>This <b>CANNOT</b> be undone." +
                    "<br>Confirm Admin password: <input type='password' placeholder='Password' autocomplete='false' id='manage_deleteAccountPw'><p class='error'></p>", {
                        text: "Yes, DELETE Account",
                        callback: function() {
                            app.manage._deleteAccount.call(this, studentId, button2);
                        }
                    }
                ).show();
            };
            container.appendChild(article.article);
        }
    },
    _deleteAccount: function(studentId, submitButton) {
        var pass = document.getElementById("manage_deleteAccountPw").value;
        if (!pass) {
            document.getElementById("manage_deleteAccountPw").nextSibling.innerHTML = "Please enter password.";
            return;
        }
        // Disable the "Delete account" button
        submitButton.disabled = true;
        
        var that = this;
        Resources.Users.DELETE(studentId, pass, function(data) {
            that.hide();
            new Modal("Account Deleted", "User Account with Student ID: " + data.payload.studentId + " has been deleted successfully.", null, null, "Okay").show();
            var article = document.getElementById("UA_A_" + data.payload.userId);
            article.parentElement.removeChild(article);
        }, function(data){
            //Failure function
            if(data.messageCode == "AuthenticationFailed"){
                // Enable the submit button
                submitButton.disabled = false;
                
                document.getElementById("manage_deleteAccountPw").nextSibling.innerHTML = "Incorrect Password.";
                return;
            }
            that.hide();
            app.manage._deleteAccountFailure;
        });
    },
    
    _deleteAccountFailure: function(data){
        new Modal("Not Authorized", MessageCode(data.messageCode), null, null, "Okay").show();
    },
    _generateCourses: function(container, courses) {
        if (courses.length == 0) {
            app.manage.noResults(container, "Courses");
            return;
        }
        
        // Only increment pagesLoaded if there are results
        app.manage.pagesLoaded++;
        
        for (var i = 0; i < courses.length; i++) {
            let course = courses[i];
            let article = this._generateArticle(4);
            article.article.id = "UA_C_" + course.id;
            article.header.innerText = course.courseName;
            
            var section =  course.section.sectionify(true);
            
            article.description.appendChild(generatePTag("Teacher", course.teacherFullName));
            article.description.appendChild(generatePTag("Course", course.courseNumber));
            article.description.appendChild(generatePTag(section[0], section[1]));
            article.description.appendChild(generatePTag("Semester", course.semester));
            article.description.appendChild(generatePTag("Contains", course.notesAuthored + " note".pluralize(course.notesAuthored)));

            article.button.innerHTML = "View Course Notes";
            article.button.id = "UA" + i + "_" + course.id;
            article.button.onclick = function() {
                var courseId = this.id.split("_")[1];
                location.assign("./notes?courseId=" + courseId);
            };
            article.button2.innerHTML = "Grant User Access";
            article.button2.id = "Access" + i + "_" + course.id;
            article.button2.onclick = function() {
                var courseId = this.id.split("_")[1];
                app.store("grantAccessCourseId", courseId);
                location.assign("./userAccess");
            };
            
            article.button3.innerHTML = "Modify Course";
            article.button3.id = "Modify" + i + "_" + course.id;
            article.button3.onclick = function(){
                var courseId = this.id.split("_")[1];
                location.assign("./editCourse?courseId=" + courseId);
            };
            
            article.button4.innerHTML = "Delete Course";
            article.button4.id = "Delete" + i + "_" + course.id;
            article.button4.className = "warning";
            article.button4.dataset.courseName = course.courseName;
            article.button4.onclick = function() {
                var courseName = this.dataset.courseName;
                var courseId = this.id.split("_")[1];
                var button4 = this;
                new Modal("Delete Course",
                    "Are you sure you want to delete the course named: " + courseName.nescape() + " ?" +
                    "<br>This <b>CANNOT</b> be undone." +
                    "<br>Confirm Admin password: <input type='password' placeholder='Password' autocomplete='false' id='manage_deleteCoursePw'><p></p>", {
                        text: "Yes, DELETE Course",
                        callback: function() {
                            app.manage._deleteCourse.call(this, courseId, button4);
                        }
                    }
                ).show();
            };
            
            container.appendChild(article.article);
        }
    },
    
    _deleteCourse: function(courseId, submitButton) {
        var pass = document.getElementById("manage_deleteCoursePw").value;
        if (!pass) {
            document.getElementById("manage_deleteCoursePw").nextSibling.innerHTML = "Please enter password.";
            return;
        }
        
        // Disable the "Delete course" button
        submitButton.disabled = true;
        
        var that = this;
        Resources.Courses.DELETE(courseId, pass, function(data) {
            that.hide();
            
            let course = data.payload.course;
            
            var section = course.section.sectionify(true);
            
            new Modal("Course Deleted", course.courseName.nescape() + " ("+section[0]+" " + section[1].nescape() + ") has been deleted successfully.", null, null, "Okay").show();
            
            var article = document.getElementById("UA_C_" + course.id);
            article.parentElement.removeChild(article);
        }, function(data){
            //Failure function
            if(data.messageCode == "AuthenticationFailed"){
                // Enable the submit button
                submitButton.disabled = false;
                
                document.getElementById("manage_deleteCoursePw").nextSibling.innerHTML = "Incorrect Password.";
                document.getElementById("manage_deleteCoursePw").nextSibling.className = "error";
                return;
            }
            that.hide();
            app.manage._deleteCourseFailure;
        });
    
    },
    
    _deleteCourseFailure: function(data){
        new Modal("Error", MessageCode(data.messageCode), null, null, "Okay").show();
    },
    
    _generateUserAccess: function(container, useraccesses) {
        if (useraccesses.length == 0) {
            app.manage.noResults(container, "User Accesses");
            return;
        }
        
        // Only increment pagesLoaded if there are results
        app.manage.pagesLoaded++;
        
        for (var i = 0; i < useraccesses.length; i++) {
            let ua = useraccesses[i];
            let article = this._generateArticle();
            var role;
            if(ua.role == "NOTETAKER"){
                role = ua.role.toProperCase();
            } else if (ua.role == "STUDENT"){
                role = "Student Receiving Notes";
            }
            article.header.innerText = role + " - " + ua.firstName + " " + ua.lastName;
            var section =  ua.courseSection.sectionify(true);
            
            article.description.appendChild(generatePTag("Course", ua.courseNumber + " (" + ua.courseName + ")"));
            article.description.appendChild(generatePTag(section[0], section[1]));
            article.description.appendChild(generatePTag("Role", role));
            if(ua.role=="NOTETAKER") article.description.appendChild(generatePTag("Author of", ua.notesAuthored + " note".pluralize(ua.notesAuthored) + " (for this class)"));
            article.description.appendChild(generatePTag("Created On", new Date(ua.created.replace(/-/g, '\/')).toPrettyDate()));
            article.description.appendChild(generatePTag("Expires On", new Date(ua.expires_on).toPrettyDate()));

            article.button.innerHTML = "View Notes";
            article.button.id = "UA2" + i + "_" + ua.userId + "_" + ua.courseId;
            article.button.onclick = function(e) {
                var userId = this.id.split("_")[1];
                var courseId = this.id.split("_")[2];
                location.assign("./notes?studentId=" + userId + "&courseId=" + courseId);
            };
            article.button2.innerHTML = "Revoke Access";
            article.button2.className = "warning";
            article.button2.id = "UA" + i + "_" + ua.userId + "_" + ua.courseId;
            article.button2.onclick = function(e) {
                var uid = this.id.split("_")[1];
                var cid = this.id.split("_")[2];
                var cb = function(event) {
                    
                    // Disable the confirm button 
                    event.target.disabled = true;
                    
                    // Delete the user access
                    Resources.UserAccess.DELETE(uid, cid, function() {
                        article.article.parentNode.removeChild(article.article);
                    });
                    this.hide(); //this refers to the confirmation modal

                };
                generateDeleteConfirmationModal("Are you sure you want to delete this user access?", cb).show(); // this refers to the confirm button
            };
            container.appendChild(article.article);
        }
    },
    _getSearchType: function() {
        let searchWhat = document.getElementsByName("searchWhat");
        for (var i = 0; i < searchWhat.length; i++) {
            if (searchWhat[i].checked) {
                return searchWhat[i].value;
            }
        }
        return "student";
    },
    _searchCourse: function() {
        let teacherName = document.getElementById("teacherFullName").value;
        let courseName = document.getElementById("courseName").value;
        let courseNumber = document.getElementById("courseNumber").value;
        let courseSection = document.getElementById("section").value;
        let seasonSelector = document.getElementById("season");
        var season = seasonSelector.value;
        var year = document.getElementById("year").value;
        if (year == "") year = null;
        if (season == "allSemesters") season = null;
        var formattedSemester = "";
        
        if(season && !year){
            new Modal("Error", MessageCode("MissingArgumentYear"), null, {
                    text: "Okay"
                }).show();
                return;
        }

         if (season || year) {

             if (isNaN(year) || year % 1 != 0 || year<0) {
                 new Modal("Error", year + " is not a valid year.", null, {
                     text: "Okay"
                 }).show();
                return;
            }

            if (!app.dateFormatting.semesterFormatVerification(season, year)) {
                new Modal("Error", seasonSelector.innerText + " " + year + " is not a valid semester.", null, {
                    text: "Okay"
                }).show();
                return;
            }

            if (!app.dateFormatting.isPastSemester(season, year)) {
                new Modal("Error", MessageCode("FutureSemester"), null, {
                    text: "Okay"
                }).show();
                return;
            }

            // Format the semester correctly
            formattedSemester = season + year;
        }
        
        app.manage.searchData = {
            type: "course",
            tname: teacherName,
            cname: courseName,
            cnumber: courseNumber,
            sec: courseSection,
            sem: formattedSemester
        };
        
        // Disable the search form
        document.getElementById("searchButton").disabled = true;
        document.getElementById("searchData").removeEventListener('submit', app.manage.search);
        
        Resources.Courses.SEARCH(teacherName, courseName, courseNumber, courseSection, formattedSemester, this.pagesLoaded, this.searchSuccess, this.searchFailure);
    },
    _searchStudent: function() {
        let name = document.getElementById("studentName").value;
        let studentId = document.getElementById("studentId").value;
        
        app.manage.searchData = {
            type: "student",
            name: name,
            studentId: studentId,
        };
        
        // Disable the search form
        document.getElementById("searchButton").disabled = true;
        document.getElementById("searchData").removeEventListener('submit', app.manage.search);

        Resources.Users.SEARCH(name, studentId, this.pagesLoaded, this.searchSuccess, this.searchFailure);
    },
    _searchAccess: function() {
        let courseName = document.getElementById("ua_courseName").value;
        let courseNumber = document.getElementById("ua_courseNumber").value;
        let studentId = document.getElementById("ua_studentId").value;
        
        app.manage.searchData = {
            type: "access",
            studentId: studentId,
            cname: courseName,
            cnumber: courseNumber,
        };
        
        // Disable the search form
        document.getElementById("searchButton").disabled = true;
        document.getElementById("searchData").removeEventListener('submit', app.manage.search);

        Resources.UserAccess.SEARCH(studentId, courseName, courseNumber, this.pagesLoaded, this.searchSuccess, this.searchFailure);
    },
    searchSuccess: function(data) {
        // Enable the search form
        document.getElementById("searchButton").disabled = false;
        document.getElementById("searchData").addEventListener('submit', app.manage.search);
        
        let container = document.getElementById("searchResultContainer");
        
        if (data.payload.users) {
            if(data.payload.users.length == 0){
                app.manage.paginationEnd = true;
            }
            app.manage._generateUsers(container, data.payload.users);
        }
        if (data.payload.courses) {
            if(data.payload.courses.length == 0){
                app.manage.paginationEnd = true;
            }
            app.manage._generateCourses(container, data.payload.courses);
        }
        if (data.payload.useraccesses) {
            if(data.payload.useraccesses.length == 0){
                app.manage.paginationEnd = true;
            }
            app.manage._generateUserAccess(container, data.payload.useraccesses);
        }
        
        // Empty the search fields: 
        document.getElementById('studentName').value = "";
        document.getElementById('studentId').value = "";
        document.getElementById('courseName').value = "";
        document.getElementById("courseNumber").value = "";
        document.getElementById("section").value = "";
        document.getElementById("teacherFullName").value = "";
        document.getElementById("season").selectedIndex = 0;
        var yearInput = document.getElementById("year");
        yearInput.value = "";
        yearInput.placeholder = "All Years";
        yearInput.readOnly = true;
        yearInput.classList.add("noHighlight");
        document.getElementById("ua_courseName").value = "";
        document.getElementById("ua_courseNumber").value = "";
        document.getElementById("ua_studentId").value = "";
    },
    
    searchFailure: function(response){
        // Enable the search form
        document.getElementById("searchButton").disabled = false;
        document.getElementById("searchData").addEventListener('submit', app.manage.search);
        
        app.handleFailure(response);
    },
    
    
    //DO NOT USE FOR RESOURCES
    noResults : function(container, searchType) {
        //Used to indicate NO courses/useraccesses/students. not for errors. Errors uses modals.
        if(app.manage.pagesLoaded != 0){
            return;
        }
        let article = app.manage._generateArticle();
        article.header.innerText = "No " + searchType + " Found";
        article.description.innerHTML = "<p>There are no search results for " + searchType + "</p>";
        article.button.parentNode.removeChild(article.button);
        article.button2.parentNode.removeChild(article.button2);
        container.appendChild(article.article);
    },
    search: function(e) {
        e.preventDefault();
        
        let container = document.getElementById("searchResultContainer");
        container.innerHTML = "";
        
        this.pagesLoaded = 0;
        this.paginationEnd = false;
        this.submitSearch();
    },
    
    submitSearch: function(){
        let searchWhat = app.manage._getSearchType();
        switch (searchWhat) {
            case "student":
                app.manage._searchStudent();
                break;
            case "course":
                app.manage._searchCourse();
                break;
            case "useraccess":
                app.manage._searchAccess();
                break;
        }
    },
    
    scrollSearch: function(event){
        let scrollPosition = (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) / ((document.body.scrollHeight - window.innerHeight) || 1);        
        if(scrollPosition > 0.9 && !app.manage.paginationEnd){
            app.manage.searchPaged(app.manage.searchData);
        }
        
    },
    
    searchPaged: function(data){
        if(!data){
            return;
        }
        switch(data.type){
            case "course": 
                Resources.Courses.SEARCH(data.tname, data.cname, data.cnumber, data.sec, data.sem, this.pagesLoaded, this.searchSuccess, this.searchFailure);
                break;
            case "access":
                Resources.UserAccess.SEARCH(data.studentId, data.cname, data.cnumber, this.pagesLoaded, this.searchSuccess, this.searchFailure);
                break;
            case "student":
                Resources.Users.SEARCH(data.name, data.studentId, this.pagesLoaded, this.searchSuccess, this.searchFailure);
                break;
            default:
                break;
        }
    },
    
    toggleSearchFields: function() {
        app.manage.pagesLoaded = 0;
        app.manage.paginationEnd = false;
        let searchOptions = document.getElementsByName("searchWhat");
        for (var i = 0; i < searchOptions.length; i++) {
            var inputRows = document.getElementsByClassName("searchWhat_" + searchOptions[i].value);
            if (searchOptions[i].checked) {
                for (var x = 0; x < inputRows.length; x++) {
                    inputRows[x].style.display = "";
                }
                continue;
            }
            for (var x = 0; x < inputRows.length; x++) {
                inputRows[x].style.display = "none";
            }
        }
    },

    updateYearInput: function() {
        let seasonSelector = document.getElementById("season");
        let yearInput = document.getElementById("year");
        
        // Set the yearInput to "" if "All semesters" is selected
        if (seasonSelector.selectedIndex == 0) {
            yearInput.placeholder = "All Years";
            yearInput.value = "";
            yearInput.readOnly = true;
            yearInput.classList.add("noHighlight");
        } else if (!yearInput.value) {
            yearInput.readOnly = false;
            yearInput.value = "";
            yearInput.placeholder = "Please enter a year";
            yearInput.classList.remove("noHighlight");
        }

    },
    
    downloadStats: function(e){
        this.disabled = true;
        let id = this.id;
        let xsrf = app.getCookie("xsrfToken");
        
        let url = "./v1/Admin/download.php?type=" + id.slice(15, id.length) + "&xsrfToken=" + xsrf;
        var newWin = window.open(url, '_blank');  
        
        this.disabled = false;
        if(!newWin || newWin.closed || typeof newWin.closed=='undefined') { 
            new Modal("Error", MessageCode("PopUpBlocked"), null, null, "Okay").show();
        }
    }
};

app.startup.push(function manageStartup() {
    app.manage.search = app.manage.search.bind(app.manage);
    
    document.getElementById("searchData").addEventListener('submit', app.manage.search);
    
    let searchWhat = document.getElementsByName("searchWhat");
    for (var i = 0; i < searchWhat.length; i++) {
        searchWhat[i].addEventListener('change', app.manage.toggleSearchFields);
    }
    app.manage.toggleSearchFields();

    document.getElementById("season").addEventListener("change", app.manage.updateYearInput);
    app.manage.updateYearInput();
    
    document.body.onscroll = debounce(app.manage.scrollSearch, 250);
    
    document.getElementById("download_stats_global").onclick = app.manage.downloadStats;
    document.getElementById("download_stats_user").onclick = app.manage.downloadStats;
});
