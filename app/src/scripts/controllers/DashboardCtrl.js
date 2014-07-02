'use strict';

module.exports = function($scope, DashboardService, $rootScope, $cookies, EndpointTestService, $modal) {

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

    var myModal = $modal({
        title: 'Title',
        content: 'Hello Modal<br />This is a multiline message!',
        show: false
    });
    $scope.showModal = function() {
        myModal.$promise.then(myModal.show);
    };
    $scope.hideModal = function() {
        myModal.$promise.then(myModal.hide);
    };



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
                    var method = endpoint.method;
                    var uri = endpoint.uri;
                    var request_headers = endpoint.request_headers;

                    EndpointTestService.testEndpoint(name, method, uri, request_headers).then(function(response) {
                        console.log(response);
                        // $scope.showModal();

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