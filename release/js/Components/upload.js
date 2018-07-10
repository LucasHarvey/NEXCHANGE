/* global Resources,MessageCode,datePolyFillStart,Modal,getExtension,location */
var app = app || {
    startup: [],
    afterStartup: []
};

app.postNotes = {
    uploadInProgress: false,

    reset: function() {
        app.postNotes.uploadInProgress = false;

        document.getElementById("pb").style.width = 0;
        document.getElementById("pt").innerText = "";
    },

    setProgress: function(percent) {
        percent = percent || 0;
        let progressBar = document.getElementById("pb");
        let progressText = document.getElementById("pt");

        if (progressBar !== undefined) {
            progressBar.style.width = percent + "%";
        }

        if (progressText !== undefined) {
            progressText.innerText = percent + "%";
            if(percent == 100){
                progressText.innerText += " - Upload Complete";
            }
        }
    },

    addFile: function(event) {
        // Reset the progess bar and progress text to 0 when the user clicks on the button to select files
        document.getElementById("pb").style.width = 0;
        document.getElementById("pt").innerText = "";
    },

    getCourses: function() {
        Resources.UserCourses.GET(app.postNotes.getCoursesSuccess);
    },

    getCoursesSuccess: function(response) {
        let courses = response.payload.courses;
        if (courses.length == 0) {
            new Modal("Error", MessageCode("UserNoCoursesAccessible"), {
                text: "Okay",
                callback: function(){
                    location.assign("./home");
                }
            }, false).show();
            return;
        }
        var courseSelected = app.getStore("uploadNotesCourseId");
        if (courseSelected) app.store("uploadNotesCourseId", null);
        let coursePicker = document.getElementById("course");
        for (var i = 0; i < courses.length; i++) {
            if (courses[i].role == "NOTETAKER") {
                let course = document.createElement('option');
                course.innerText = courses[i].courseName + " " + courses[i].courseNumber;
                course.value = courses[i].id;
                coursePicker.appendChild(course);
            }
        }
        let options = coursePicker.children;
        if(options.length == 0){
            new Modal("Error", MessageCode("UserNoCoursesAccessible"), {
                text: "Okay",
                callback: function(){
                    location.assign("./home");
                }
            }, false).show();
            return;
        }
        for(var i = 0; i < options.length; i++){
            if (courseSelected && options[i].value === courseSelected) {
                coursePicker.selectedIndex = i;
            } 
        }

    },

    uploadNotesSuccess: function(response) {
        
        // Clear the file input
        var fileSelector = document.getElementById("file");
        fileSelector.parentNode.removeChild((fileSelector));
        
        var newFileSelector = document.createElement("INPUT");
        newFileSelector.type = "file";
        newFileSelector.id = "file";
        newFileSelector.name = "file[]";
        newFileSelector.className = "inputFile";
        newFileSelector.multiple = true;
        newFileSelector.addEventListener("change", app.postNotes.updateFileLabel);
        
        var fileUserField = document.getElementById("fileUserField");
        
        var fileLabel = document.getElementById("fileLabel");
        
        fileUserField.insertBefore(newFileSelector, fileLabel);
        
        app.postNotes.updateFileLabel();
        
        // Empty the form
        document.getElementById('noteName').value = "";
        document.getElementById('description').value = "";
        document.getElementById('date').value = new Date().toDateInputValue();
        app.postNotes.updateCharacterLimit();

        app.postNotes.uploadInProgress = false;
        
        //Enable the form
        document.getElementById("submit").disabled = app.postNotes.uploadInProgress;
        document.getElementById('noteData').addEventListener('submit', app.postNotes.submitFiles);
        
        let successes = response.payload.succeeded;

        let uploadDiv = document.getElementById("uploads");
        let succeededDiv = document.createElement("div");
        succeededDiv.className = "uploadSuccess"
        
        uploadDiv.innerText = '';

        if (successes.length) {
            succeededDiv.innerHTML = '<p>Uploaded file'.pluralize(successes.length) + ':</p>';
            let list = document.createElement("UL");
            for (var x = 0; x < successes.length; x++) {
                let listItem = document.createElement('li');
                let span = document.createElement('span');
                span.innerText = successes[x].name; //+ " - MD5: " + successes[x].md5;
                listItem.appendChild(span);
                list.appendChild(listItem);
            }
            succeededDiv.appendChild(list);
        }

        uploadDiv.appendChild(succeededDiv);

    },

    submitFiles: function(event) {
        event.preventDefault();
        if (this.uploadInProgress) {
            console.warn("Notes are already being uploaded...");
            return;
        }
        app.postNotes.reset();

        let files = document.getElementById('file').files;
        if (files.length == 0) {
            app.handleFailure({ 
                messageCode: "NoNoteFilesUploaded",
                status: 400
            });
            return;
        }
        if(files.length > 20) {
            app.handleFailure({
                messageCode: "TooManyFiles",
                status: 400
            });
            return;
        }
        let name = document.getElementById('noteName').value;
        let description = document.getElementById('description').value;

        // Add check that name is present
        if (!name) {
            app.handleFailure({
                messageCode: "MissingArgumentNoteName",
                status: 400
            });
            return;
        }

        if (!this.validateName(name)) {
            app.handleFailure({
                messageCode: "NoteNameNotValid",
                status: 400
            });
            return;
        }

        if (!this.validateDescription(description)) {
            app.handleFailure({
                messageCode: "DescriptionNotValid",
                status: 400
            });
            return;
        }

        let datePicker = document.getElementById('date');
        var date = datePicker.value;
        var validatedDate = app.dateFormatting.isPastDate(new Date(date.replace(/-/g, '\/').replace(/T.+/, '')));
        if (!date) {
            app.handleFailure({
                messageCode: "MissingArgumentDate",
                status: 400
            });
            return;
        }
        if (!validatedDate) {
            app.handleFailure({
                messageCode: "DateNotValid",
                status: 400
            });
            return;
        }

        var dateToSubmit = app.dateFormatting.parseSubmissionDate(new Date(datePicker.value.replace(/-/g, '\/').replace(/T.+/, '')));

        let coursePicker = document.getElementById("course");
        let courseId = coursePicker.options[coursePicker.selectedIndex].value;

        if (!courseId) {
            app.handleFailure({
                messageCode: "MissingArgumentCourse",
                status: 400
            });
            return;
        }
        
        for(var i=0; i<files.length; i++){
            var extension = getExtension(files[i].name);
            if(!app.DEFAULTS.ALLOWED_EXTENSIONS.includes(extension)){
                new Modal("Error", MessageCode("NoteExtensionUnauthorized"), null, {
                text: "Okay"
                }).show();
                return;
            }
        }

        this.uploadInProgress = true;
        
        //Disable the form
        document.getElementById("submit").disabled = app.postNotes.uploadInProgress;
        document.getElementById('noteData').removeEventListener('submit', app.postNotes.submitFiles);
 
        Resources.Notes.POST(name, description, courseId, dateToSubmit, files, this.uploadNotesSuccess, this.uploadNotesFailure, function(event) {
            if (event.lengthComputable === true) {
                let percent = Math.round((event.loaded / event.total) * 100);
                app.postNotes.setProgress(percent);
            }
        });
    },

    uploadNotesFailure: function(data) {
        app.postNotes.uploadInProgress = false;
        
        // Enable the form
        document.getElementById("submit").disabled = app.postNotes.uploadInProgress;
        document.getElementById('noteData').addEventListener('submit', app.postNotes.submitFiles);
        
        app.handleFailure(data);
    },

    validateName: function(name) {
        return (name.length <= 60);
    },

    validateDescription: function(description) {
        return (description.length <= 500);
    },

    updateCharacterLimit: function() {
        let description = document.getElementById('description');
        var charactersUsed = description.value.length;
        var charactersLeft = 500 - charactersUsed;
        if (charactersLeft < 0) {
            app.handleFailure({
                messageCode: "DescriptionNotValid",
                status: 400
            });
            return;
        }

        document.getElementById("characterCount").innerHTML = "Characters left: " + charactersLeft;
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("file");
        var label = document.getElementById("fileLabel");
        
        if(input.files.length == 0) {
            label.innerText = "Select File(s)";
            return;
        }
        
        label.innerText = input.files.length + " File".pluralize(input.files.length)+" Selected";
    }

};

app.startup.push(function postNotesStartup() {
    app.postNotes.submitFiles = app.postNotes.submitFiles.bind(app.postNotes);

    document.getElementById('file').addEventListener('click', app.postNotes.addFile);
    document.getElementById('noteData').addEventListener('submit', app.postNotes.submitFiles);

    datePolyFillStart();

    document.getElementById('date').value = new Date().toDateInputValue();
    app.postNotes.updateCharacterLimit();
    document.getElementById('description').addEventListener('input', app.postNotes.updateCharacterLimit);
    document.getElementById('description').addEventListener('keyup', app.postNotes.updateCharacterLimit);
    
    // Change the file label when files are added
    document.getElementById("file").addEventListener("change", app.postNotes.updateFileLabel);
    app.postNotes.updateFileLabel();
});

app.afterStartup.push(function postNotesAfterStartup() {
    app.postNotes.getCourses();
});
