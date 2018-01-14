/* global app */
let Resources = {
    Password: {
        location: "../v1/password.php",
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
            request.send();
            return request;
        }
    },
    Auth: {
        location: "../v1/auth.php",
        POST: function(studentId, password, successCallback, failureCallback, options) {
            let request = app._generateRequest(successCallback, failureCallback, options);
            request.open("POST", Resources.Auth.location);
            request.setRequestHeader("Authorization", "Basic " + window.btoa(studentId + ":" + password));
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
                    location: "../v1/auth_all.php"
                },
                null,
                successCallback,
                failureCallback);
        }
    },
    Users: {
        location: "../v1/users.php",
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
            var data = {
                currentPassword: currentPassword
            };
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
            return app.get({ location: "../v1/usersearch.php" },
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
        location: "../v1/useraccess.php",
        SEARCH: function(studentId, courseName, courseNumber, page, successCallback, failureCallback) {
            let data = {};
            if (studentId) data["studentId"] = studentId;
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
            let data = {
                courseId: courseId,
                notifications: notify
            };
            return app.put(Resources.UserAccess, data, successCallback, failureCallback);
        },
        DELETE: function(userId, courseId, successCallback, failureCallback) {
            let data = {
                userId: userId,
                courseId: courseId
            };
            return app.delete(Resources.UserAccess, data, successCallback, failureCallback);
        }
    },
    UserCourses: {
        location: "../v1/usercourses.php",
        GET: function(successCallback, failureCallback) {
            return app.get(Resources.UserCourses, null, successCallback, failureCallback);
        }
    },
    Notes: {
        location: "../v1/notes.php",
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
            request.setRequestHeader("Authorization", app.user.authToken);
            request.upload.onprogress = progressCallback;
            request.send(formData);
            return request;
        },
        DELETE: function(id, successCallback, failureCallback) {
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
            return app.get({ location: "../v1/notessearch.php" }, data, successCallback, failureCallback);
        }
    },
    
    NotesEdit: {
        location: "../v1/notesEdit.php",
        POST: function(noteId, name, description, takenOn, files, successCallback, failureCallback, progressCallback) {
            if (!noteId || (!name && !description && !takenOn && !files)) return; //Nothing to change or no noteid
            var formData = new FormData();
            formData.append("noteId", noteId);
            if (name) formData.append("name", name);
            if (description) formData.append("description", description);
            if (takenOn) formData.append("takenOn", takenOn);
            if(files){
                for (var i = 0; i < files.length; i++) {
                    formData.append("file[]", files[i]);
                }
            }

            let request = app._generateRequest(successCallback, failureCallback);
            request.open("POST", this.location);
            request.setRequestHeader("Authorization", app.user.authToken);
            request.upload.onprogress = progressCallback;
            request.send(formData);
            return request;
            
        }
    },

    Files: {
        location: "../v1/noteFiles.php",
        GET: function(noteId, progressCallback, successCallback, failureCallback) {
            let data = { noteId: noteId };
            let options = {
                onprogress: progressCallback,
                responseType: "blob"
            };
            return app.get(Resources.Files, data, successCallback, failureCallback, options);
        }
    },

    Courses: {
        location: "../v1/courses.php",
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
        POST: function(teacherFullName, courseName, courseNumber, section, semester, successCallback, failureCallback){
            let data = {
                teacherFullName: teacherFullName,
                courseName: courseName,
                courseNumber: courseNumber,
                section: section,
                semester: semester
            };
            return app.post(Resources.Courses, data, successCallback, failureCallback);
        },
        
        PUT: function(courseId, teacherFullName, courseName, courseNumber, sectionStart, sectionEnd, semester, successCallback, failureCallback){
            let data = {
                courseId: courseId,
                teacherFullName: teacherFullName,
                courseName: courseName,
                courseNumber: courseNumber,
                sectionStart: sectionStart,
                sectionEnd: sectionEnd,
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
        location: "../v1/_navbar.php",
        GET: function(successCallback, failureCallback) {
            return app.get(Resources.Navbar, null, successCallback, failureCallback);
        }
    }
};
