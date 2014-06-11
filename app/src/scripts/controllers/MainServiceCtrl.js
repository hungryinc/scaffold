'use strict';

module.exports = function($scope, MainService, $rootScope) {

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

    $scope.watch('tasks[$index]',
        function(newval, oldval, scope) {
            console.log("HELLO!!!!");
        }, true);



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
            priority: $scope.priority
        };

        MainService.create(newTask).then(function() {
            refreshList()
        });

        $scope.add = "";
        $scope.priority = "";
    }
}