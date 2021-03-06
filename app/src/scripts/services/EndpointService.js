'use strict';

module.exports = function($resource, $q, $rootScope) {

    console.log("EndpointService Loaded");

    var URL = 'http://api.scaffold.dev/endpoints/:endpointId';

    var formatDate = function(date) {
        var t = date.split(/[- :]/);
        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);

        return date;
    }

    var Endpoints = $resource(URL, {}, {

        getById: {
            method: "GET",
            params: {
                endpointId: '@id'
            }
        },

        create: {
            method: "POST"
        },

        edit: {
            method: "PUT",
            params: {
                endpointId: '@id'
            }
        },

        remove: {
            method: "DELETE",
            params: {
                endpointId: '@id'
            }
        }


    });

    var format = function(endpoint) {
        return endpoint;
    }

    this.getAllEndpoints = function() {
        console.log("EndpointService.getAllEndpoints");
        var deferred = $q.defer();

        Endpoints.get({}, function(response) {
            for (var i = 0; i < response.data.length; i++) {
                response.data[i] = format(response.data[i]);
            };

            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    }

    this.edit = function(id, data) {
        console.log("EndpointService.edit", data);

        var deferred = $q.defer();

        Endpoints.edit({
            endpointId: id
        }, data, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            alert("ERROR: " + error.data.message);
            deferred.reject(error);
        });
        return deferred.promise;
    }

    this.create = function(data) {
        console.log("EndpointService.create");
        var deferred = $q.defer();

        Endpoints.create(data, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });
        return deferred.promise;
    };

    this.remove = function(id) {
        console.log("EndpointService.remove", id);

        var deferred = $q.defer();

        Endpoints.remove({
            endpointId: id
        }, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });
        return deferred.promise;
    }

    return this;
}