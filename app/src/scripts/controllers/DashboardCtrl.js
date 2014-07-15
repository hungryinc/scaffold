'use strict';

module.exports = function($scope, DashboardService, $rootScope, $cookies, EndpointTestService, ObjectService, EndpointService, $modal, $alert) {

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

    $scope.createEndpointModal = function() {
        $scope.request_headers = [];
        $scope.addRequestHeader = function(key, value) {
            if (key != null && value != null) {
                var header = {
                    key: key,
                    value: value
                };


                $scope.request_headers.push({
                    key: key,
                    value: value
                });
                key = "";
                value = "";
            } else {
                console.log("Please insert BOTH key and value");
            }
        };
        $scope.removeRequestHeader = function(header) {
            var index = $scope.request_headers.indexOf(header);
            if (index > -1) {
                $scope.request_headers.splice(index, 1);
            }
        };

        $scope.response_headers = [];
        $scope.addResponseHeader = function(key, value) {
            if (key != null && value != null) {
                var header = {
                    key: key,
                    value: value
                };


                $scope.response_headers.push({
                    key: key,
                    value: value
                });
                key = "";
                value = "";
            } else {
                console.log("Please insert BOTH key and value");
            }
        };
        $scope.removeResponseHeader = function(header) {
            var index = $scope.response_headers.indexOf(header);
            if (index > -1) {
                $scope.response_headers.splice(index, 1);
            }
        };

        $scope.input_values = [];
        $scope.addInputValue = function(name, type) {
            if (name != null && type != null) {
                var inputValue = {
                    key: name,
                    value: type
                };


                $scope.input_values.push({
                    key: name,
                    value: type
                });
                name = "";
                type = "";
            } else {
                console.log("Please insert BOTH name and type");
            }
        };
        $scope.removeInputValue = function(inputValue) {
            var index = $scope.input_values.indexOf(inputValue);
            if (index > -1) {
                $scope.input_values.splice(index, 1);
            }
        };

        $scope.HTTPMethods = ['GET', 'POST', 'PUT', 'DELETE'];
        $scope.InputTypes = ['STRING', 'NUMBER', 'BOOLEAN', 'JSON', 'MIXED'];

        var modal = $modal({
            title: "New Endpoint",
            scope: $scope,
            template: '/src/html/strap-templates/endpointForm.html',
            show: true
        });
        // Show when some event occurs (use $promise property to ensure the template has been loaded)
        $scope.showModal = function() {
            modal.$promise.then(modal.show);
        };
        $scope.hideModal = function() {
            modal.$promise.then(modal.hide);
        };
    };

    $scope.createEndpoint = function(project, name, uri, method, response_code, request_headers, response_headers, json, input_values) {

        try {
            json = JSON.parse(json);
        } catch (error) {

        }

        var endpointJSON = {
            name: name,
            uri: uri,
            method: method,
            response_code: response_code,
            request_headers: createJSON(request_headers),
            response_headers: createJSON(response_headers),
            json: json,
            input: createJSON(input_values)
        };

        try {
            endpointJSON.project_id = project.id;
        } catch (error) {

        }

        EndpointService.create(endpointJSON).then(function(response) {
            $scope.hideModal();
            refreshList()
        }, function(error) {
            generateAlert("ERROR:", error.data.message);
        });

    };

    $scope.removeEndpointModal = function(endpoint) {
        $scope.endpointToRemove = endpoint;
        var modal = $modal({
            title: "Delete Endpoint",
            scope: $scope,
            template: '/src/html/strap-templates/removeEndpoint.html',
            show: true
        });
        $scope.showModal = function() {
            modal.$promise.then(modal.show);
        };
        $scope.hideModal = function() {
            modal.$promise.then(modal.hide);
        };
    };

    $scope.removeEndpoint = function(endpointToRemove) {
        EndpointService.remove(endpointToRemove.id).then(function(endpoint) {
            $scope.hideModal();
            console.log(endpoint);
            refreshList()
        }, function(error) {
            generateAlert("ERROR:", error.data.message);
        });
    };

    $scope.testEndpointButton = function(endpoint) {
        console.log('DashboardCtrl.testEndpoint');
        for (var i = 0; i < $scope.projects.length; i++) {
            var project = $scope.projects[i];
            for (var j = 0; j < project.endpoints.length; j++) {
                var value = project.endpoints[j]
                if (endpoint.id == value.id) {
                    var name = convertToSlug(project.name);
                    var request_headers = endpoint.request_headers;

                    if (['POST', 'PUT'].indexOf(endpoint.method.toUpperCase()) > -1) {
                        $scope.name = name
                        $scope.endpoint = endpoint;
                        $scope.request_headers = request_headers;

                        var modal = $modal({
                            title: "Test Endpoint",
                            scope: $scope,
                            template: '/src/html/strap-templates/testEndpointInput.html',
                            show: true
                        });
                        // Show when some event occurs (use $promise property to ensure the template has been loaded)
                        $scope.showModal = function() {
                            modal.$promise.then(modal.show);
                        };
                        $scope.hideModal = function() {
                            modal.$promise.then(modal.hide);
                        };
                    } else {
                        var input = null;
                        $scope.testEndpoint(name, endpoint, input, request_headers);
                    }
                }
            }
        }
    };

    $scope.testEndpoint = function(name, endpoint, input, request_headers) {
        EndpointTestService.testEndpoint(name, endpoint, input).then(function(response) {

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

            $scope.hideModal();

            testEndpointModal(response);

        }, function(error) {
            generateAlert("ERROR:", error.data.message);
            return error;
        });
    };

    var testEndpointModal = function(response) {
        var response = $scope.response;
        var request_headers = $scope.request_headers;

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
        $scope.hideModal = function() {
            modal.$promise.then(modal.hide);
        };
    }


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
        $scope.hideModal = function() {
            modal.$promise.then(modal.hide);
        };
    };

    $scope.createObject = function(project, name, description, json) {

        try {
            json = JSON.parse(json);
        } catch (error) {

        }

        var objectJSON = {
            name: name,
            description: description,
            json: json
        };

        try {
            objectJSON.project_id = project.id;
        } catch (error) {

        }

        ObjectService.create(objectJSON).then(function(response) {
            $scope.hideModal();
            refreshList()
        }, function(error) {
            generateAlert("ERROR:", error.data.message);
        });

    };

    var generateAlert = function(title, content) {
        var myAlert = $alert({
            title: title,
            content: content,
            placement: 'top-right',
            type: 'warning',
            keyboard: true,
            duration: 3,
            show: true
        });
    };

    $scope.removeObjectModal = function(object) {
        $scope.objectToRemove = object;
        var modal = $modal({
            title: "Delete Object",
            scope: $scope,
            template: '/src/html/strap-templates/removeObject.html',
            show: true
        });
        $scope.showModal = function() {
            modal.$promise.then(modal.show);
        };
        $scope.hideModal = function() {
            modal.$promise.then(modal.hide);
        };
    };

    $scope.removeObject = function(objectToRemove) {
        ObjectService.remove(objectToRemove.id).then(function(object) {
            $scope.hideModal();

            if (object.warning != null) {
                var myModal = $modal({
                    title: 'WARNING',
                    content: object.warning,
                    show: true
                });
                $scope.showModal = function() {
                    myModal.$promise.then(myModal.show);
                };
                $scope.hideModal = function() {
                    myModal.$promise.then(myModal.hide);
                };
            }

            console.log(object);
            refreshList()
        }, function(error) {
            generateAlert("ERROR:", error.data.message);
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

    var createJSON = function(array) {
        var json = {};

        for (var i = 0; i < array.length; i++) {
            json[array[i].key] = [array[i].value, true];
        };

        return json;
    };
}