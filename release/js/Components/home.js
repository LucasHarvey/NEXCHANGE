/* global Resources,MessageCode,Modal,location,Blob,navigator,debounce */
var app = app || {
    startup: [],
    afterStartup: []
};

app.home = {
    pagesLoaded: 0,
    paginationEnd: false,
    
    getSortMethod: function() {

        let sortSelector = document.getElementById("sortDrop");
        var sortBy = sortSelector.value;
        var returnValue = ["created", "DESC"];
        switch (sortBy) {
            case "newestUpload":
                return (["created", "DESC"]);
            case "oldestUpload":
                return (["created", "ASC"]);
            case "newestTakenOn":
                return (["taken_on", "DESC"]);
            case "oldestTakenOn":
                return (["taken_on", "ASC"]);
            case "noteNameAscending":
                return (["noteName", "ASC"]);
            case "noteNameDescending":
                return (["noteName", "DESC"]);
            default:
                return (["created", "DESC"]);
        }
    },
    __toggleCourse: function() {
        var courseHeader = this; //BOUND FUNCTION to courseHeader.
        courseHeader.parentNode.querySelector("img").classList.toggle("rotate");
        var courseId = courseHeader.parentNode.parentNode.id.replace("course_", "");
        var notesContainer = document.getElementById("notes_" + courseId);
        if (notesContainer.style.display == "none")
            notesContainer.style.display = "block";
        else
            notesContainer.style.display = "none";
    },
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
    __generateCourseHeader: function(course) {
        //Course header
        var courseDiv = document.createElement('SECTION');
        courseDiv.className = "course";
        courseDiv.id = "course_" + course.id;

        var courseHeader = document.createElement('HEADER');
        courseHeader.className = "courseHeader";
        var courseHeaderText = document.createElement("DIV");
        courseHeaderText.innerHTML = "<span class='courseHTitle'>" + course.courseName + "</span>" + "<span class='courseHSemester'>(" + app.dateFormatting.formatSemester(course.semester) + ")</span>";
        courseHeader.appendChild(courseHeaderText);
        courseHeaderText.onclick = app.home.__toggleCourse.bind(courseHeaderText);
        var courseHeaderDropDown = document.createElement("IMG");
        courseHeaderDropDown.src = "./img/dropdown_icon.png";
        courseHeaderDropDown.className = "dropdownIcon";
        courseHeaderDropDown.onclick = app.home.__toggleCourse;
        courseDiv.appendChild(courseHeader);
        
        if (course.role == 'NOTETAKER') {
            var uploadNoteButton = document.createElement("BUTTON");
            uploadNoteButton.className = "button courseUploadButton";
            uploadNoteButton.innerText = "Upload Notes for " + course.courseName;
            uploadNoteButton.id = "upload_" + course.id;
            uploadNoteButton.onclick = function(e) {
                var id = e.target.id.replace("upload_", "");
                app.store("uploadNotesCourseId", id);
                location.assign("./upload");
            };
            courseDiv.appendChild(uploadNoteButton);
        } else {
            //As soon as user is student in a course, show hide downloaded field.
            document.getElementById("hideDownloadedField").style.display = "flex";
            var toggleNotifications = document.createElement("label");
            toggleNotifications.className = "switch";
            var toggleNotifications_check = document.createElement("INPUT");
            toggleNotifications_check.type = "checkbox";
            toggleNotifications_check.id = "togglenotifications_" + course.id;
            toggleNotifications.appendChild(toggleNotifications_check);
            var toggleNotifications_span = document.createElement("SPAN");
            toggleNotifications_span.className = "slider round";
            toggleNotifications.appendChild(toggleNotifications_span);
    
            var toggleText = document.createElement("label");
            toggleText.className = "notificationSpan";
            toggleText.htmlFor = toggleNotifications_check.id;
            toggleText.innerText = "Notify Me";
    
            var div = document.createElement("DIV");
            div.className = "notificationWrapper";
            div.appendChild(toggleText);
            div.appendChild(toggleNotifications);
    
            toggleNotifications_check.onchange = function(e) {
                e.preventDefault();
                var courseId = e.target.id.replace("togglenotifications_", "");
                Resources.UserAccess.PUT(e.target.checked, courseId, function(data) { app.home.notificationsToggledSuccess(data.payload) }, app.home.notificationsToggledFailed);
            };
            
            courseHeader.appendChild(div);
        }
        
        courseHeader.appendChild(courseHeaderDropDown);
        
        //The notes portion of the courses
        var notesDiv = document.createElement('DIV');
        notesDiv.id = "notes_" + course.id;
        courseDiv.appendChild(notesDiv);

        let notesContainer = document.getElementById("notesContainer");
        notesContainer.appendChild(courseDiv);
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
        articleSection.appendChild(descriptionP);

        let notesContainer = document.getElementById("notesContainer");
        notesContainer.appendChild(article);
    },

    __populateCourse: function(note) {
        var noteArticle = app.home._generateArticle(note);
        document.getElementById("notes_" + note.courseId).appendChild(noteArticle);
    },

    _generateArticle: function(noteData) {
        let article = document.createElement("ARTICLE");

        let articleHeader = document.createElement("HEADER");
        let articleHeaderSpan = document.createElement("SPAN");
        articleHeaderSpan.className = "title";
        articleHeaderSpan.innerText = noteData.name;
        articleHeader.appendChild(articleHeaderSpan);
        article.appendChild(articleHeader);

        let articleSection = document.createElement("SECTION");
        article.appendChild(articleSection);
        
        if(noteData.description){
            let descriptionP = document.createElement("P");
            descriptionP.innerText = noteData.description;
            articleSection.appendChild(descriptionP);
        }
        
         if(noteData.size){
            var fileSize = parseFloat(noteData.size/1024**2).toFixed(2);
    
            if(fileSize < 1.00){
               fileSize = parseFloat(noteData.size/1024).toFixed(0);
               if(fileSize < 1){
                   fileSize = parseFloat(noteData.size) + " Bytes";
               } else {
                   fileSize += " KB";
               }
            } else {
                fileSize += " MB";
            }

            let sizeP = document.createElement("P");
            sizeP.innerHTML = "File size: <span>" + fileSize + "</span>";;
            articleSection.appendChild(sizeP);
        }
        
         if(noteData.extension){
            let extensionP = document.createElement("P");
            extensionP.innerHTML = "File Extension: <span>" + "." + noteData.extension + "</span>";;
            articleSection.appendChild(extensionP);
        }

        let dateP = document.createElement("P");
        dateP.innerHTML = "Notes taken on: <span>" + new Date(noteData.taken_on.replace(/-/g, '\/')).toPrettyDate() + "</span>";
        articleSection.appendChild(dateP);

        if (noteData.role == "STUDENT") {
            article.className = noteData.lastDownloaded == null ? "newnote" : ""; //Show a halo on the new undownloaded note
            let dateDownloaded = noteData.lastDownloaded == null ? "Never" : new Date(noteData.lastDownloaded.replace(/-/g, '\/')).toPrettyDate(true);
            let dateDP = document.createElement("P");
            dateDP.id = noteData.id + "_downloadDate";
            dateDP.innerHTML = "Downloaded on: <span>" + dateDownloaded + "</span>";
            articleSection.appendChild(dateDP);
        }

        let noteDownload = document.createElement("BUTTON");
        noteDownload.id = noteData.id + "_btn";
        noteDownload.innerText = "Download Notes";
        noteDownload.addEventListener("click", this.downloadNote);
        articleSection.appendChild(noteDownload);

        if (noteData.role == "NOTETAKER") {
            let editNoteBtn = document.createElement("BUTTON");
            editNoteBtn.innerText = "Edit";
            editNoteBtn.id = noteData.id + "_edit";
            editNoteBtn.addEventListener("click", function(e) {
                let noteId = this.id.slice(0, -5);
                app.store("editNoteNoteId", noteId);
                location.assign("./edit");
            });
            articleSection.appendChild(editNoteBtn);
        }

        return article;
    },

    _generateEmptyArticle: function(courseName, role) {
        let article = document.createElement("ARTICLE");

        let articleHeader = document.createElement("HEADER");
        articleHeader.innerHTML = "<span class='title'>No Notes</span>";
        article.appendChild(articleHeader);

        let articleSection = document.createElement("SECTION");
        article.appendChild(articleSection);

        let descriptionP = document.createElement("P");
        let checkboxField = document.getElementById("hideDownloadedField");
        let checkbox = document.getElementById("hideDownloaded");
        if(checkboxField.style.display == "flex" && checkbox.checked == true && role == "NOTETAKER"){
            descriptionP.innerText = "There are no uploaded notes for " + courseName + " that have not been downloaded.";
        } else if(role == "NOTETAKER"){
            descriptionP.innerText = "There are no uploaded notes for " + courseName + "."; 
        } else{
            descriptionP.innerText = "There are no new notes for " + courseName + ".";
        }   
        
        articleSection.appendChild(descriptionP);
        return article;
    },

    notesSuccess: function(data) {
        app.home.pagesLoaded++;
        
        var notes = data.payload.notes;
        
        if(notes.length == 0){
            app.home.paginationEnd = true;
        }
        
        for (var i = 0; i < notes.length; i++) {
            app.home.__populateCourse(notes[i]);
        }

        let noteContainers = document.getElementById("notesContainer").children;
        for (var x = 0; x < noteContainers.length; x++) {
            var courseName = noteContainers[x].children[0].children[0].children[0].innerText;
            var id = noteContainers[x].id.split("course_")[1];
            var nc = document.getElementById("notes_" + id);
            var role = document.getElementById("upload_" + id) ? "NOTETAKER" : "STUDENT";
            if (nc.children.length == 0) {
                var emptyArticle = app.home._generateEmptyArticle(courseName, role);
                nc.appendChild(emptyArticle);
            }
        }
    },
    
    getNotes: function(forced) {
        let scrollPosition = (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) / ((document.body.scrollHeight - window.innerHeight) || 1);
        
        if(forced === true || (scrollPosition > 0.9 && !app.home.paginationEnd) ){
            let sorting = app.home.getSortMethod();
            let sortMethod = sorting[0];
            let sortDirection = sorting[1];
            let hideDownloaded = document.getElementById("hideDownloaded").checked;
            
            Resources.Notes.GET(sortMethod, sortDirection, hideDownloaded, app.home.pagesLoaded, app.home.notesSuccess);
        }
    },
    getCourses: function() {
        var nc = document.getElementById("notesContainer");
        while (nc.firstChild) nc.removeChild(nc.firstChild);
        app.home.pagesLoaded = 0;

        Resources.UserCourses.GET(app.home.coursesSuccess);
    },
    coursesSuccess: function(data) {
        var courses = data.payload.courses;
        if (courses.length == 0) {
            app.home.__generateEmptyCourseHeader();
            return;
        }
        for (var i = 0; i < courses.length; i++) {
            app.home.__generateCourseHeader(courses[i]);
            app.home.notificationsToggledSuccess(courses[i]);
        }
        
        app.home.getNotes(true);
    },
    downloadNote: function(e) {
        this.disabled = true;
        let id = this.id;
        let xsrf = app.getCookie("xsrfToken");
        
        let url = "./v1/download.php?noteId=" + id.slice(0, -4) + "&xsrfToken=" + xsrf;

        var newWin = window.open(url, '_blank');  
        
        this.disabled = false;
        
        var article = null;
        let section = this.parentElement;
        if(section){
          article = section.parentElement;  
        } 
        if(article){
            article.className = ""; //Show a halo on the new undownloaded note
            if(document.getElementById("hideDownloaded").checked == true){
                //remove note from view
                var nc = article.parentElement;
                nc.removeChild(article);

                if(nc.children.length == 0){
                    var courseId = nc.id.split("notes_")[1];
                    var courseName = document.getElementById("course_" + courseId).children[0].children[0].children[0].innerText;
                    var role = document.getElementById("upload_" + courseId) ? "NOTETAKER" : "STUDENT";
                    var emptyArticle = app.home._generateEmptyArticle(courseName, role);
                    nc.appendChild(emptyArticle);
                }
            }
        }
        
        let downloadDate = document.getElementById(id.slice(0, -4) + "_downloadDate");
        
        if(downloadDate){
            let dateDownloaded = new Date().toPrettyDate(true);
            downloadDate.innerHTML = "Downloaded on: <span>" + dateDownloaded + "</span>";   

        }
        
        if(!newWin || newWin.closed || typeof newWin.closed=='undefined') { 
            new Modal("Error", MessageCode("PopUpBlocked"), null, null, "Okay").show();
        }
    }
};

app.startup.push(function userHomeStartup() {

    document.getElementById("sortDrop").addEventListener('change', app.home.getCourses);
    document.getElementById("hideDownloaded").addEventListener('change', app.home.getCourses);
    
});

app.afterStartup.push(function userHomeAfterStartup() {
    // Scroll to the top of the page
    
    window.pageYOffset = 0;
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;

    document.body.onscroll = debounce(app.home.getNotes, 250);
    app.home.getCourses();
});
