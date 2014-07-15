'use strict';

module.exports = function($scope, DashboardService, $rootScope, $cookies, EndpointTestService, ObjectService, EndpointService, $modal, $timeout) {

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

        $scope.alerts = [];

        $scope.addAlert = function(text) {
            $scope.alerts.push({
                type: 'danger',
                msg: text
            });
            console.log($scope.alerts);

            $timeout(function() {
                $scope.alerts.splice(0, 1);
                console.log("ALERT REMOVED");
                console.log($scope.alerts);
            }, 3000);
        }

        $scope.closeAlert = function(index) {
            console.log(index);
            $scope.alerts.splice(index, 1);
        };

    };

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.createEndpointModal = function() {

        $scope.request_headers = [];
        $scope.addRequestHeader = function(key, value) {
            console.log(key, value);
            if (key != null && key != "" && value != null && value != "") {
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
                $scope.addAlert("Please insert BOTH key and value");
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
            if (key != null && key != "" && value != null && value != "") {
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
                $scope.addAlert("Please insert BOTH key and value");
            }
        };
        $scope.removeResponseHeader = function(header) {
            var index = $scope.response_headers.indexOf(header);
            if (index > -1) {
                $scope.response_headers.splice(index, 1);
            }
        };

        $scope.input_values = [];
        $scope.addInputValue = function(name, type, required) {


            if (required == "YES") {
                required = true;
            } else if (required == "NO") {
                required = false;
            } else {
                required = null;
            }


            if (name != null && type != "SELECT" && required != null) {
                var inputValue = {
                    key: name,
                    value: [type, required]
                };


                $scope.input_values.push(inputValue);
                name = "";
                type = "";
            } else {
                $scope.addAlert("Please insert name and type");
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

        $scope.selectedProject = $scope.projects[0];
        $scope.setProject = function(project) {
            $scope.selectedProject = project;
        };

        $scope.selectedMethod = "SELECT";
        $scope.setMethod = function(method) {
            $scope.selectedMethod = method;
        };

        $scope.selectedInputType = "SELECT";
        $scope.setInputType = function(value) {
            $scope.selectedInputType = value;
        };

        $scope.selectedInputRequired = "SELECT";
        $scope.setInputRequired = function(value) {
            $scope.selectedInputRequired = value;
        };

        console.log($scope.selectedProject);

        var modal = $modal.open({
            scope: $scope,
            templateUrl: '/src/html/ui-bootstrap-templates/endpointForm.html',
        });

        modal.result.then(function() {

        }, function() {
            console.log('Modal dismissed at: ' + new Date());
        });
    };

    $scope.createEndpoint = function(project, name, uri, method, response_code, request_headers, response_headers, json, input_values, $close) {

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
            $close('success');
            refreshList()
        }, function(error) {
            $scope.addAlert(error.data.message);
        });

    };

    $scope.removeEndpointModal = function(endpoint) {
        $scope.endpointToRemove = endpoint;

        var modal = $modal.open({
            scope: $scope,
            templateUrl: '/src/html/ui-bootstrap-templates/removeEndpoint.html',
        });

        modal.result.then(function() {

        }, function() {
            console.log('Modal dismissed at: ' + new Date());
        });
    };

    $scope.removeEndpoint = function(endpointToRemove, $close) {
        EndpointService.remove(endpointToRemove.id).then(function(endpoint) {
            $close('success');
            console.log(endpoint);
            refreshList()
        }, function(error) {
            $scope.addAlert(error.data.message);
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

                        var modal = $modal.open({
                            scope: $scope,
                            templateUrl: '/src/html/ui-bootstrap-templates/testEndpointInput.html'
                        });

                        modal.result.then(function() {

                        }, function() {
                            console.log('Modal dismissed at: ' + new Date());
                        });
                    } else {
                        var input = null;
                        $scope.testEndpoint(name, endpoint, input, request_headers, null);
                    }
                }
            }
        }
    };

    $scope.testEndpoint = function(name, endpoint, input, request_headers, $close) {
        EndpointTestService.testEndpoint(name, endpoint, input).then(function(response) {


            if ($close != null) {
                $close();
            }

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

            if ($scope.hideModal) {
                $scope.hideModal();
            }

            testEndpointModal(response);

        }, function(error) {
            $scope.addAlert(error.data.message);
            return error;
        });
    };

    var testEndpointModal = function(response) {
        var response = $scope.response;
        var request_headers = $scope.request_headers;

        var modal = $modal.open({
            scope: $scope,
            templateUrl: '/src/html/ui-bootstrap-templates/testEndpoint.html'
        });

        modal.result.then(function() {

        }, function() {
            console.log('Modal dismissed at: ' + new Date());
        });
    }


    $scope.createObjectModal = function() {

        $scope.selectedProject = $scope.projects[0];
        $scope.setProject = function(project) {
            $scope.selectedProject = project;
        };


        var modal = $modal.open({
            scope: $scope,
            templateUrl: '/src/html/ui-bootstrap-templates/objectForm.html',
        });

        modal.result.then(function() {

        }, function() {
            console.log('Modal dismissed at: ' + new Date());
        });
    };

    $scope.createObject = function(project, name, description, json, $close) {

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
            $close('success');
            refreshList()
        }, function(error) {
            $scope.addAlert(error.data.message);
        });

    };

    $scope.removeObjectModal = function(object) {
        $scope.objectToRemove = object;

        var modal = $modal.open({
            scope: $scope,
            templateUrl: '/src/html/ui-bootstrap-templates/removeObject.html',
        });

        modal.result.then(function() {

        }, function() {
            console.log('Modal dismissed at: ' + new Date());
        });
    };

    $scope.removeObject = function(objectToRemove, $close) {
        ObjectService.remove(objectToRemove.id).then(function(object) {
            $close('success');

            if (object.warning != null) {

                $scope.objectWarning = object.warning;

                var modal = $modal.open({
                    scope: $scope,
                    templateUrl: '/src/html/ui-bootstrap-templates/removeObjectWarning.html',
                });

                modal.result.then(function() {

                }, function() {
                    console.log('Modal dismissed at: ' + new Date());
                });
            }

            console.log(object);
            refreshList()
        }, function(error) {
            $scope.addAlert(error.data.message);
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
            json[array[i].key] = array[i].value;
        };

        return json;
    };
}