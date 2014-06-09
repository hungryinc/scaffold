'use strict';

var express = require('express');
var url = require('url');
var app = express();

/**
 * configuration
 */
app.set('views', __dirname + '/app');
app.engine('html', require('ejs').renderFile);
app.set('view engine', 'ejs');
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.cookieParser());
app.use(express.compress());
app.use(express.static(__dirname + '/app'));
app.use(app.router);

app.use(require('connect-livereload')({
    port: 35729
}));

var gulp = require('child_process').spawn('gulp', [], {stdio:'inherit'})

/**
 * Consumer
 */
app.get('*', function(req, res) {
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