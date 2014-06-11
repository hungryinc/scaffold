'use strict';

require("../../vendor/angular/angular");
require("../../vendor/angular-resource/angular-resource");
require("../../vendor/angular-route/angular-route");
require("../../vendor/angular-animate/angular-animate");
require("../../vendor/angular-sanitize/angular-sanitize");
require("../../vendor/angular-ui-router/release/angular-ui-router");

console.log("app.js Loaded");

var scaffold = angular.module('Scaffold', ['ngRoute', 'ui.router', 'ngAnimate', 'ngResource']);

scaffold.config(require("./routes/MainRoutes"));

scaffold.controller('ObjectCtrl', ["$scope", require("./controllers/ObjectCtrl")]);
scaffold.controller('ListCtrl', ["$scope", require("./controllers/ListCtrl")]);
scaffold.controller('MainCtrl', ["$scope", "MainService", require("./controllers/MainServiceCtrl")]);

scaffold.service('MainService', ["$resource", "$q", require("./services/MainService")]);

angular.bootstrap(document, ['Scaffold']);