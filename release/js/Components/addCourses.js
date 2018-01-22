/* global Resources,MessageCode,Modal */
var app = app || {
    startup: [],
    afterStartup: []
};

app.addCourses = {
    uploadInProgress: false,

    reset: function() {
        app.addCourses.uploadInProgress = false;

        document.getElementById("pb").style.width = 0;
        document.getElementById("pt").innerText = "";

        // Empty the course input fields: 
        document.getElementById("file").value = "";
        document.getElementById("season").selectedIndex = app.addCourses.getDefaultSeason();
        document.getElementById("year").value = new Date().getFullYear();
        
        app.addCourses.updateFileLabel();
    },
    
    addFile: function(event) {
        // Reset the progess bar and progress text to 0 when the user clicks on the button to select files
        document.getElementById("pb").style.width = 0;
        document.getElementById("pt").innerText = "";
    },
    
    updateFileLabel: function(){
        var input = document.getElementById("file");
        var label = document.getElementById("fileLabel");
        
        if(!this.files || this.files.length == 0) {
            label.innerText = "Select File (*.csv)";
            return;
        }
        
        label.innerText = this.files.length + " File".pluralize(this.files.length)+" Selected";
    },
    
    getDefaultSeason: function() {
        let month = new Date().getMonth();
        let today = new Date().getDate();
        if (month >= 11) return 2; //intersession
        if (month >= 0 && month < 5) return 3; //winter
        if (month >= 5 && month < 8) return 4; //summer
        return 1; //fall
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
    
    submitCourseSuccess: function(data) {
        
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitCourse);

        // Empty the course input fields: 
        app.addCourses.reset();
        
        new Modal("Courses Added", MessageCode["CoursesCreated"] + "<br>" + data.payload.output, null, {
                    text: "Okay"
                }).show();
    },
    
    submitCourseFailure: function(data){
        // Enable the form
        document.getElementById('submit').disabled = false;
        document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitCourse);
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
        
        // TODO XSS ESCAPING!

        let seasonSelector = document.getElementById("season");
        var season = seasonSelector.value;
        var year = document.getElementById("year").value;
        var formattedSemester = "";
        
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

        app.addCourses.uploadInProgress = true;
        
        //Disable the form
        document.getElementById('submit').disabled = true;
        document.getElementById('addCourses').removeEventListener('submit', app.addCourses.submitCourse);
 
        Resources.Courses.POST(formattedSemester, file, this.submitCourseSuccess, this.submitCourseFailure, function(event) {
            if (event.lengthComputable === true) {
                let percent = Math.round((event.loaded / event.total) * 100);
                app.addCourses.setProgress(percent);
            }
        });
    },


};

app.startup.push(function addCoursesStartup() {
    app.addCourses.submitFile = app.addCourses.submitFile.bind(app.addCourses);
    
    document.getElementById('file').addEventListener('click', app.addCourses.addFile);
    document.getElementById('addCourses').addEventListener('submit', app.addCourses.submitFile);
    
    document.getElementById("year").value = new Date().getFullYear();
    document.getElementById("season").selectedIndex = app.addCourses.getDefaultSeason();
    
    // Change the file label when files are added
    document.getElementById("file").addEventListener("change", app.addCourses.updateFileLabel);
    app.addCourses.updateFileLabel();
});

