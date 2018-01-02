/* global Resources,MessageCode,Modal,debounce */
var app = app || {
    startup: [],
    afterStartup: []
};

app.postCourseSearch = {
    
    pagesLoaded: 0,
    paginationEnd: false,

    highlightRow: function(row) {
        row.originalColor = row.style.backgroundColor;
        row.style.backgroundColor = '#BCD4EC';
        row.highlighted = true;

    },
    unhighlightRow: function(row) {
        row.style.backgroundColor = row.originalColor;
        row.highlighted = false;
    },

    removeCourse: function() {
        let course = this.parentElement;
        document.getElementById("courseContainer").removeChild(course);
    },

    containsCourse: function(courseId) {
        let courseContainer = document.getElementById("courseContainer");
        for (var x = 0; x < courseContainer.children.length; x++) {
            if (courseId == courseContainer.children[x].id) {
                return true;
            }
        }
        return false;
    },
    addCourses: function(event) {
        event.preventDefault();
        let resultsTable = document.getElementById("results");
        let courseContainer = document.getElementById("courseContainer");
        for (var i = 1; i < resultsTable.rows.length; i++) {
            if (resultsTable.rows[i].highlighted == true) {
                var courseId = resultsTable.rows[i].id;
                if (app.postCourseSearch.containsCourse(courseId)) {
                    continue;
                }
                var course = document.createElement("SPAN");
                var courseName = resultsTable.rows[i].cells[0].innerText;
                var courseNumber = resultsTable.rows[i].cells[1].innerText;
                var courseId = resultsTable.rows[i].id;
                course.innerText = courseName + " - " + courseNumber;
                course.id = courseId;

                var removeButton = document.createElement("BUTTON");
                removeButton.className = "removeButton";
                removeButton.type = "button";
                removeButton.innerText = "X";
                removeButton.onclick = app.postCourseSearch.removeCourse;

                course.appendChild(removeButton);
                courseContainer.appendChild(course);
                app.postCourseSearch.unhighlightRow(resultsTable.rows[i]);
            }
        }
    },

    updateAddButton: function() {
        var selectedRows = 0;
        let addButton = document.getElementById('addCourses');
        for (var i = 1; i < this.rows.length; i++) {
            if (this.rows[i].highlighted == true) {
                selectedRows = selectedRows + 1;
            }
        }
        if (selectedRows > 0) {
            addButton.style.display = 'block';
        } else {
            addButton.style.display = 'none';
            return
        }
        if (selectedRows == 1) {
            addButton.innerText = "Add Course";
            return;
        } else {
            addButton.innerText = "Add Courses";
            return;
        }
    },

    updateYearInput: function() {
        let seasonSelector = document.getElementById("season");
        let yearInput = document.getElementById("year");

        if (seasonSelector.selectedIndex == 0) {
            yearInput.placeholder = "All Years";
            yearInput.value = "";
            yearInput.readOnly = true;
            yearInput.classList.toggle("noHighlight");
        } else if (!yearInput.value) {
            yearInput.readOnly = false;
            yearInput.value = "";
            yearInput.placeholder = "Please enter a year";
            yearInput.classList.toggle("noHighlight");
        }

    },

    emptySearchResults: function() {
        let resultsTable = document.getElementById("results");
        while (resultsTable.rows.length > 1) {
            resultsTable.deleteRow(1);
        }
        return;
    },

    _generateResult: function(result) {

        let resultRow = document.createElement("tr");
        // ID of row is the course ID
        resultRow.id = result.id;

        let courseName = document.createElement("td");
        courseName.innerText = result.courseName;

        let courseNumber = document.createElement("td");
        courseNumber.innerText = result.courseNumber;

        let section = document.createElement("td");
        section.innerText = (result.section + "").padStart(5, "0");

        let teacherFullName = document.createElement("td");
        teacherFullName.innerText = result.teacherFullName;

        let semester = document.createElement("td");
        semester.innerText = result.semester;

        resultRow.appendChild(courseName);
        resultRow.appendChild(courseNumber);
        resultRow.appendChild(section);
        resultRow.appendChild(teacherFullName);
        resultRow.appendChild(semester);

        return resultRow;
    },

    courseSearchSuccess: function(data) {
        // Enable the form
        document.getElementById("submit").disabled = false;
        document.getElementById('courseSearch').addEventListener('submit', app.postCourseSearch.submitCourseSearch);

        app.postCourseSearch.pagesLoaded++;

        var courses = data.payload.courses;
        if (courses.length == 0) {
            app.postCourseSearch.paginationEnd = true;
            document.getElementById('resultsTray').style.display = 'block';
            document.getElementById("noResults").style.display = "block";
            document.getElementById("noResults").innerHTML = "No " + app.postCourseSearch.pagesLoaded == 1 ? "" : "more" + " Results";
            return;
        }

        var resultsTable = document.getElementById("results");

        for (var i = 0; i < courses.length; i++) {
            var result = app.postCourseSearch._generateResult(courses[i]);
            // Add onclick function to row for highlighting
            result.onclick = function() {
                if (!this.highlighted) {
                    app.postCourseSearch.highlightRow(this);

                } else {
                    app.postCourseSearch.unhighlightRow(this);

                }
            }
            // Add row to results table
            resultsTable.tBodies[0].appendChild(result);
        }

        document.getElementById("results").style.display = "table";
        document.getElementById('resultsTray').style.display = 'block';

        // Empty the course search fields: 
        document.getElementById('courseName').value = "";
        document.getElementById("courseNumber").value = "";
        document.getElementById("section").value = "";
        document.getElementById("teacherFullName").value = "";
        document.getElementById("season").selectedIndex = 0;
        var yearInput = document.getElementById("year");
        yearInput.placeholder = "All Years";
        yearInput.readOnly = true;
        yearInput.classList.toggle("noHighlight");
    },
    
    courseSearchFailure: function(response){
        // Enable the form
        document.getElementById("submit").disabled = false;
        document.getElementById('courseSearch').addEventListener('submit', app.postCourseSearch.submitCourseSearch);
        
        app.handleFailure(response);
    },

    submitCourseSearch: function(event) {
        event.preventDefault();
        document.getElementById('resultsTray').style.display = 'none';

        document.getElementById("noResults").style.display = "none";
        document.getElementById("noResults").innerHTML = "";

        document.getElementById("results").style.display = "none";


        let courseName = document.getElementById('courseName').value;
        let courseNumber = document.getElementById("courseNumber").value;
        let section = document.getElementById("section").value;
        let teacherFullName = document.getElementById("teacherFullName").value;
        let seasonSelector = document.getElementById("season");
        var season = seasonSelector.value;
        var year = document.getElementById("year").value;
        if (year == "") year = null;
        if (season == "allSemesters") season = null;
        var formattedSemester = "";
        var thisYear = new Date().getFullYear();
        if (season || year) {

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

            if (!app.dateFormatting.isPastSemester(season, year)) {
                new Modal("Error", MessageCode["FutureSemester"], null, {
                    text: "Okay"
                }).show();
                return;
            }

            // Format the semester correctly
            formattedSemester = season + year;
        }
        
        app.postCourseSearch.searchData = {
            tname: teacherFullName,
            cname: courseName,
            cnumber: courseNumber,
            sec: section,
            sem: formattedSemester
        };
        
        // Disable the form
        document.getElementById("submit").disabled = true;
        document.getElementById('courseSearch').removeEventListener('submit', app.postCourseSearch.submitCourseSearch);

        this.paginationEnd = false;
        this.pagesLoaded = 0;
        app.postCourseSearch.emptySearchResults();
        Resources.Courses.SEARCH(teacherFullName, courseName, courseNumber, section, formattedSemester, this.pagesLoaded, this.courseSearchSuccess, this.courseSearchFailure);
    },
    
    scrollCourses: function(event){
        let elem = document.getElementById("tableResults");
        let scrollPosition = elem.scrollTop / ((elem.scrollHeight - elem.clientHeight) || 1) ;
        if(scrollPosition > 0.9 && !app.postCourseSearch.paginationEnd){
            app.postCourseSearch.searchCourses(app.postCourseSearch.searchData);
        }
    },
    
    searchCourses: function(data){
        Resources.Courses.SEARCH(data.tname, data.cname, data.cnumber, data.sec, data.sem, this.pagesLoaded, this.courseSearchSuccess, this.courseSearchFailure);
    }

};

app.startup.push(function postCourseSearchStartup() {
    app.postCourseSearch.submitCourseSearch = app.postCourseSearch.submitCourseSearch.bind(app.postCourseSearch);
    
    document.getElementById('courseSearch').addEventListener('submit', app.postCourseSearch.submitCourseSearch);
    
    document.getElementById('addCourses').addEventListener('click', app.postCourseSearch.addCourses);
    
    document.getElementById('results').addEventListener('click', app.postCourseSearch.updateAddButton);
    document.getElementById('tableResults').onscroll = debounce(app.postCourseSearch.scrollCourses, 250);
    
    document.getElementById("season").addEventListener("change", app.postCourseSearch.updateYearInput);
    app.postCourseSearch.updateYearInput();
});


