'use strict';

module.exports = function($scope, DashboardService, $rootScope, $cookies, EndpointTestService) {

    console.log("DashboardCtrl Loaded");

    var refreshList = function() {
        console.log('DashboardCtrl.refreshList');
        DashboardService.getProjects().then(function(projects) {
            $scope.projects = projects;
        });

        DashboardService.getEndpoints().then(function(endpoints) {
            $scope.endpoints = endpoints;
        });

        DashboardService.getObjects().then(function(objects) {
            $scope.objects = objects;
        });

    };

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.testEndpoint = function(endpoint) {
        console.log('DashboardCtrl.testEndpoint');
        for (var i = 0; i < $scope.projects.length; i++) {
            var project = $scope.projects[i];
            for (var j = 0; j < project.endpoints.length; j++) {
                var value = project.endpoints[j]
                if (endpoint.id == value.id) {
                    console.log(value.id);
                    var name = project.name;
                    var uri = endpoint.uri;
                    var request_headers = endpoint.request_headers;

                    EndpointTestService.testEndpoint(name, uri, request_headers).then(function(result) {
                        console.log(result);

                    });
                }
            };
        };
    };

    $scope.editEndpoint = function(endpoint) {

    };

    //USE THIS TO REMOVE $$HASHKEY WHEN USING NG-REPEAT
    $scope.hideHashkey = function(object) {
        var output;

        output = angular.toJson(object);
        // output = angular.fromJson(output);

        return angular.fromJson(output);
    };
}