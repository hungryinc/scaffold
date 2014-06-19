'use strict';

module.exports = function($scope, ObjectService, $rootScope, $cookies) {

    console.log("ObjectCtrl Loaded");

    var refreshList = function() {
        console.log('ObjectCtrl.refreshList');
        ObjectService.getAllObjects().then(function(objects) {
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





    $scope.names = [{
        "name": "Alex",
        "color": "Blue"
    }, {
        "name": "Zach",
        "color": "Red"
    }, {
        "name": "Steve",
        "color": "Yellow"
    }, {
        "name": "Brady",
        "color": "Green"
    }, {
        "name": "Niko",
        "color": "Orange"
    }, {
        "name": "John",
        "color": "Purple"
    }, {
        "name": "Kathy",
        "color": "Pink"
    }];
};