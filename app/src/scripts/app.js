'use strict';

require("../../vendor/angular/angular");
require("../../vendor/angular-resource/angular-resource");
require("../../vendor/angular-route/angular-route");
require("../../vendor/angular-animate/angular-animate");
require("../../vendor/angular-sanitize/angular-sanitize");
require("../../vendor/angular-ui-router/release/angular-ui-router");
require("../../vendor/ngQuickDate/dist/ng-quick-date");
require("./modules/cookies");
require("../../vendor/ng-prettyjson/dist/ng-prettyjson.min");
require("../../vendor/behave/behave");

console.log("app.js Loaded");

var dateLocalizer = angular.module('dateLocalizeFilter', []).filter('dateLocalize', function() {
    return function(utcDate) {
        var dt = new Date(utcDate + 'Z').getTime();
        return dt;
    }
});

var scaffold = angular.module('Scaffold', ['ngRoute', 'ui.router', 'ngAnimate', 'ngResource', 'ngQuickDate', 'dateLocalizeFilter', 'cookies', 'ngPrettyJson']);

scaffold.config(require("./routes/MainRoutes"));

scaffold.controller('ObjectCtrl', ["$scope", "ObjectService", "$rootScope", "$cookies", require("./controllers/ObjectCtrl")]);
scaffold.controller('EndpointCtrl', ["$scope", "EndpointService", "$rootScope", "$cookies", require("./controllers/EndpointCtrl")]);

scaffold.controller('ListCtrl', ["$scope", require("./controllers/ListCtrl")]);
scaffold.controller('MainCtrl', ["$scope", "MainService", "$rootScope", "$cookies", require("./controllers/MainServiceCtrl")]);


scaffold.service('MainService', ["$resource", "$q", "$rootScope", require("./services/MainService")]);
scaffold.service('ObjectService', ["$resource", "$q", "$rootScope", require("./services/ObjectService")]);
scaffold.service('EndpointService', ["$resource", "$q", "$rootScope", require("./services/EndpointService")]);

angular.bootstrap(document, ['Scaffold']);