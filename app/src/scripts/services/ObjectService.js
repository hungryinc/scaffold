'use strict';

module.exports = function($resource, $q, $rootScope) {

    console.log("MainService Loaded");

    var URL = 'http://localhost:8888/objects/:objectId';

    var formatDate = function(date) {
        var t = date.split(/[- :]/);
        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);

        return date;
    }

    var Objects = $resource(URL, {}, {

        getById: {
            method: "GET",
            params: {
                objectId: '@id'
            }
        },

        create: {
            method: "POST"
        },

        edit: {
            method: "PUT",
            params: {
                objectId: '@id'
            }
        },

        remove: {
            method: "DELETE",
            params: {
                objectId: '@id'
            }
        }


    });

    var format = function(object) {
        object.remove = remove;

        return object;
    }

    this.getAllObjects = function() {
        console.log("ObjectService.getAllObjects");
        var deferred = $q.defer();

        Objects.get({}, function(response) {
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
        console.log("ObjectService.edit", data);

        var deferred = $q.defer();

        Objects.edit({
            objectId: id
        }, data, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            alert("ERROR: " + error.data.message);
            deferred.reject(error);
        });
        return deferred.promise;
    }


    this.create = function(data) {
        console.log("ObjectService.create");
        var deferred = $q.defer();

        Objects.create(data, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            alert("ERROR: " + error.data.message);
            deferred.reject(error);
        });

        return deferred.promise;
    };

    var remove = function() {
        console.log("ObjectService.remove");
        var object = this;
        if (confirm("Do you really want to remove object number " + object.id + "?")) {
            Objects.remove({
                objectId: object.id
            }, function(response) {

                console.log(object.name + " removed");

                if (response.data.warning) {
                    alert(response.data.warning);
                } else {
                    console.log("No endpoints were referencing the object")
                }

                $rootScope.$emit('afterModification');

            });

        } else {
            console.log("Deletion Cancelled");
        }
    };

    return this;
}