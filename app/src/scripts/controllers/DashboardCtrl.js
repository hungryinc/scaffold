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
                    var name = convertToSlug(project.name);
                    var method = endpoint.method;
                    var uri = endpoint.uri;
                    var request_headers = endpoint.request_headers;

                    EndpointTestService.testEndpoint(name, method, uri, request_headers).then(function(response) {
                        console.log(response);

                        $scope.response = response;
                        $scope.request_headers = request_headers;



                    }).then(function() {

                        var response = $scope.response;
                        var request_headers = $scope.request_headers;

                        var myModal = $modal({
                            title: response.config.url,
                            content: testEndpointContent(response, request_headers),
                            show: true
                        });
                        $scope.showModal = function() {
                            myModal.$promise.then(myModal.show);
                        };
                        $scope.hideModal = function() {
                            myModal.$promise.then(myModal.hide);
                        };
                    });
                }
            };
        };
    };

    var testEndpointContent = function(response, request_headers) {

        var template = "";

        template += '<div class="dashboard">';


        //REQUEST HEADERS
        template += '<h2>Request Headers</h2>';
        var requestHeaderTable = '<table border = "1" style="text-align: center; padding: 10px;">';
        for (var i = 0; i < request_headers.length; i++) {
            var header = request_headers[i];
            var string = "<tr><td>" + header['key'] + "</td><td>" + header['value'] + "</td></tr>";
            requestHeaderTable += string;
        };
        requestHeaderTable += "</table>"
        template += requestHeaderTable;


        //JSON DATA
        template += '<h2>JSON</h2>';
        template += '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';


        //RESPONSE HEADERS
        template += '<h2>Response Headers</h2>';
        var responseHeaderTable = '<table border = "1" style="text-align: center; padding: 10px;">';
        for (var i in response.headers) {
            var string = "<tr><td>" + i + "</td><td>" + response.headers[i] + "</td></tr>";
            responseHeaderTable += string;
        };
        responseHeaderTable += "</table>"
        template += responseHeaderTable;

        template += "</div>";
        return template;
    }

    $scope.createProject = function() {
        var myModal = $modal({
            title: "New Project",
            content: "TEST",
            show: true
        });
        $scope.showModal = function() {
            myModal.$promise.then(myModal.show);
        };
        $scope.hideModal = function() {
            myModal.$promise.then(myModal.hide);
        };
    };

    //USE THIS TO REMOVE $$HASHKEY WHEN USING NG-REPEAT
    $scope.hideHashkey = function(object) {
        var output;

        output = angular.toJson(object);
        // output = angular.fromJson(output);

        return angular.fromJson(output);
    };

    var convertToSlug = function(str) {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
        var to = "aaaaaeeeeeiiiiooooouuuunc------";
        for (var i = 0, l = from.length; i < l; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-'); // collapse dashes

        return str;
    };
}