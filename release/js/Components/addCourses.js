/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.addCourses = {
    uploadInProgress: false,

    reset: function() {
        app.addCourses.uploadInProgress = false;

        // Empty the course input fields: 
        document.getElementById("file").value = "";
        document.getElementById("season").selectedIndex = app.addCourses.getDefaultSeason();
        document.getElementById("year").value = new Date().getFullYear();
        
        app.addCourses.updateFileLabel();
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("file");
        var label = document.getElementById("fileLabel");
        
        if(!this.files || this.files.length == 0) {
            label.innerText = "Select File (.csv)";
            return;
        }
        
        label.innerText = this.files.length + " File".pluralize(this.files.length)+" Selected";
    },
    
    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month >= 11) return 1; //intersession
        if (month >= 0 && month < 5) return 2; //winter
        if (month >= 5 && month < 8) return 3; //summer
        return 0; //fall
    },
    
    submitCourseSuccess: function(data) {
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitCourse);

        // Empty the course input fields: 
        app.addCourses.reset();
        
        new Modal("Courses Added", MessageCode("CoursesCreated") + "<br>" + data.payload.output, null, {
                    text: "Okay"
                }).show();
    },
    
    submitCourseFailure: function(data){
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitCourse);
        app.addCourses.uploadInProgress = false;
        
        app.handleFailure(data);
    },
    
    submitFile: function(event) {
        event.preventDefault();
        if (this.uploadInProgress) {
            console.warn("Courses are already being uploaded...");
            return;
        }

        let file = document.getElementById('file').files;
        if (file.length == 0) {
            app.handleFailure({ messageCode: "NoFilesUploaded" });
            return;
        }

        let seasonSelector = document.getElementById("season");
        var season = seasonSelector.value;
        var year = document.getElementById("year").value;
        var formattedSemester = "";
        
        if(!season){
            new Modal("Error", MessageCode("MissingArgumentSeason"), null, {
                text: "Okay"
            }).show();
            return;
        }
        
        if(!year){
            new Modal("Error", MessageCode("MissingArgumentYear"), null, {
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
        
        new Modal("Upload Courses",
                    "Are you sure you want to upload the selected file?" +
                    "<br>This <b>CANNOT</b> be undone." +
                    "<br>Confirm Admin password: <input type='password' placeholder='Password' autocomplete='false' id='addCourses_uploadCoursesPw'><p class='error'></p>", {
                        text: "Yes, UPLOAD Courses",
                        callback: function() {
                            this.confirmButton.disabled = true;
                            app.addCourses._uploadCourses.call(this, formattedSemester, file);
                        }
                    }
                ).show();
    },
    
    _uploadCourses: function(formattedSemester, file){
        
        var that = this;
        
        var pass = document.getElementById("addCourses_uploadCoursesPw").value;
        if (!pass) {
            document.getElementById("addCourses_uploadCoursesPw").nextSibling.innerHTML = "Please enter password.";
            that.confirmButton.disabled = false;
            return;
        }
        
        app.addCourses.uploadInProgress = true;
        
        //Disable the form
        document.getElementById('submit').disabled = true;
        document.getElementById('addCourses').removeEventListener('submit', app.addCourses.submitCourse);

        Resources.Courses.POST(formattedSemester, file, pass, function(response){
            //Success function
            that.hide();
            app.addCourses.submitCourseSuccess(response);
        }, function(response){
            //Failure function
            if(response.messageCode == "AuthenticationFailed"){
                
                // Enable the form
                document.getElementById('submit').disabled = false;
                document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitCourse);
                
                that.confirmButton.disabled = false;
                
                app.addCourses.uploadInProgress = false;
                
                document.getElementById("addCourses_uploadCoursesPw").nextSibling.innerHTML = "Incorrect Password.";
                return;
            }
            that.hide();
            app.addCourses.submitCourseFailure(response);
            
        });
    }
};

app.startup.push(function addCoursesStartup() {
    app.addCourses.submitFile = app.addCourses.submitFile.bind(app.addCourses);
    
    document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitFile);
    
    document.getElementById("year").value = new Date().getFullYear();
    document.getElementById("season").selectedIndex = app.addCourses.getDefaultSeason();
    
    // Change the file label when files are added
    document.getElementById("file").addEventListener("change", app.addCourses.updateFileLabel);
    app.addCourses.updateFileLabel();
});

