'use strict';

module.exports = function($scope, DashboardService, $rootScope, $cookies, EndpointTestService, ObjectService, EndpointService, $modal) {

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

        $scope.modal = {
            "title": "Title",
            "content": "Hello Modal<br />This is a multiline message!"
        };

    };

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.popover = {
        "title": "Title",
        "content": "Hello Popover<br />This is a multiline message!"
    };

    $scope.testEndpoint = function(endpoint) {
        console.log('DashboardCtrl.testEndpoint');
        for (var i = 0; i < $scope.projects.length; i++) {
            var project = $scope.projects[i];
            for (var j = 0; j < project.endpoints.length; j++) {
                var value = project.endpoints[j]
                if (endpoint.id == value.id) {
                    var name = convertToSlug(project.name);
                    var method = endpoint.method;
                    var uri = endpoint.uri;
                    var request_headers = endpoint.request_headers;

                    EndpointTestService.testEndpoint(name, method, uri, request_headers).then(function(response) {

                        $scope.response = response;
                        $scope.request_headers = request_headers;

                        var response_headers = [];

                        for (var i in $scope.response.headers) {
                            var header = {
                                key: i,
                                value: $scope.response.headers[i]
                            }
                            response_headers.push(header);
                        };
                        $scope.response_headers = response_headers;
                        console.log(response_headers);



                    }).then(function() {

                        var response = $scope.response;
                        var request_headers = $scope.request_headers;

                        console.log(request_headers);
                        console.log(response.headers);

                        // Pre-fetch an external template populated with a custom scope
                        var modal = $modal({
                            title: response.config.url,
                            scope: $scope,
                            template: '/src/html/strap-templates/testEndpoint.html',
                            show: true
                        });
                        // Show when some event occurs (use $promise property to ensure the template has been loaded)
                        $scope.showModal = function() {
                            modal.$promise.then(modal.show);
                        };
                    });
                }
            };
        };
    };

    $scope.createObjectModal = function() {
        var modal = $modal({
            title: "New Object",
            scope: $scope,
            template: '/src/html/strap-templates/objectForm.html',
            show: true
        });
        // Show when some event occurs (use $promise property to ensure the template has been loaded)
        $scope.showModal = function() {
            modal.$promise.then(modal.show);
        };
    };

    $scope.createObject = function(project, name, description, json) {

        try {
            json = JSON.parse(json);
        } catch (error) {

        }

        var objectJSON = {
            project_id: project.id,
            name: name,
            description: description,
            json: json
        }

        ObjectService.create(objectJSON).then(function() {
            refreshList()
        });

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