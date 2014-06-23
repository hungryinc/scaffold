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
        // object.rename = rename;
        // object.remove = remove;
        // object.changeDescription = changeDescription;
        // object.changeJSON = changeJSON;

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
        Objects.edit({
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
                id: object.id,
            }, function() {
                console.log(object.name + " removed");
                $rootScope.$emit('afterModification');
            });
        } else {
            console.log("Deletion Cancelled");
        }
    };

    return this;
}