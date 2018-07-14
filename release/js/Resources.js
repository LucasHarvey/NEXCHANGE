/* global app */
let Resources = {
    Log: {
        location: "v1/uilog.php",
        POST: function(message, successCallback, failureCallback){
            if(!message) return;
            let data = {message: message};
            return app.post(Resources.Log, data, successCallback, failureCallback);
        }
    },
    Password: {
        location: "v1/password.php",
        POST: function(studentId, email, successCallback, failureCallback){
            let data = {
                studentId: studentId,
                email: email
            };
            return app.post(Resources.Password, data, successCallback, failureCallback);
        },
        PUT: function(code, password, successCallback, failureCallback){
            let request = app._generateRequest(successCallback, failureCallback, null);
            request.open("PUT", Resources.Password.location);
            request.setRequestHeader("Authorization", "Basic " + window.btoa(code + ":" + password));
            // Enable the loading spinner 
            document.body.classList.add("load");
            request.send();
            return request;
        }
    },
    Auth: {
        location: "v1/auth.php",
        POST: function(studentId, password, successCallback, failureCallback, options) {
            let request = app._generateRequest(successCallback, failureCallback, options);
            request.open("POST", Resources.Auth.location);
            request.setRequestHeader("Authorization", "Basic " + window.btoa(studentId + ":" + password));
            // Enable the loading spinner 
            document.body.classList.add("load");
            request.send();
            return request;
        },
        DELETE: function(successCallback, failureCallback, disableSubsequentChecks) {
            return app.delete(Resources.Auth, null, successCallback, failureCallback, {
                disableAuthResult: disableSubsequentChecks
            });
        },
        DELETEALL: function(successCallback, failureCallback) {
            return app.delete({
                    location: "v1/authall.php"
                },
                null,
                successCallback,
                failureCallback);
        }
    },
    Users: {
        location: "v1/users.php",
        GET: function(successCallback, failureCallback) {
            return app.get(Resources.Users, null, successCallback, failureCallback);
        },
        POST: function(firstName, lastName, studentId, email, successCallback, failureCallback) {
            let data = {
                firstName: firstName,
                lastName: lastName,
                studentId: studentId,
                email: email
            };
            return app.post(Resources.Users, data, successCallback, failureCallback);
        },
        PUT: function(email, password, currentPassword, successCallback, failureCallback) {
            var data = {};
            data["currentPassword"] = "Basic " + window.btoa(currentPassword);
            if (email) data["email"] = email;
            if (password) data["password"] = "Basic " + window.btoa(password);
            return app.put(Resources.Users, data, successCallback, failureCallback, {
                disableAuthResult: true //Let failure handle it
            });
        },
        SEARCH: function(name, id, page, successCallback, failureCallback) {
            let data = {};
            if (name) data["name"] = name;
            if (id) data["studentId"] = id;
            if (page) data["page"] = page;
            return app.get({ location: "v1/usersearch.php" },
                data,
                successCallback,
                failureCallback);
        },
        DELETE: function(studentId, password, successCallback, failureCallback) {
            let data = {};
            if (!studentId || !password) return;
            data.studentId = studentId;
            data.password = window.btoa(password);
            return app.delete(Resources.Users, data, successCallback, failureCallback,{
                disableAuthResult: true //Let failure handle it
            });
        }
    },
    UserAccess: {
        location: "v1/useraccess.php",
        SEARCH: function(studentId, lastName, courseName, courseNumber, page, successCallback, failureCallback) {
            let data = {};
            if (studentId) data["studentId"] = studentId;
            if (lastName) data["lastName"] = lastName;
            if (courseName) data["courseName"] = courseName;
            if (courseNumber) data["courseNumber"] = courseNumber;
            if (page) data["page"] = page;
            return app.get(Resources.UserAccess, data, successCallback, failureCallback);
        },
        POST: function(studentId, coursesId, role, expiryDate, successCallback, failureCallback) {
            let data = {
                studentId: studentId,
                coursesId: coursesId,
                role: role,
                expiryDate: expiryDate
            };
            return app.post(Resources.UserAccess, data, successCallback, failureCallback);
        },
        PUT: function(notify, courseId, successCallback, failureCallback) {
            if(!courseId) return;
            let data = {
                courseId: courseId,
                notifications: notify
            };
            return app.put(Resources.UserAccess, data, successCallback, failureCallback);
        },
        DELETE: function(userId, courseId, successCallback, failureCallback) {
            if(!userId || !courseId) return;
            let data = {
                userId: userId,
                courseId: courseId
            };
            return app.delete(Resources.UserAccess, data, successCallback, failureCallback);
        }
    },
    UserCourses: {
        location: "v1/usercourses.php",
        GET: function(successCallback, failureCallback) {
            return app.get(Resources.UserCourses, null, successCallback, failureCallback);
        }
    },
    Notes: {
        location: "v1/notes.php",
        GET_id: function(id, successCallback, failureCallback) {
            let data = {
                id: id
            };
            return app.get(Resources.Notes, data, successCallback, failureCallback);
        },
        GET: function(sortMethod, sortDirection, hideDownloaded, page, successCallback, failureCallback) {
            let data = {
                sortMethod: sortMethod,
                sortDirection: sortDirection,
                hideDownloaded: hideDownloaded,
                page: page
            };
            return app.get(Resources.Notes, data, successCallback, failureCallback);
        },
        
        POST: function(name, description, courseId, date, files, successCallback, failureCallback, progressCallback) {
            var formData = new FormData();
            formData.append("noteName", name);
            formData.append("description", description);
            formData.append("courseId", courseId);
            formData.append("takenOn", date);
            for (var i = 0; i < files.length; i++) {
                formData.append("file[]", files[i]);
            }

            let request = app._generateRequest(successCallback, failureCallback);
            request.open("POST", this.location);
            request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
            request.upload.onprogress = progressCallback;
            // Enable the loading spinner 
            document.body.classList.add("load");
            request.send(formData);
            return request;
        },
        DELETE: function(id, successCallback, failureCallback) {
            if(!id) return;
            let data = {
                id: id
            };
            return app.delete(Resources.Notes, data, successCallback, failureCallback);
        },
        SEARCH: function(sortMethod, sortDirection, courseId, studentId, page, successCallback, failureCallback) {
            var data = {};
            if(sortMethod) data["sortMethod"] = sortMethod;
            if(sortDirection) data["sortDirection"] = sortDirection;
            if (courseId) data["courseId"] = courseId;
            if (studentId) data["studentId"] = studentId;
            if (page) data["page"] = page;
            return app.get({ location: "v1/notessearch.php" }, data, successCallback, failureCallback);
        }
    },
    
    NotesEdit: {
        location: "v1/notesedit.php",
        POST: function(noteId, name, description, takenOn, files, successCallback, failureCallback, progressCallback) {
            if (!noteId) return;
            var formData = new FormData();
            formData.append("noteId", noteId);
            if (name) formData.append("name", name);
            if(description != undefined) formData.append("description", description);
            if (takenOn) formData.append("takenOn", takenOn);
            if(files){
                for (var i = 0; i < files.length; i++) {
                    formData.append("file[]", files[i]);
                }
            }

            let request = app._generateRequest(successCallback, failureCallback);
            request.open("POST", this.location);
            request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
            request.upload.onprogress = progressCallback;
            // Enable the loading spinner 
            document.body.classList.add("load");
            request.send(formData);
            return request;
            
        }
    },

    Courses: {
        location: "v1/courses.php",
        GET: function(id, successCallback, failureCallback) {
            let data = {
                courseId: id
            };
            return app.get(Resources.Courses, data, successCallback, failureCallback);
        },
        SEARCH: function(teacherFullName, courseName, courseNumber, section, semester, page, successCallback, failureCallback) {
            let data = {
                teacherFullName: teacherFullName,
                courseName: courseName,
                courseNumber: courseNumber,
                section: section,
                semester: semester,
                page: page
            };
            return app.get(Resources.Courses, data, successCallback, failureCallback);
        },
        
        POST: function(semesterCode, files, password, successCallback, failureCallback) {
            var formData = new FormData();
            formData.append("semesterCode", semesterCode);
            for (var i = 0; i < files.length; i++) {
                formData.append("file[]", files[i]);
            }
            formData.append("password", window.btoa(password));

            let request = app._generateRequest(successCallback, failureCallback, {
                disableAuthResult: true //Let failure handle it
            });
            request.open("POST", this.location);
            request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
            // Enable the loading spinner 
            document.body.classList.add("load");
            request.send(formData);
            return request;
        },
        
        PUT: function(courseId, teacherFullName, courseName, courseNumber, section, semester, successCallback, failureCallback){
            if(!courseId) return;
            let data = {
                courseId: courseId,
                teacherFullName: teacherFullName,
                courseName: courseName,
                courseNumber: courseNumber,
                section: section,
                semester: semester
            };
            return app.put(Resources.Courses, data, successCallback, failureCallback); 
            
        },
        
        DELETE: function(courseId, password, successCallback, failureCallback){
            let data = {};
            if (!courseId || !password) return;
            data.courseId = courseId;
            data.password = window.btoa(password);
            return app.delete(Resources.Courses, data, successCallback, failureCallback, {
                disableAuthResult: true //Let failure handle it
            });
        }
    },

    Navbar: {
        location: "v1/navbar.php",
        GET: function(successCallback, failureCallback) {
            return app.get(Resources.Navbar, null, successCallback, failureCallback);
        }
    },
    
    Semester: {
        location: "v1/semester.php",
        GET: function(semesterCode, successCallback, failureCallback){
            return app.get(Resources.Semester, {semesterCode: semesterCode}, successCallback, failureCallback);
        },
        POST: function(semesterCode, files, newSemesterStart, newSemesterEnd, newMarchBreakStart, newMarchBreakEnd, password, successCallback, failureCallback) {
            var formData = new FormData();
            formData.append("semesterCode", semesterCode);
            for (var i = 0; i < files.length; i++) {
                formData.append("file[]", files[i]);
            }
            formData.append("newSemesterStart", newSemesterStart);
            formData.append("newSemesterEnd", newSemesterEnd);
            formData.append("newMarchBreakStart", newMarchBreakStart);
            formData.append("newMarchBreakEnd", newMarchBreakEnd);
            formData.append("password", window.btoa(password));

            let request = app._generateRequest(successCallback, failureCallback, {
                disableAuthResult: true //Let failure handle it
            });
            request.open("POST", this.location);
            request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
            // Enable the loading spinner 
            document.body.classList.add("load");
            request.send(formData);
            return request;
        },
        PUT: function(semesterCode, semesterStart, semesterEnd, marchBreakStart, marchBreakEnd, successCallback, failureCallback){
            if(!semesterCode) return;
            let data = {};
            data["semesterCode"] = semesterCode;
            if(semesterStart !== undefined) data["semesterStart"] = semesterStart;
            if(semesterEnd !== undefined) data["semesterEnd"] = semesterEnd;
            if(marchBreakStart !== undefined) data["marchBreakStart"] = marchBreakStart;
            if(marchBreakEnd !== undefined) data["marchBreakEnd"] = marchBreakEnd;

            return app.put(Resources.Semester, data, successCallback, failureCallback);
        }
    }
};
