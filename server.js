'use strict';

var express = require('express');
var subdomain = require('subdomain');
var url = require('url');
var auth = function(req, res, next) {
    next()
};
var app = express();

app.config = {
    clientId: '32CE59F158ABE7839C652447673GH',
    clientSecret: '987A3CA5D2373F625C397C2EB376D',
    twitterConsumerKey: 'zbV9qTSIe6yHvkQew854WQ',
    twitterConsumerSecret: 'sNcIBN1dp7OMzEdU8emrooGHBoP0rsXKBKjSKw1R64'
}

app.configure('dev', function() {
    app.config.host = 'thehitch.dev';
    app.config.authHost = 'oauth.thehitch.dev';

    // Run gulp
    var gulp = require('child_process').spawn('gulp', [], {stdio:'inherit'})
})

app.configure('staging', function() {
    var auth = express.basicAuth(function(username, password) {
        return username === 'owners' && password === 'only';
    });

    app.config.host = 'staging.thehitch.com';
    app.config.authHost = 'oauth.staging.thehitch.com';
})

app.configure('production', function() {
    app.config.host = 'thehitch.com';
    app.config.authHost = 'oauth.thehitch.com';
})

/**
 * configuration
 */
app.set('views', __dirname + '/app');
app.engine('html', require('ejs').renderFile);
app.set('view engine', 'ejs');
app.use(require('prerender-node'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.cookieParser());
app.use(express.compress());
app.use(require('prerender-node'));
app.use(express.static(__dirname + '/app'));
app.use(subdomain({
    base: app.config.host,
    removeWWW: true
}));

app.use(app.router);

app.use(require('connect-livereload')({
    port: 35729
}));

app.options('/signup', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, OPTIONS");

    res.send();
})

app.options('/signin', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, OPTIONS");

    res.send();
})

/**
 * Venue Route
 */
app.get('/subdomain/venues/*', auth, function(req, res) {
    res.render('release/html/index.html');
});

/**
 * Twitter Login Routes
 */
app.get('/twitter', function(req, res) {
    require('./routes/twitter')(app, req, res);
});

/**
 * OAuth Login Route
 */
app.post('/signin', function(req, res) {
    require('./routes/signin')(app, req, res);
});

/**
 * OAuth Signup Route
 */
app.post('/signup', function(req, res) {
    require('./routes/signup')(app, req, res);
});

/**
 * Forgot Password Route
 */
app.post('/forgotpassword', function(req, res) {
    require('./routes/forgotpassword')(app, req, res);
});

/**
 * Reset Password Route
 */
app.post('/resetpassword', function(req, res) {
    require('./routes/resetpassword')(app, req, res);
});

/**
 * Refresh OAuth Token
 */
app.post('/refresh', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, OPTIONS");

    require('./routes/refresh')(app, req, res);
});

/**
 * Refresh OAuth Token
 */
app.options('/refresh', function(req, res) {
    res.setHeader("Access-Control-Allow-Origin", "*");
    res.setHeader("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, OPTIONS");

    res.send();
});

/**
 * Clear Beta Access
 */
app.get('/exit', function(req, res) {
    res.clearCookie('access');
    res.redirect(301, '/');
});

app.get('/sitemap.xml', function(req, res) {
    res.sendfile(__dirname + '/app/release/html/sitemap.xml')
});

/**
 * Consumer
 */
app.get('*', auth, function(req, res) {
    res.render('release/html/index.html');
});

//you may also need an error handler too (below), to serve a 404 perhaps?
app.use(function(err, req, res, next) {
    if (!err) {
        return next();
    } // you also need this line
    console.log('error: ' + err.stack);
    res.send('error!!!');
});

/**
 * Start server
 */
app.listen(process.env.PORT || 80, function() {
    console.log('listening on port %d using %s ', process.env.PORT || 80, app.get('env'));
});
