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
require("../../vendor/angular-strap/dist/angular-strap");
require("../../vendor/angular-strap/dist/angular-strap.tpl");

console.log("app.js Loaded");

var dateLocalizer = angular.module('dateLocalizeFilter', []).filter('dateLocalize', function() {
    return function(utcDate) {
        var dt = new Date(utcDate + 'Z').getTime();
        return dt;
    }
});

var scaffold = angular.module('Scaffold', ['ngRoute', 'ui.router', 'ngAnimate', 'ngSanitize', 'ngResource', 'ngQuickDate', 'dateLocalizeFilter', 'cookies', 'ngPrettyJson', 'mgcrea.ngStrap']);

scaffold.config(require("./routes/MainRoutes"));
scaffold.config(function($modalProvider) {
    angular.extend($modalProvider.defaults, {
        html: true
    });
});

scaffold.controller('ObjectCtrl', ["$scope", "ObjectService", "$rootScope", "$cookies", require("./controllers/ObjectCtrl")]);
scaffold.controller('EndpointCtrl', ["$scope", "EndpointService", "$rootScope", "$cookies", require("./controllers/EndpointCtrl")]);

scaffold.controller('ListCtrl', ["$scope", require("./controllers/ListCtrl")]);
scaffold.controller('DashboardCtrl', ["$scope", "DashboardService", "$rootScope", "$cookies", "EndpointTestService", "ObjectService", "EndpointService", "$modal", require("./controllers/DashboardCtrl")]);

scaffold.service('DashboardService', ["$resource", "$q", "$rootScope", require("./services/DashboardService")]);
scaffold.service('ObjectService', ["$resource", "$q", "$rootScope", require("./services/ObjectService")]);
scaffold.service('EndpointService', ["$resource", "$q", "$rootScope", require("./services/EndpointService")]);
scaffold.service('EndpointTestService', ["$resource", "$q", "$rootScope", "$http", require("./services/EndpointTestService")]);

angular.bootstrap(document, ['Scaffold']);