/* global Resources,MessageCode,Modal,getQueryParameterByName,location,sectionVerification */
var app = app || {
    startup: [],
    afterStartup: []
};

app.editCourse = {
    
    courseId: undefined, //ID of currently editing course
    originalCourse: undefined, //Original course data
    
    courseSuccess: function(data){
        let course = data.payload.course;
        app.editCourse.originalCourse = course;
        
        // Update the course input fields to match the original course:
        document.getElementById('courseName').value = app.editCourse.originalCourse.courseName;
        document.getElementById("courseNumber").value = app.editCourse.originalCourse.courseNumber;
        document.getElementById("section").value = app.editCourse.originalCourse.section.sectionify(true)[1];
        document.getElementById("teacherFullName").value = app.editCourse.originalCourse.teacherFullName;
        
        var season = app.editCourse.originalCourse.semester[0];
        var index = 0;
        switch (season){
            case "F": 
                index = 0;
                break;
            case "I": 
                index = 1;
                break;
            case "W": 
                index = 2;
                break;
            default: 
                index = 3;
                    break;
        }
        
        document.getElementById("season").selectedIndex = index;
        document.getElementById("year").value = app.editCourse.originalCourse.semester.substring(1);
    },
    
    successCourseEdit: function(data) {
        
        // Enable the form
        document.getElementById("submit").disabled = false;
        document.getElementById("editCourse").addEventListener("submit", app.editCourse.submitCourseEdit);
        
        // Update the course data
        let course = data.payload;
        app.editCourse.originalCourse = course;

        // Update the course input fields 
        document.getElementById('courseName').value = data.payload.courseName;
        document.getElementById("courseNumber").value = data.payload.courseNumber;
        document.getElementById("section").value = data.payload.section;
        document.getElementById("teacherFullName").value = data.payload.teacherFullName;
        
        var season = data.payload.semester[0];
        var index = 0;
        switch (season){
            case "F": 
                index = 0;
                break;
            case "I": 
                index = 1;
                break;
            case "W": 
                index = 2;
                break;
            default: 
                index = 3;
                break;
        }
        
        document.getElementById("season").selectedIndex = index;
        document.getElementById("year").value = data.payload.semester.substring(1);
        
        new Modal("Course Updated", MessageCode("CourseEdited"), {
            text: "Back To Home Page",
            callback: function() {
                location.assign("./signup");
            }
        }, null, "Okay").show();
        
    },
    
    failureCourseEdit: function(data){
        
        // Enable the form
        document.getElementById("submit").disabled = false;
        document.getElementById("editCourse").addEventListener("submit", app.editCourse.submitCourseEdit);
        
        app.handleFailure(data);
    },

    submitCourseEdit: function(event) {
        event.preventDefault();

        let newCourseName = document.getElementById('courseName').value;
        let newCourseNumber = document.getElementById("courseNumber").value;
        let newSection = document.getElementById("section").value;
        let newTeacherFullName = document.getElementById("teacherFullName").value;
        let seasonSelector = document.getElementById("season");
        var newSeason = seasonSelector.value;
        var newYear = document.getElementById("year").value;
        var newFormattedSemester = "";
        var thisYear = new Date().getFullYear();
        
        if(!newCourseName){
            new Modal("Error", MessageCode("MissingArgumentCourseName"), null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!newCourseNumber){
            new Modal("Error", MessageCode("MissingArgumentCourseNumber"), null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!newSection){
            new Modal("Error", MessageCode("MissingArgumentSection"), null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!newTeacherFullName){
            new Modal("Error", MessageCode("MissingArgumentTeacher"), null, {
                    text: "Okay"
                }).show();
            return;
        }
        
        if(!newSeason){
            new Modal("Error", MessageCode("MissingArgumentSeason"), null, {
                text: "Okay"
            }).show();
            return;
        }
        
        if(!newYear){
            new Modal("Error", MessageCode("MissingArgumentYear"), null, {
                text: "Okay"
            }).show();
            return;
        }

        if (isNaN(newYear) || newYear % 1 != 0 || newYear<0) {
            new Modal("Error", newYear + " is not a valid year.", null, {
                text: "Okay"
            }).show();
            return;
        }

        if (!app.dateFormatting.semesterFormatVerification(newSeason, newYear)) {
            new Modal("Error", seasonSelector.innerText + " " + newYear + " is not a valid semester.", null, {
                text: "Okay"
            }).show();
            return;
        }
        
        if (!sectionVerification(newSection)){
            new Modal("Error", newSection + " is not a valid section code. Non-continuous sections seperated by commas (,) and continuous sections seperated by hyphens (-). Example: Section '66,68,71-75,91' would mean sections 66, 68 and sections 71 through 75, followed by 91.", null, {
                text: "Okay"
            }).show();
            return;
        }

        // Format the semester correctly
        newFormattedSemester = newSeason + newYear;
        
        var changes = {};
        if (newCourseName != this.originalCourse.courseName)
            changes.courseName = newCourseName;
        if (newCourseNumber != this.originalCourse.courseNumber)
            changes.courseNumber = newCourseNumber;
        if (newSection != this.originalCourse.section)
            changes.section = newSection;
        if(newTeacherFullName != this.originalCourse.teacherFullName)
            changes.teacherFullName = newTeacherFullName;
        if(newFormattedSemester != this.originalCourse.semester)
            changes.semester = newFormattedSemester;
            
        // Disable the form
        document.getElementById("submit").disabled = true;
        document.getElementById("editCourse").removeEventListener("submit", app.editCourse.submitCourseEdit);

        if (changes != {}) {
            Resources.Courses.PUT(app.editCourse.courseId, changes.teacherFullName, changes.courseName, changes.courseNumber, changes.section, changes.semester, this.successCourseEdit, this.failureCourseEdit);
        } else {
            new Modal("No Changes", MessageCode("NoChangesToMake"), null, null, "Okay").show();
        }
    }


};

app.startup.push(function editCourseStartup(){
    app.editCourse.submitCourseEdit = app.editCourse.submitCourseEdit.bind(app.editCourse);
    
    document.getElementById("editCourse").addEventListener("submit", app.editCourse.submitCourseEdit);
});

app.afterStartup.push(function editCourseAfterStartup() {
    let courseId = getQueryParameterByName("courseId");
    if(courseId){
        app.editCourse.courseId = courseId;
        Resources.Courses.GET(courseId, app.editCourse.courseSuccess)
    } else {
        location.assign("./signup");
    }
});

