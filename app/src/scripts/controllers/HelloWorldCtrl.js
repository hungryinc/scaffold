'use strict';

module.exports = function($scope, HelloWorldService) {

    console.log("HelloWorldCtrl Loaded");

    HelloWorldService.getTasks().then(function(tasks) {
        $scope.tasks = tasks;
    });

};