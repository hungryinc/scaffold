'use strict';

module.exports = function($scope, MainService) {

    console.log("MainServiceCtrl Loaded");

    $scope.showArchives = false;

    var refreshList = function() {
        if ($scope.showArchives) {
            MainService.getAllTasks().then(function(tasks) {
                $scope.tasks = tasks;
            });
        } else {
            MainService.getUnarchivedTasks().then(function(tasks) {
                $scope.tasks = tasks;
            });
        }
    }

    refreshList();

    $scope.toggleArchiveView = function() {
        $scope.showArchives = !$scope.showArchives;
        refreshList();
    }

    $scope.create = function() {

        var newTask = {
            task: $scope.name
        };

        MainService.create(newTask).then(function() {
            refreshList()
        });

        $scope.name = "";
    }
}