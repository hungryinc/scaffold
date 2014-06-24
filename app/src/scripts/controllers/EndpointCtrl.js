'use strict';

module.exports = function($scope, EndpointService, $rootScope, $cookies) {

    console.log("EndpointCtrl Loaded");

    var refreshList = function() {
        console.log('EndpointCtrl.refreshList');
        EndpointService.getAllEndpoints().then(function(endpoints) {
            $scope.endpoints = endpoints;
        });

    };

    refreshList();





    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.action = function() {

        var endpointJSON = $scope.endpointJSON;
        var id = $scope.idDropdown;

        if (id) {
            EndpointService.edit(id, endpointJSON).then(function() {
                refreshList()
                $scope.endpointJSON = "";
                $scope.idDropdown = "";
            });
        } else {
            EndpointService.create(endpointJSON).then(function() {
                refreshList()
                $scope.endpointJSON = "";
                $scope.idDropdown = "";
            });
        }

    };

    //USE THIS TO REMOVE $$HASHKEY WHEN USING NG-REPEAT
    $scope.hideHashkey = function(object) {
        var output;

        output = angular.toJson(object);
        // output = angular.fromJson(output);

        return angular.fromJson(output);
    };
}