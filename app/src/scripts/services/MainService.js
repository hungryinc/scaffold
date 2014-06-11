'use strict';

module.exports = function($resource, $q) {

    console.log("MainService Loaded");

    var URL = 'http://localhost:8888/tasks/:taskId/:action/:name';

    var formatDate = function(date) {
        var t = date.split(/[- :]/);
        var date = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);

        return date;
    }

    var Tasks = $resource(URL, {}, {

        getById: {
            method: "GET",
            params: {
                taskId: '@id'
            }
        },

        getArchived: {
            method: "GET",
            params: {
                action: 'archived'
            }
        },

        getUnarchived: {
            method: "GET",
            params: {
                action: 'unarchived'
            }
        },

        create: {
            method: "POST"
        },

        markDone: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'markdone'
            }
        },

        markNotDone: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'marknotdone'
            }
        },

        archive: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'archive'
            }
        },

        unarchive: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'unarchive'
            }
        },

        rename: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'rename',
                name: '@name'
            }
        },

        remove: {
            method: "DELETE",
            params: {
                taskId: '@taskId'
            }
        }


    });

    var format = function(task) {
        task.updated_at = formatDate(task.updated_at);
        task.markdone = markDone;
        task.marknotdone = markNotDone;
        task.archive = archive;
        task.unarchive = unarchive;
        task.rename = rename;
        task.remove = remove;

        return task;
    }

    this.getAllTasks = function() {
        console.log("MainService.getAllTasks");
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

    this.getArchivedTasks = function() {
        console.log("MainService.getAllTasks");
        var deferred = $q.defer();

        Tasks.getArchived({}, function(response) {
            for (var i = 0; i < response.data.length; i++) {
                response.data[i] = format(response.data[i]);
            };

            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    }

    this.getUnarchivedTasks = function() {
        console.log("MainService.getAllTasks");
        var deferred = $q.defer();

        Tasks.getUnarchived({}, function(response) {
            for (var i = 0; i < response.data.length; i++) {
                response.data[i] = format(response.data[i]);
            };

            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    }

    var markDone = function() {
        console.log("MainService.markDone");
        var task = this;
        Tasks.markDone({
            id: task.id,
        }, function() {
            task.completed = 1;
        });
    };

    var markNotDone = function() {
        console.log("MainService.markNotDone");
        var task = this;
        Tasks.markNotDone({
            id: task.id,
        }, function() {
            task.completed = 0;
        });
    };

    var archive = function() {
        console.log("MainService.archive");
        var task = this;
        Tasks.archive({
            id: task.id,
        }, function() {
            task.archived = 1;
        });
    };

    var unarchive = function() {
        console.log("MainService.unarchive");
        var task = this;
        Tasks.unarchive({
            id: task.id,
        }, function() {
            task.archived = 0;
        });
    };

    var rename = function() {
        console.log("MainService.rename");
        var task = this;
        var newname = prompt("What would you like to rename the task to?");
        Tasks.rename({
            id: task.id,
            name: newname
        }, function() {
            task.task = newname;
        });
    };

    this.create = function(data) {
        console.log("MainService.create");
        var deferred = $q.defer();

        Tasks.create(data, function(response) {
            deferred.resolve(response.data);
        }, function(error) {
            deferred.reject(error);
        });

        return deferred.promise;
    };

    var remove = function() {
        console.log("MainService.remove");
        var task = this;
        if (confirm("Do you really want to remove this task?")) {
            Tasks.remove({
                taskId: task.id,
            }, function() {
                console.log(task.task + " removed");
            });
        } else {
            console.log("Deletion Cancelled");
        }
    };

    return this;
}