'use strict';

module.exports = function($resource, $q, $rootScope, $http) {

    console.log("EndpointTestService Loaded");

    var generateHeaders = function(request_headers) {
        var result = {};
        for (var i = 0; i < request_headers.length; i++) {
            var header = request_headers[i];
            result[header['key']] = header['value'];
        };
        return result;

    }



    this.testEndpoint = function(name, endpoint) {
        console.log("EndpointTestService.testEndpoint");

        var URI = formatURI(endpoint.uri);

        var URL = 'http://' + name + '.api.scaffold.dev' + URI;

        var deferred = $q.defer();

        $http({
            method: endpoint.method,
            url: URL,
            headers: generateHeaders(endpoint.request_headers)

        }).success(function(data, status, headers, config) {
            console.log('Test Successful');

            var results = [];
            results.data = data;
            results.headers = headers();
            results.status = status;
            results.config = config;

            deferred.resolve(results);

        }).error(function(data, status) {
            console.log('Test Failed');
            deferred.reject(data, status, headers, config);
        });

        return deferred.promise;
    }

    this.getObjects = function() {
        console.log("DashboardService.getObjects");

        var deferred = $q.defer();

        Objects.getObjects({}, function(response) {

            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    }


    var rename = function() {
        console.log("MainService.rename");
        var object = this;
        var newname = prompt("What would you like to rename the object to?");
        Tasks.rename({
            id: object.id,
            name: newname
        }, function() {
            $rootScope.$emit('afterModification');
        });
    };

    var changeDescription = function(newDescription) {
        console.log("MainService.changeDescription", newDescription);
        var object = this;
        Objects.changeDescription({
            id: object.id,
            description: newDescription
        }, function() {
            $rootScope.$emit('afterModification');
        });
    }

    var changeJSON = function(newJSON) {
        console.log("MainService.changeJSON", newJSON);
        var object = this;
        Objects.changeJSON({
            id: object.id,
            json: newJSON
        }, function() {
            $rootScope.$emit('afterModification');
        });
    }

    this.create = function(data) {
        console.log("MainService.create");
        var deferred = $q.defer();

        Objects.create(data, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    };

    var remove = function() {
        console.log("MainService.remove");
        var object = this;
        if (confirm("Do you really want to remove this object?")) {
            Objects.remove({
                taskId: object.id,
            }, function() {
                console.log(object.name + " removed");
                $rootScope.$emit('afterModification');
            });
        } else {
            console.log("Deletion Cancelled");
        }
    };

    var formatURI = function(uri) {
        var regexArray = uri.match(/\/:([a-zA-Z]+){([a-zA-Z]+)}/g)

        if (regexArray != null) {
            for (var string in regexArray) {
                if (string.toUpperCase().indexOf('STRING') != -1) {
                    uri = uri.replace(string, '/string');

                } else if (string.toUpperCase().indexOf('NUMBER') != -1) {
                    uri = uri.replace(string, '/123');

                }
            }
        }

        return uri;
    }

    return this;
}