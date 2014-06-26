"use strict";

module.exports = function($stateProvider, $urlRouterProvider, $routeProvider, $locationProvider) {

    console.log("MainRoutes Loaded");

    $locationProvider.html5Mode(true).hashPrefix("!");

    $stateProvider.state("Dashboard", {
        url: "/scaffold/dashboard",
        views: {
            layout: {
                templateUrl: "/release/html/layouts/scaffold/dashboard.html",
                controller: "DashboardCtrl"
            }
        },

    }).state("Service", {
        url: "/service",
        views: {
            layout: {
                templateUrl: "/release/html/layouts/service.html",
                controller: "MainCtrl"
            }
        },

    }).state("Objects", {
        url: "/scaffold/objects",
        views: {
            layout: {
                templateUrl: "/release/html/layouts/scaffold/objects.html",
                controller: "ObjectCtrl"
            }
        },

    }).state("Endpoints", {
        url: "/scaffold/endpoints",
        views: {
            layout: {
                templateUrl: "/release/html/layouts/scaffold/endpoints.html",
                controller: "EndpointCtrl"
            }
        },

    });


}