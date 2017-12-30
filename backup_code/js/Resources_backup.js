/* global app */
let Resources = {
    Password: {
        location: "../v1/password.php",
        POST: function(code, password, successCallback, failureCallback){
            let request = app._generateRequest(successCallback, failureCallback, null);
            request.open("POST", Resources.Password.location);
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
            return app.delete(Resources.Users, data, successCallback, failureCallback);
        }
    },
    UserAccess: {
        location: "../v1/useraccess.php",
        GET: function(data, successCallback, failureCallback) {
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
        SEARCH: function(studentId, courseName, courseNumber, page, successCallback, failureCallback) {
            let data = {};
            if (studentId) data["studentId"] = studentId;
            if (courseName) data["courseName"] = courseName;
            if (courseNumber) data["courseNumber"] = courseNumber;
            if (page) data["page"] = page;
            return app.get({ location: "../v1/useraccesssearch.php" }, data, successCallback, failureCallback);
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
        PUT: function(noteId, name, description, takenOn, successCallback, failureCallback) {
            if (!noteId || (!name && !description && !takenOn)) return; //Nothing to change or no noteid

            var data = {};
            data["noteId"] = noteId;
            if (name) data["name"] = name;
            if (description) data["description"] = description;
            if (takenOn) data["takenOn"] = takenOn;
            return app.put(Resources.Notes, data, successCallback, failureCallback);
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
        }
    },

    Navbar: {
        location: "../v1/_navbar.php",
        GET: function(successCallback, failureCallback) {
            return app.get(Resources.Navbar, null, successCallback, failureCallback);
        }
    }
};
