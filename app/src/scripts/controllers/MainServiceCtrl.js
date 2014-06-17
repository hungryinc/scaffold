'use strict';

module.exports = function($scope, MainService, $rootScope, $cookies) {

    console.log("MainServiceCtrl Loaded");

    var refreshList = function() {
        console.log('MainServiceCtrl.refreshList');
        MainService.getAllObjects().then(function(objects) {
            $scope.objects = objects;
        });

    }

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.create = function() {

        var newObject = $scope.add;
        console.log(newObject['name']);

        MainService.create(newObject).then(function() {
            refreshList()
        });

        $scope.add = "";
    }
}