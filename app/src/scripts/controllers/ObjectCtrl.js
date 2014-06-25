'use strict';

module.exports = function($scope, ObjectService, $rootScope, $cookies, ngDialog) {

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

    $scope.action = function() {

        var objectJSON = $scope.objectJSON;
        var id = $scope.idDropdown;

        if (id) {
            ObjectService.edit(id, objectJSON).then(function() {
                refreshList()
                $scope.objectJSON = "";
                $scope.idDropdown = "";
            });
        } else {
            ObjectService.create(objectJSON).then(function() {
                refreshList()
                $scope.objectJSON = "";
                $scope.idDropdown = "";
            });
        }

    };

    $scope.dialog = function(json) {
        ngDialog.open({
            template: '<pre pretty-json=' + json + ' />',
            plain: true
        });
    };

    //USE THIS TO REMOVE $$HASHKEY WHEN USING NG-REPEAT
    $scope.hideHashkey = function(object) {
        var output;

        output = angular.toJson(object);
        // output = angular.fromJson(output);

        return angular.fromJson(output);
    };
}