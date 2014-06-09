'use strict';

module.exports = function($resource, $q) {

    console.log("HelloWorldService Loaded");

    var Tasks = $resource('http://localhost:8888/tasks');

    var format = function(task) {
        task.completed = (task.completed) ? 'yes' : 'no';

        return task;
    }

    this.hello = function() {
        return "Hello World Service";
    }

    this.getTasks = function() {
        var deferred = $q.defer();

        Tasks.get({}, function(response) {
            for (var i = 0; i < response.data.length; i++) {
                response.data[i] = format(response.data[i]);
            };

            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    }

    return this;
}