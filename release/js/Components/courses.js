/* global Resources,MessageCode,location */
var app = app || {
    startup: [],
    afterStartup: []
};

app.courses = {
    
    notificationsToggledSuccess: function(data) {
        var courseId = data.courseId || data.id;
        var tn = document.getElementById("togglenotifications_" + courseId);
        if (tn) //Notetakers dont have this.
            tn.checked = data.notifications == 1;
    },
    notificationsToggledFailed: function(data) {
        var inputElem = document.getElementById("togglenotifications_" + data.payload.courseId);
        inputElem.value = !inputElem.value;
        app.handleFailure(data);
    },
    
    __generateCourseHeader: function(courseData) {

        var course = document.createElement("ARTICLE");
        var courseHeader = document.createElement("HEADER");
        var courseHeaderText = document.createElement("SPAN");
        courseHeader.className = "courseTitle";
        courseHeaderText.innerText = courseData.courseName 
        courseHeaderText.className = "courseName";
        courseHeader.appendChild(courseHeaderText);
        course.appendChild(courseHeader);

        let courseInfo = document.createElement("SECTION");
        course.appendChild(courseInfo);
        
        let semesterP = document.createElement("P");
        semesterP.innerHTML = "Semester: " + app.dateFormatting.formatSemester(courseData.semester);
        courseInfo.appendChild(semesterP);
        
        let courseCodeP = document.createElement("P");
        courseCodeP.innerHTML = "Course Number: " + courseData.courseNumber;
        courseInfo.appendChild(courseCodeP);
        
        var sectionText = courseData.section.sectionify(true);
        
        let sectionP = document.createElement("P");
        sectionP.innerHTML = sectionText[0]+": "+sectionText[1];
        courseInfo.appendChild(sectionP);
        
        let teacherP = document.createElement("P");
        teacherP.innerHTML = "Teacher: " + courseData.teacherFullName;
        courseInfo.appendChild(teacherP);

        if (courseData.role == 'NOTETAKER') {
            var uploadNoteButton = document.createElement("BUTTON");
            uploadNoteButton.className = "button courseUploadButton";
            uploadNoteButton.innerHTML = "Upload Notes for " + courseData.courseName;
            uploadNoteButton.id = "upload_" + courseData.id;
            uploadNoteButton.onclick = function(e) {
                var id = e.target.id.replace("upload_", "");
                app.store("uploadNotesCourseId", id);
                location.assign("./upload");
            };
            course.appendChild(uploadNoteButton);
        } else {
            
            var toggleNotifications = document.createElement("label");
            toggleNotifications.className = "switch";
            var toggleNotifications_check = document.createElement("INPUT");
            toggleNotifications_check.type = "checkbox";
            toggleNotifications_check.id = "togglenotifications_" + courseData.id;
            toggleNotifications.appendChild(toggleNotifications_check);
            var toggleNotifications_span = document.createElement("SPAN");
            toggleNotifications_span.className = "slider round";
            toggleNotifications.appendChild(toggleNotifications_span);

            var toggleText = document.createElement("label");
            toggleText.className = "notificationSpan";
            toggleText.htmlFor = toggleNotifications_check.id;
            toggleText.innerHTML = "Notify Me";

            var div = document.createElement("DIV");
            div.className = "notificationWrapper";
            div.appendChild(toggleText);
            div.appendChild(toggleNotifications);

            toggleNotifications_check.onchange = function(e) {
                e.preventDefault();
                var courseId = e.target.id.replace("togglenotifications_", "");
                Resources.UserAccess.PUT(e.target.checked, courseId, function(data) { app.courses.notificationsToggledSuccess(data.payload) }, app.courses.notificationsToggledFailed);
            };

            courseHeader.appendChild(div);
        }

        let courseContainer = document.getElementById("courseContainer");
        courseContainer.appendChild(course);
    },

    __generateEmptyCourseHeader: function() {
        let article = document.createElement("ARTICLE");
        let articleHeader = document.createElement("HEADER");
        articleHeader.innerHTML = "<span class='title'>No Courses</span>";
        article.appendChild(articleHeader);

        let articleSection = document.createElement("SECTION");
        article.appendChild(articleSection);

        let descriptionP = document.createElement("P");
        descriptionP.innerHTML = "You do not have access to any courses. Please visit the Access Centre to request access.";
        descriptionP.className = "description";
        articleSection.appendChild(descriptionP);

        let courseContainer = document.getElementById("courseContainer");
        courseContainer.appendChild(article);
    },
    getCourses: function() {
        var courseContainer = document.getElementById("courseContainer");
        while (courseContainer.firstChild) courseContainer.removeChild(courseContainer.firstChild);

        Resources.UserCourses.GET(app.courses.coursesSuccess);
    },
    coursesSuccess: function(data) {
        var courses = data.payload.courses;
        if (courses.length == 0) {
            app.courses.__generateEmptyCourseHeader();
            return;
        }
        for (var i = 0; i < courses.length; i++) {
            app.courses.__generateCourseHeader(courses[i]);
            app.courses.notificationsToggledSuccess(courses[i]);
        }
        
    }
}

app.afterStartup.push(function coursesAfterStartup() {
    app.courses.getCourses();
});
