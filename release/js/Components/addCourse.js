/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.addCourse = {

    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month >= 11) return 2; //intersession
        if (month >= 0 && month < 5) return 3; //winter
        if (month >= 5 && month < 8) return 4; //summer
        return 1; //fall
    },
    
    submitCourseSuccess: function(data) {
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addCourse').addEventListener('submit', app.addCourse.submitCourse);

        // Empty the course input fields: 
        document.getElementById('courseName').value = "";
        document.getElementById("courseNumber").value = "";
        document.getElementById("section").value = "";
        document.getElementById("teacherFullName").value = "";
        document.getElementById("season").selectedIndex = app.addCourse.getDefaultSeason();
        document.getElementById("year").value = new Date().getFullYear();
        
        new Modal("Course Added", MessageCode["CourseCreated"], null, {
                    text: "Okay"
                }).show();
    },
    
    submitCourseFailure: function(data){
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addCourse').addEventListener('submit', app.addCourse.submitCourse);
        app.handleFailure(data);
    },

    submitCourse: function(event) {
        event.preventDefault();

        let courseName = document.getElementById('courseName').value;
        let courseNumber = document.getElementById("courseNumber").value;
        let section = document.getElementById("section").value;
        let teacherFullName = document.getElementById("teacherFullName").value;
        let seasonSelector = document.getElementById("season");
        var season = seasonSelector.value;
        var year = document.getElementById("year").value;
        var formattedSemester = "";
        var thisYear = new Date().getFullYear();
        
        if(!courseName){
            new Modal("Error", MessageCode["MissingArgumentCourseName"], null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!courseNumber){
            new Modal("Error", MessageCode["MissingArgumentCourseNumber"], null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!section){
            new Modal("Error", MessageCode["MissingArgumentSection"], null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!teacherFullName){
            new Modal("Error", MessageCode["MissingArgumentTeacher"], null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!season){
            new Modal("Error", MessageCode["MissingArgumentSeason"], null, {
                text: "Okay"
            }).show();
            return;
        }
        
        if(!year){
            new Modal("Error", MessageCode["MissingArgumentYear"], null, {
                text: "Okay"
            }).show();
            return;
        }

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

        // Format the semester correctly
        formattedSemester = season + year;
        
        // Disable the form
        document.getElementById('submit').disabled = true;
        document.getElementById('addCourse').removeEventListener('submit', app.addCourse.submitCourse);
        

        Resources.Courses.POST(teacherFullName, courseName, courseNumber, section, formattedSemester, this.submitCourseSuccess, this.submitCourseFailure);
    }


};

app.startup.push(function addCourseStartup() {
    app.addCourse.submitCourse = app.addCourse.submitCourse.bind(app.addCourse);
    
    document.getElementById('addCourse').addEventListener('submit', app.addCourse.submitCourse);
    
    document.getElementById("year").value = new Date().getFullYear();
    document.getElementById("season").selectedIndex = app.addCourse.getDefaultSeason();

});
