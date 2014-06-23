'use strict';

module.exports = function($scope, ObjectService, $rootScope, $cookies) {

    console.log("ObjectCtrl Loaded");

    var refreshList = function() {
        console.log('ObjectCtrl.refreshList');
        ObjectService.getAllObjects().then(function(objects) {
            $scope.objects = objects;
        });

    };

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.create = function() {

        var newObject = $scope.addObject;
        console.log(newObject['name']);

        ObjectService.create(newObject).then(function() {
            refreshList()
        });

        $scope.addObject = "";
    };

    //USE THIS TO REMOVE $$HASHKEY WHEN USING NG-REPEAT
    $scope.hideHashkey = function(object) {
        var output;

        output = angular.toJson(object);
        // output = angular.fromJson(output);

        return angular.fromJson(output);
    };
}