/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.addCourses = {

    reset: function() {
        app.semesterController.uploadInProgress = false;

        // Empty the course input fields: 
        document.getElementById("addCoursesFile").value = "";
        document.getElementById("addSeason").selectedIndex = app.addCourses.getDefaultSeason();
        document.getElementById("addYear").value = new Date().getFullYear();
        
        app.addCourses.updateFileLabel();
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("addCoursesFile");
        var label = document.getElementById("addFileLabel");
        
        if(!this.files || this.files.length == 0) {
            label.innerText = "Select File (.csv)";
            return;
        }
        
        label.innerText = this.files.length + " File".pluralize(this.files.length)+" Selected";
    },
    
    getDefaultSeason: function() {
        let month = new Date().getMonth();
        if (month >= 10) return 1; //intersession
        if (month >= 0 && month < 3) return 2; //winter
        if (month >= 3 && month < 7) return 3; //summer
        return 0; //fall
    },
    
    submitCoursesSuccess: function(data) {
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
        app.semesterController.uploadInProgress = false;

        // Empty the course input fields: 
        app.addCourses.reset();
        
        new Modal("Courses Added", MessageCode("CoursesCreated") + "<br>" + data.payload.output, null, null, "Okay").show();
    },
    
    submitCoursesFailure: function(data){
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
        app.semesterController.uploadInProgress = false;
        
        app.handleFailure(data);
    },
    
    submitCourses: function() {

        if (app.semesterController.uploadInProgress) {
            console.warn("Courses are already being uploaded...");
            return;
        }

        let file = document.getElementById('addCoursesFile').files;
        if (file.length == 0) {
            app.handleFailure({ messageCode: "NoCourseFilesUploaded" });
            return;
        }

        let seasonSelector = document.getElementById("addSeason");
        var season = seasonSelector.value;
        let yearInput = document.getElementById("addYear");
        var year = yearInput.value;
        
        var semesterCode = app.semesterController.validateSemester(seasonSelector, yearInput, season, year);
        if(!semesterCode) return;
        
        new Modal("Upload Courses",
                    "Are you sure you want to upload the selected file?" +
                    "<br>This <b>CANNOT</b> be undone." +
                    "<br>Confirm Admin password: <input type='password' placeholder='Password' autocomplete='false' id='addCourses_uploadCoursesPw'><p class='error'></p>", {
                        text: "Yes, UPLOAD Courses",
                        callback: function() {
                            this.confirmButton.disabled = true;
                            app.addCourses._uploadCourses.call(this, semesterCode, file);
                        }
                    }
                ).show();
    },
    
    _uploadCourses: function(semesterCode, file){
        
        var that = this;
        
        var pass = document.getElementById("addCourses_uploadCoursesPw").value;
        if (!pass) {
            document.getElementById("addCourses_uploadCoursesPw").nextSibling.innerHTML = "Please enter password.";
            that.confirmButton.disabled = false;
            return;
        }
        
        app.semesterController.uploadInProgress = true;
        
        //Disable the form
        document.getElementById('submit').disabled = true;
        document.getElementById("semesterData").removeEventListener('submit', app.semesterController.submitSemester);

        Resources.Courses.POST(semesterCode, file, pass, function(response){
            //Success function
            that.hide();
            app.addCourses.submitCoursesSuccess(response);
        }, function(response){
            //Failure function
            if(response.messageCode == "AuthenticationFailed"){
                
                // Enable the form
                document.getElementById('submit').disabled = false;
                document.getElementById("semesterData").addEventListener('submit', app.semesterController.submitSemester);
                
                that.confirmButton.disabled = false;
                
                app.semesterController.uploadInProgress = false;
                
                document.getElementById("addCourses_uploadCoursesPw").nextSibling.innerHTML = "Incorrect Password.";
                return;
            }
            that.hide();
            app.addCourses.submitCoursesFailure(response);
            
        });
    }
};

app.startup.push(function addCoursesStartup() {
    app.addCourses.submitCourses = app.addCourses.submitCourses.bind(app.addCourses);
    
    document.getElementById("addYear").value = new Date().getFullYear();
    document.getElementById("addSeason").selectedIndex = app.addCourses.getDefaultSeason();
    
    // Change the file label when files are added
    document.getElementById("addCoursesFile").addEventListener("change", app.addCourses.updateFileLabel);
    app.addCourses.updateFileLabel();
});

