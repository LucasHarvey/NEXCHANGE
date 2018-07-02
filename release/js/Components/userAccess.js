/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.useraccess = {
    
    clearForm: function(){
        document.getElementById('studentId').value = "";
        document.getElementById("role").selectedIndex = 0;

        document.getElementById("yearExpiry").value = new Date().getFullYear();
        document.getElementById("seasonExpiry").selectedIndex = app.useraccess.getDefaultSeason();
        
        let courseContainer = document.getElementById("courseContainer");
        while (courseContainer.firstChild) {
            courseContainer.removeChild(courseContainer.firstChild);
        }
        
        // Clear the search results
        document.getElementById('resultsTray').style.display = 'none';
        
        document.getElementById("results").style.display = "none";
        
        let resultsTable = document.getElementById("results");
        while (resultsTable.rows.length > 1) {
            resultsTable.deleteRow(1);
        }
        
        var noResultsTray = document.getElementById("noResults");
        noResultsTray.style.display = "none";
        
        // Remove the article if present
        var noResultChildren = noResultsTray.children;
        for(var i=0; i<noResultChildren.length; i++){
            noResultsTray.removeChild(noResultChildren[i]);
        }
        
        document.getElementById("noResults").innerHTML = "";
    },

    userAccessSuccess: function(response) {
        
        // Enable the form
        document.getElementById("submitAccess").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.useraccess.submitUserAccessRequest);
        
        let courses = response.payload.courses;
        let previousAccess= response.payload.previousAccess;

        if (courses.length == 0 && previousAccess.length == 0) {
            new Modal("Error", MessageCode("UserAccessNotUpdated"), null, {
                text: "Okay"
            }).show();
            return;
        }

        let role = response.payload.role == "NOTETAKER" ? "take notes" : "receive notes";
        let secondaryRole = response.payload.role == "NOTETAKER" ? "receive notes" : "take notes";

        let modalContent = "";
        if (courses.length > 0) {
            modalContent += "User has been granted access to <span>" + role + "</span> in the following course".pluralize(courses.length) + ": " +
                "<ul>";

            for (var i = 0; i < courses.length; i++) {
                var course = courses[i];
                var section = course.section.sectionify(true);
                let name = course.courseName + " (" + course.courseNumber + " " + section[0] + " " + section[1].nescape() + ")";
                modalContent += "<li>" + name + "</li>";
            }

            modalContent += "</ul>";
        }
        
        var accessArrays = [[],[]];
        
        for (var i = 0; i < previousAccess.length; i++) {
            if(previousAccess[i].role == response.payload.role){
                accessArrays[0].push(previousAccess[i]);
            } else {
                accessArrays[1].push(previousAccess[i]);
            }
        }
        
        if(accessArrays[0].length > 0){
            var firstArray = accessArrays[0];
                modalContent += "User already has access to <span>" + role + "</span> in the following course".pluralize(firstArray.length) + ": " +
                "<ul>";
            for (var i = 0; i < firstArray.length; i++) {
                    var course = firstArray[i];
                    var section = course.section.sectionify(true);
                    let name = course.courseName + " (" + course.courseNumber + " " + section[0] + " " + section[1].nescape() + ")";
                    modalContent += "<li>" + name + "</li>";
            }
            
            modalContent += "</ul>";
        }
        
        if(accessArrays[1].length > 0){
            var secondArray = accessArrays[1];
                modalContent += "User already has access to <span>" + secondaryRole + "</span> in the following course".pluralize(secondArray.length) + ": " +
                "<ul>";
            for (var i = 0; i < secondArray.length; i++) {
                    var course = secondArray[i];
                    var section = course.section.sectionify(true);
                    let name = course.courseName + " (" + course.courseNumber + " " + section[0] + " " + section[1].nescape() + ")";
                    modalContent += "<li>" + name + "</li>";
            }
            
            modalContent += "</ul>";
        }

        new Modal("User Access Updated", modalContent, null, {
            text: "Okay"
        }).show();
        
        app.useraccess.clearForm();
    },
    
    userAccessFailure: function(response){
        // Enable the form
        document.getElementById("submitAccess").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.useraccess.submitUserAccessRequest);
        
        app.handleFailure(response);
    },

    submitUserAccessRequest: function(event) {
        event.preventDefault();

        let studentId = document.getElementById('studentId').value;
        if (!this.verifyUserId(studentId)) {
            app.handleFailure({
                messageCode: "UserIdNotValid"
            });
            return;
        }

        let role = document.getElementById("role").value;
        if (!role) {
            app.handleFailure({
                messageCode: "MissingArgumentRole"
            });
            return;
        }

        let seasonExpirySelector = document.getElementById("seasonExpiry");
        let yearExpiryInput = document.getElementById("yearExpiry");
        var yearExpiry = yearExpiryInput.value;
        var seasonExpiry = seasonExpirySelector.value;
        var formattedExpiryDate = "";

        if (!yearExpiry) {
            new Modal("Error", MessageCode("MissingArgumentYearExpiry"), null, {
                text: "Okay"
            }).show();
            return;
        }

        if (!seasonExpiry) {
            new Modal("Error", MessageCode("MissingArgumentSeasonExpiry"), null, {
                text: "Okay"
            }).show();
            return;
        }

        if (isNaN(yearExpiry) || yearExpiry % 1 != 0 || yearExpiry<0) {
            new Modal("Error", yearExpiry + " is not a valid year.", null, {
                text: "Okay"
            }).show();
            return;
        }

        if (!app.dateFormatting.semesterFormatVerification(seasonExpiry, yearExpiry)) {
            new Modal("Error", seasonExpirySelector.innerText + " " + yearExpiry + " is not a valid semester.", null, {
                text: "Okay"
            }).show();
            return;
        }

        // Transform season + year into date
        var formattedExpiryDate = app.dateFormatting.formatExpiryDate(seasonExpiry, yearExpiry);


        var isValidExpiryDate = app.dateFormatting.validateExpiryDate(formattedExpiryDate);
        if (!isValidExpiryDate) {
            app.handleFailure({
                messageCode: "PastSemester",
                status: 400
            });
            return;
        }

        let coursesId = [];
        let courseContainer = document.getElementById("courseContainer");
        let courses = courseContainer.children;

        if (courses.length == 0) {
            app.handleFailure({ messageCode: "MissingArgumentCourses" });
            return;
        }

        for (var i = 0; i < courses.length; i++) {
            coursesId.push(courses[i].id);
        }
        
        // Disable the form
        document.getElementById("submitAccess").disabled = true;
        document.getElementById('userData').removeEventListener('submit', app.useraccess.submitUserAccessRequest);

        Resources.UserAccess.POST(studentId, coursesId, role, formattedExpiryDate, this.userAccessSuccess, this.userAccessFailure);
    },

    verifyUserId: function(userId) {
        return (userId.length == 7 && !isNaN(userId) && userId);
    },

    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month >= 11 || (month == 0 && today < 15)) return 2; //intersession
        if (month >= 0 && month < 5) return 3; //winter
        if (month >= 5 && month < 8) return 4; //summer
        return 1; //fall
    }
};

app.startup.push(function userAccessStartup() {
    app.useraccess.submitUserAccessRequest = app.useraccess.submitUserAccessRequest.bind(app.useraccess);
    
    document.getElementById('userData').addEventListener('submit', app.useraccess.submitUserAccessRequest);
    document.getElementById("yearExpiry").value = new Date().getFullYear();
    document.getElementById("seasonExpiry").selectedIndex = app.useraccess.getDefaultSeason();
    let signupLoginId = app.getStore("userAccessLoginId");
    if (signupLoginId) {
        app.store("userAccessLoginId", null);
        document.getElementById("studentId").value = signupLoginId;
    }
});

