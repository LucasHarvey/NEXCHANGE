/* global Resources,MessageCode */
var app = app || {
    startup: [],
    afterStartup: []
};

app.navbar = {

    _generateNavbar: function(urlList) {
        let navBarContent = document.getElementById('navmain');
        let logout = document.getElementById('logout');

        for (var i = 0; i < urlList.length; i++) {
            var link = document.createElement('a');
            link.innerText = urlList[i].content;
            link.href = urlList[i].url;
            navBarContent.insertBefore(link, logout);
        }
    },

    navbarSuccess: function(data) {
        app.navbar._generateNavbar(data.payload);
        app.store("navbar", data.payload)
    },

    getNavbar: function() {
        Resources.Navbar.GET(this.navbarSuccess);
    }

};

app.afterStartup.push(function navbarAfterStartup() {
    var navbar = app.getStore("navbar");
    if(navbar){
        app.navbar._generateNavbar(navbar);
        return;
    }
    
    // If no navbar is found in localstorage/cookies, retrieve it from the server
    app.navbar.getNavbar();
});
