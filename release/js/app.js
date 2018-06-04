/* global MessageCode, Resources, Modal,flexibility,location,navigator */
var app = app || {
    startup: [],
    afterStartup: []
};
//App.startup contains an array of functions that will be executed sequentially when the page is done loading
app.DEFAULTS = {
    REQUEST_TIMEOUT: 20 * 1000,
    ALLOWED_EXTENSIONS: ['pdf','docx','doc','pptx','ppt','xlsx','csv','jpeg','jpg','png', 'txt', 'zip']
};

app.start = function() {
    flexibility(document.body); //Make everything a flexbox (Polyfill.)
    console.log("NEXCHANGE Started.");
    for (var i = 0; i < app.startup.length; i++) {
        try {
            app.startup[i].call(app);
        } catch (e) {
            app.logUi("Pre-startup error: : "+e.message);
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
            app.logUi("Startup error: "+e.message);
            app.handleFailure({
                status: 500,
                messageCode: "StartupError"
            });
            console.log(e);
        }
    }
};

app.storageEnabled = function(){
    try {
        if(typeof(Storage) === "undefined") return false;
        if (typeof(window) !== 'undefined' && 'localStorage' in window) { 
            window.localStorage.setItem("nexchange_test_storage", true);
            window.localStorage.removeItem("nexchange_test_storage");
        }
        return true;
    }
    catch(err) {
        return false;
    }
};

app.store = function(key, data) {
    if (!app.storageEnabled()) {
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
    if (!app.storageEnabled()) {
        //Store cookies instead
        app.getCookie(key);
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
            return JSON.parse(c.substring(name.length, c.length));
        }
    }
    return null;
};

app.handleFailure = function(response) {
    console.warn(response.status, response.messageCode);
    if(response.requested != "/"+Resources.Log.location){
        app.logUi(response);
    }
    if (typeof Modal === "undefined") {
        return;
    }
    new Modal("Error", MessageCode(response.messageCode), null, {
        text: "Okay"
    }).show();
};

app.handleAuthError = function(response) {
    console.warn(response.status, response.messageCode);
    let logoutFunction = function() {
        var f = function() {
            // Only save the last location if the token expired
            if(response.messageCode == "AuthenticationExpired"){
                // Store the current location as the next location after login
                app.store("login_nextLocation", window.location.href);
            } else {
                app.store("navbar", null);
                app.store("login_nextLocation", null);
            }
            // Redirect the user to the login page
            window.location = "./login";
        };
        // Delete the user's JWT and xsrf token (this is necessary because of HTTPOnly)
        Resources.Auth.DELETE(f, f, true);
    };
    if (typeof Modal === "undefined") {
        logoutFunction();
        return;
    }
    if (response.messageCode == "AuthorizationFailed") {
        let successData = {
            text: MessageCode(response.messageCode + "Button"),
            callback: logoutFunction
        };
        new Modal("Not Authorized", MessageCode(response.messageCode), successData, false).show();
        return;
    }
    let successData = {
        text: MessageCode(response.messageCode + "Button"),
        callback: logoutFunction
    };
    new Modal("Not Authenticated", MessageCode(response.messageCode), successData, false).show();
};

app.post = function(resource, data, success, failure, options) {
    let request = this._generateRequest(success, failure, options);
    request.open("POST", resource.location);
    // Set the xsrf token in header
    request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
    request.setRequestHeader("content-type", "application/json");
    // Enable the loading spinner 
    document.body.classList.add("load");
    request.send(JSON.stringify(data || {}));
    return request;
};

app.put = function(resource, data, success, failure, options) {
    let request = this._generateRequest(success, failure, options);
    request.open("PUT", resource.location);
    // Set the xsrf token in header
    request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
    request.setRequestHeader("content-type", "application/json");
    // Enable the loading spinner 
    document.body.classList.add("load");
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
    request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
    request.setRequestHeader("content-type", "application/json");
    // Enable the loading spinner 
    document.body.classList.add("load");
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
    request.setRequestHeader("x-csrftoken", app.getCookie("xsrfToken"));
    // Enable the loading spinner 
    document.body.classList.add("load");
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
            
            // Disable the loading spinner 
            document.body.classList.remove("load");

            var response = this.response;
            //Check if the content type is json
            if (this.getResponseHeader("content-type") == "application/json" && this.responseType != "blob")
                response = app._getResponse(this.response);

            // Check if the XMLHttpRequest is "OK"
            if (this.status >= 200 && this.status < 300) {
                success(response, this);
            } else {
                if(!navigator.onLine){
                    var successBtn = {text: "Okay",
                    callback: function(){
                        location.reload();
                    }};
                    
                    new Modal("Error", MessageCode("NoInternet"), successBtn, false).show();
                    return;
                }
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

app.logUi = function(message){
    console.log("UILog: ",message);
    if(Resources && Resources.Log){
        let failureFunc = function(response){
            console.warn(response.status, response.messageCode);
        }
        Resources.Log.POST(message, null, failureFunc);
    }
}

app._getResponse = function(responseBody) {
    try {
        return JSON.parse(responseBody);
    } catch (e) {
        app.logUi("Cannot parse JSON: "+e.message);
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
    if(length === 0 || (length !== 1 && length != false))
        return this + (pluralizedForm || "s");
    return this+""; //Needs "" or else will return a string object instead of string primitive.
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
    
    return monthNames[monthIndex] + ' ' + day + ' ' + year + (showTime ? time : "");
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
    //Pads the start of a string with the padString until it reaches the targetLength
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

String.prototype.sectionify = function sectionify(asLabelAndValue){
    function piecePadder(piece){
        if(piece.contains("-")){
            //Its a range of numbers. Ex: 13-14
            var range = piece.split("-");
            return range[0].padStart(5, "0") + "-" + range[1].padStart(5, "0");
        }else{
            //Its a single number. Ex: 13
            return piece.padStart(5, "0");
        }
    }
    
    var prefix = "Section";
    if(this.contains(",") || this.contains("-"))
        prefix += "s";
        
    var padded = []
    if(this.contains(",")){
        var pieces = this.split(",");
        for(var i = 0; i<pieces.length; i++){
            var piece = pieces[i];
            padded.push(piecePadder(piece));
        }
    }else{
        padded.push(piecePadder(this));
    }
    
    var postfix = padded.join();
    
    if(asLabelAndValue)
        return [prefix, postfix]
    
    return prefix + " " + postfix;
};

String.prototype.nescape = function escapeHtml() {
    return this.replace(/[\"&<>]/g, function (a) {
        return { '"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;' }[a];
    });
};

String.prototype.contains = function contains(elem){
    return this.indexOf(elem) !== -1;
};

function sectionVerification(section){
    if(!section.contains(",")){
        if(section.contains("-")){
            return validateDash(section); //it only has a range. ex: 51-56
        }
        return !isNaN(section); //It only has a number (allegedly) ex: 31; ex: notANumber
    }
    
    var splits = section.split(",");
    for(var i = 0; i<splits.length; i++){
        var split = splits[i];
        if(!split) return false;
        if(split.contains("-")){
            if(!validateDash(split)) return false;
            continue;
        }
        
        if(isNaN(split)) return false;
    }
    return true;
    
    function validateDash(dashed){
        var dashSplit = dashed.split("-");
        for(var i = 0; i<dashSplit.length; i++){
            if(!dashSplit[i]) return false;
            if(isNaN(dashSplit[i])) return false;
        }
        return true;
    }
};

function generatePTag(header, content, spanned){
    let ptag = document.createElement("P");
    ptag.innerText = header + ": ";
    if(spanned){
        let spantag = document.createElement("SPAN");
        spantag.innerText = content;
        ptag.appendChild(spantag);
    }else{
        ptag.innerText += content;
    }
    return ptag;
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

function getExtension(fileName){
    var fileComponents = fileName.split(".");
    var extension = fileComponents[fileComponents.length - 1];
    return extension.toLowerCase();
}
