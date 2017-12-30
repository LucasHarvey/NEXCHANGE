/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.useraccess = {
    prepopulateCourses: function(data) {
        let courseContainer = document.getElementById("courseContainer");
        var course = document.createElement("p");
        var courseName = data.payload.course.courseName;
        var courseNumber = data.payload.course.courseNumber;
        var courseId = data.payload.course.id;
        course.innerText = courseName + " - " + courseNumber;
        course.id = courseId;

        var removeButton = document.createElement("BUTTON");
        removeButton.className = "removeButton";
        removeButton.type = "button";
        removeButton.innerText = "X";
        removeButton.onclick = app.postCourseSearch.removeCourse;

        course.appendChild(removeButton);
        courseContainer.appendChild(course);
    },

    userAccessSuccess: function(response) {
        
        // Enable the form
        document.getElementById("submitAccess").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.useraccess.submitUserAccessRequest.bind(app.useraccess));
        
        let courses = response.payload.courses;
        let previousAccess= response.payload.previousAccess;

        if (courses.length == 0 && previousAccess.length == 0) {
            new Modal("Error", MessageCode["UserAccessNotUpdated"], null, {
                text: "Okay"
            }).show();
            return;
        }

        let role = response.payload.role == "NOTETAKER" ? "take notes" : "receive notes";

        let modalContent = "";
        if (courses.length > 0) {
            modalContent += "User has been granted access to <span>" + role + "</span> in the following course".pluralize(courses.length) + ": " +
                "<ul>";

            for (var i = 0; i < courses.length; i++) {
                var course = courses[i];
                let name = course.courseName + " (" + course.courseNumber + " section " + (course.section + "").padStart(5, "0") + ")";
                modalContent += "<li>" + name + "</li>";
            }

            modalContent += "</ul>";
        }
        
        if(previousAccess.length > 0){
            modalContent += "User already has access to <span>" + role + "</span> in the following course".pluralize(previousAccess.length) + ": " +
                "<ul>";

            for (var i = 0; i < previousAccess.length; i++) {
                var course = previousAccess[i];
                let name = course.courseName + " (" + course.courseNumber + " section " + (course.section + "").padStart(5, "0") + ")";
                modalContent += "<li>" + name + "</li>";
            }

            modalContent += "</ul>";
        }

        new Modal("User Access Updated", modalContent, null, {
            text: "Okay"
        }).show();
    },
    
    userAccessFailure: function(response){
        // Enable the form
        document.getElementById("submitAccess").disabled = false;
        document.getElementById('userData').addEventListener('submit', app.useraccess.submitUserAccessRequest.bind(app.useraccess));
        
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
            new Modal("Error", MessageCode["MissingArgumentYearExpiry"], null, {
                text: "Okay"
            }).show();
            return;
        }

        if (!seasonExpiry) {
            new Modal("Error", MessageCode["MissingArgumentSeasonExpiry"], null, {
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
        document.getElementById('userData').removeEventListener('submit', app.useraccess.submitUserAccessRequest.bind(app.useraccess));

        Resources.UserAccess.POST(studentId, coursesId, role, formattedExpiryDate, this.userAccessSuccess, this.userAccessFailure);
    },

    verifyUserId: function(userId) {
        return (userId.length == 7 && !isNaN(userId) && userId);
    },

    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month == 11 || (month == 0 && today<15)) return 1; //intersession
        if (month >= 0 && month < 5) return 2; //winter
        if (month >= 5 && month < 8) return 3; //summer
        return 0; //fall

    }

};

app.startup.push(function userAccessStartup() {
    document.getElementById('userData').addEventListener('submit', app.useraccess.submitUserAccessRequest.bind(app.useraccess));
    document.getElementById("yearExpiry").value = new Date().getFullYear();
    document.getElementById("seasonExpiry").selectedIndex = app.useraccess.getDefaultSeason();
    let signupLoginId = app.getStore("userAccessLoginId");
    if (signupLoginId) {
        app.store("userAccessLoginId", null);
        document.getElementById("studentId").value = signupLoginId;
    }
});

app.afterStartup.push(function userAccessAfterStartup() {
    let courseId = app.getStore("grantAccessCourseId");
    if (courseId) {
        app.store("grantAccessCourseId", null);
        Resources.Courses.GET(courseId, app.useraccess.prepopulateCourses);
    }
});
