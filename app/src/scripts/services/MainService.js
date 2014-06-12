'use strict';

module.exports = function($resource, $q, $rootScope) {

    console.log("MainService Loaded");

    var URL = 'http://localhost:8888/tasks/:taskId/:action/:value';

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
                value: '@name'
            }
        },

        changeDueDate: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'changeduedate',
                value: '@duedate'
            }
        },

        markHigh: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'markhigh'
            }
        },

        markMed: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'markmed'
            }
        },

        markLow: {
            method: "PUT",
            params: {
                taskId: '@id',
                action: 'marklow'
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
        task.duedate = formatDate(task.duedate);
        task.markdone = markDone;
        task.marknotdone = markNotDone;
        task.archive = archive;
        task.unarchive = unarchive;
        task.rename = rename;
        task.remove = remove;
        task.markHigh = markHigh;
        task.markMed = markMed;
        task.markLow = markLow;
        task.changeDueDate = changeDueDate;

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
            $rootScope.$emit('afterModification');
        });
    };

    var markNotDone = function() {
        console.log("MainService.markNotDone");
        var task = this;
        Tasks.markNotDone({
            id: task.id,
        }, function() {
            $rootScope.$emit('afterModification');
        });
    };

    var archive = function() {
        console.log("MainService.archive");
        var task = this;
        Tasks.archive({
            id: task.id,
        }, function() {
            $rootScope.$emit('afterModification');
        });
    };

    var unarchive = function() {
        console.log("MainService.unarchive");
        var task = this;
        Tasks.unarchive({
            id: task.id,
        }, function() {
            $rootScope.$emit('afterModification');
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
            $rootScope.$emit('afterModification');
        });
    };

    var markHigh = function() {
        console.log("MainService.markHigh");
        var task = this;
        Tasks.markHigh({
            id: task.id,
        }, function() {
            $rootScope.$emit('afterModification');
        });
    };

    var markMed = function() {
        console.log("MainService.markMed");
        var task = this;
        Tasks.markMed({
            id: task.id,
        }, function() {
            $rootScope.$emit('afterModification');
        });
    };

    var markLow = function() {
        console.log("MainService.markLow");
        var task = this;
        Tasks.markLow({
            id: task.id,
        }, function() {
            $rootScope.$emit('afterModification');
        });
    };

    var changeDueDate = function(newDueDate) {
        console.log("MainService.changeDate", newDueDate);
        var task = this;
        Tasks.changeDueDate({
            id: task.id,
            duedate: newDueDate
        }, function() {
            $rootScope.$emit('afterModification');
        });
    }

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
                $rootScope.$emit('afterModification');
            });
        } else {
            console.log("Deletion Cancelled");
        }
    };

    return this;
}