'use strict';

module.exports = function($scope, MainService, $rootScope, $cookies) {

    console.log("MainServiceCtrl Loaded");

    $scope.showArchives = false;

    if ($cookies.get('displayAlerts') == null) {
        console.log('Setting cookie displayAlerts for the first time');
        $cookies.set('displayAlerts', 'yes');
    }

    $scope.getAlerts = function() {
        var value = $cookies.get('displayAlerts');
        if (value == 'yes') {
            return true;
        }
        return false;
    }

    $scope.toggleAlerts = function() {
        if ($scope.getAlerts()) {
            $cookies.set('displayAlerts', 'no');
        } else {
            $cookies.set('displayAlerts', 'yes');
        }
        refreshList();
    }

    $scope.changePriority = function(task, value) {
        console.log('MainServiceCtrl.changePriority()');
        switch (true) {
            case (value == '1'):
                task.markLow();
                break;
            case (value == '2'):
                task.markMed();
                break;
            case (value == '3'):
                task.markHigh();
                break;
            default:
                console.log("Invalid value");
        }
    }

    $scope.changeDueDate = function(task, value) {
        console.log('MainServiceCtrl.changeDueDate');
        task.changeDueDate(value);
    }

    var refreshList = function() {
        console.log('MainServiceCtrl.refreshList');
        if ($scope.showArchives) {
            MainService.getAllTasks().then(function(tasks) {
                $scope.tasks = tasks;
                displayAlerts(tasks);
            });
        } else {
            MainService.getUnarchivedTasks().then(function(tasks) {
                $scope.tasks = tasks;
                displayAlerts(tasks);
            });
        }
    }

    var displayAlerts = function(tasks) {
        if ($scope.getAlerts()) {
            console.log('MainServiceCtrl.displayAlerts');
            var currentTime = new Date();
            for (var i = 0; i < tasks.length; i++) {
                var task = tasks[i];
                var duedate = task.duedate;
                var timeLeft = Math.round((duedate.getTime() - currentTime.getTime()) / (1000 * 60 * 60 * 24)); //Positive number of days
                if (timeLeft <= 3 && !task.completed) {
                    switch (true) {
                        case (timeLeft < 0):
                            alert(task.task + " was due already!");
                            break;
                        case (timeLeft == 0):
                            alert(task.task + " is due today!");
                            break;
                        case (timeLeft == 1):
                            alert(task.task + " is due in " + timeLeft + " day!");
                            break;

                        default:
                            alert(task.task + " is due in " + timeLeft + " days!");
                    }
                }
            }
        }

    }

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.toggleArchiveView = function() {
        $scope.showArchives = !$scope.showArchives;
        refreshList();
    }

    $scope.create = function() {

        var newTask = {
            task: $scope.add,
            priority: $scope.priority,
            duedate: $scope.myDate
        };
        console.log(newTask['duedate']);

        MainService.create(newTask).then(function() {
            refreshList()
        });

        $scope.add = "";
        $scope.priority = "";
        $scope.myDate = "";
    }
}