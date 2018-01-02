/* global MessageCode, Resources, Modal,flexibility */
var app = app || {
    startup: [],
    afterStartup: []
};
//App.startup contains an array of functions that will be executed sequentially when the page is done loading
app.DEFAULTS = {
    REQUEST_TIMEOUT: 20 * 1000
};

app.start = function() {
    //flexibility(document.documentElement); //Make everything a flexbox (Polyfill.)
    
    console.log("NEXCHANGE Started.");
    for (var i = 0; i < app.startup.length; i++) {
        try {
            app.startup[i].call(app);
        } catch (e) {
            app.handleFailure({
                status: 500,
                messageCode: "StartupError"
            });
            console.log(e);
        }
    }
    for (var i = 0; i < app.afterStartup.length; i++) {
        try {
            app.afterStartup[i].call(app);
        } catch (e) {
            app.handleFailure({
                status: 500,
                messageCode: "StartupError"
            });
            console.log(e);
        }
    }
};

app.store = function(key, data) {
    if (typeof(Storage) === "undefined") {
        //Store cookies instead
        if (data === null) {
            document.cookie = key + "=; expires=Thu, 01 Jan 1970 00:00:01 GMT";
            return;
        }
        document.cookie = key + "=" + JSON.stringify(data) + "; path=/";
    } else {
        if (data === null) {
            window.localStorage.removeItem(key);
            return;
        }
        window.localStorage.setItem(key, JSON.stringify(data));
    }
};

app.getStore = function(key) {
    if (typeof(Storage) === "undefined") {
        //Store cookies instead
        var name = key + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return null;
    } else {
        let item = window.localStorage.getItem(key);
        return item && JSON.parse(item);
    }
};

app.getCookie = function(key) {
    
    var name = key + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return null;


}

app.handleFailure = function(response) {
    console.warn(response, MessageCode[response.messageCode]);
    if (typeof Modal === "undefined") {
        console.log("NO MODAL...", response);
        (document.getElementById("errorTray") || {}).innerHTML = MessageCode[response.messageCode];
        (document.getElementById("errorTray") || { style: {} }).style.display = "block";
        return;
    }
    new Modal("Error", MessageCode[response.messageCode], null, {
        text: "Okay"
    }).show();
};

app.handleAuthError = function(response) {
    console.warn(MessageCode[response.messageCode]);
    let logoutFunction = function() {
        var f = function() {
            // Only save the last location if the token expired
            if(response.messageCode == "AuthenticationExpired"){
                // Store the current location as the next location after login
                app.store("login_nextLocation", window.location.href);
            } else {
                app.store("login_nextLocation", null);
            }
            // Redirect the user to the login page
            window.location = "./login.html";
        };
        // Delete the user's JWT and xsrf token (this is necessary because of HTTPOnly)
        Resources.Auth.DELETE(f, f, true);
    };
    if (response.messageCode == "AuthorizationFailed") {
        let successData = {
            text: MessageCode[response.messageCode + "Button"],
            callback: logoutFunction
        };
        new Modal("Not Authorized", MessageCode[response.messageCode], successData, false).show();
        return;
    }

    if (typeof Modal === "undefined") {
        (document.getElementById("errorTray") || {}).innerHTML = MessageCode[response.messageCode];
        (document.getElementById("errorTray") || { style: {} }).style.display = "block";
        return;
    }
    let successData = {
        text: MessageCode[response.messageCode + "Button"],
        callback: logoutFunction
    };
    new Modal("Not Authenticated", MessageCode[response.messageCode], successData, false).show();
};

app.post = function(resource, data, success, failure) {
    let request = this._generateRequest(success, failure);
    request.open("POST", resource.location);
    // Set the xsrf token in header
    request.setRequestHeader("x-csrftoken", app.user.xsrfToken);
    request.setRequestHeader("content-type", "application/json");
    request.send(JSON.stringify(data || {}));
    return request;
};

app.put = function(resource, data, success, failure, options) {
    let request = this._generateRequest(success, failure, options);
    request.open("PUT", resource.location);
    // Set the xsrf token in header
    request.setRequestHeader("x-csrftoken", app.user.xsrfToken);
    request.setRequestHeader("content-type", "application/json");
    request.send(this._generateRequestBody(data));
    return request;
};

app.delete = function(resource, data, success, failure, options) {
    let request = this._generateRequest(success, failure, options);
    data = data || {};
    let requestParams = Object.keys(data)
        .map(function(k) {
            return encodeURIComponent(k) + "=" + encodeURIComponent(data[k]);
        })
        .join('&');
    request.open("DELETE", resource.location + (requestParams.length > 0 ? "?" : "") + requestParams);
    // Set the xsrf token in header
    request.setRequestHeader("x-csrftoken", app.user.xsrfToken);
    request.setRequestHeader("content-type", "application/json");
    request.send();
    return request;
};

app.get = function(resource, data, success, failure, options) {
    let request = this._generateRequest(success, failure);
    data = data || {};
    let requestParams = Object.keys(data)
        .map(function(k) {
            return encodeURIComponent(k) + "=" + encodeURIComponent(data[k]);
        })
        .join('&');
    request.open("GET", resource.location + (requestParams.length > 0 ? "?" : "") + requestParams);

    for (var opt in options) {
        request[opt] = options[opt];
    }
    // Set the xsrf token in header
    request.setRequestHeader("x-csrftoken", app.user.xsrfToken);
    request.send();
    return request;
};

app._generateRequestBody = function(object) {
    object = object || {};
    return JSON.stringify(object);
};

app._generateRequest = function(success, failure, options) {
    success = success || function() {};
    failure = failure || app.handleFailure;
    options = options || {};

    var xmlhttp = new XMLHttpRequest();
    //xmlhttp.timeout = app.DEFAULTS.REQUEST_TIMEOUT;
    xmlhttp.ontimeout = function() {
        failure({
            status: 408,
            messageCode: MessageCode.RequestTimedout
        });
    };
    xmlhttp.addEventListener('readystatechange', function() {
        // Check if the XMLHttpRequest is finished and response is ready
        if (this.readyState === 4) {

            var response = this.response;
            //Check if the content type is json
            if (this.getResponseHeader("content-type") == "application/json" && this.responseType != "blob")
                response = app._getResponse(this.response);

            // Check if the XMLHttpRequest is "OK"
            if (this.status >= 200 && this.status < 300) {
                success(response, this);
            } else {
                let failureFunc = function(resp) {
                    if (!options.disableAuthResult && (this.status == 401 || this.status == 403)) {
                        app.handleAuthError(resp);
                        return;
                    }
                    failure(resp);
                }.bind(this);
                if (this.responseType == "blob") {
                    app._processBlobResponse(this.response, failureFunc);
                    return;
                }
                failureFunc(response);

            }
        }
    });
    return xmlhttp;
};

app._processBlobResponse = function(response, callback) {
    let reader = new FileReader();
    reader.addEventListener('loadend', function(e) {
        callback(app._getResponse(e.srcElement.result));
    });
    reader.readAsText(response);
};

app._getResponse = function(responseBody) {
    try {
        return JSON.parse(responseBody);
    } catch (e) {
        console.log("Tried parsing: ", responseBody);
        app.handleFailure({
            status: 500,
            messageCode: "JSONParseException"
        });
        return;
    }
};

(function(app) {
    if (document.readyState === 'complete') {
        app.start();
    } else {
        window.addEventListener("load", app.start);
    }
})(app);

/* PROTOTYPE CHANGES */
String.prototype.toProperCase = function() {
    if (this.length < 0) return;
    var lower = this.toLowerCase();
    return lower.charAt(0).toUpperCase() + lower.slice(1);
};

String.prototype.validEmail = function() {
    return (/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/.test(this));
}

/**
pluralize: Pluralize the current string given a number.
length: The number dictating if the string should be pluralized or not
pluralizedForm: how the string should be pluralized (s, es, etc) as a string. Default "s"
*/
String.prototype.pluralize = function(length, pluralizedForm) {
    if (length == 1) return this;
    return this + (pluralizedForm || "s");
};

Date.prototype.toDateInputValue = (function() {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset());
    return local.toJSON().slice(0, 10);
});

Date.prototype.toPrettyDate = (function(showTime) {
    if(!isFinite(this))
        return "Never";
        
    var monthNames = [
        "January", "February", "March",
        "April", "May", "June", "July",
        "August", "September", "October",
        "November", "December"
    ];

    var day = this.getDate();
    var monthIndex = this.getMonth();
    var year = this.getFullYear();

    var time = ' at ' + (this.getHours() + "").padStart(2, "0") + ":" + (this.getMinutes() + "").padStart(2, "0");

    return day + ' ' + monthNames[monthIndex] + ' ' + year + (showTime ? time : "");
});

function getQueryParameterByName(name) {
    var url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

//POLYFILL PADSTART
// https://github.com/uxitten/polyfill/blob/master/string.polyfill.js
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/padStart
if (!String.prototype.padStart) {
    String.prototype.padStart = function padStart(targetLength, padString) {
        targetLength = targetLength >> 0; //floor if number or convert non-number to 0;
        padString = String(padString || ' ');
        if (this.length > targetLength) {
            return String(this);
        } else {
            targetLength = targetLength - this.length;
            if (targetLength > padString.length) {
                padString += padString.repeat(targetLength / padString.length); //append to original to ensure we are longer than needed
            }
            return padString.slice(0, targetLength) + String(this);
        }
    };
}

// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

