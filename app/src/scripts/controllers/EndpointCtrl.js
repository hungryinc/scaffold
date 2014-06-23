'use strict';

module.exports = function($scope, EndpointService, $rootScope, $cookies, ngTableParams) {

    console.log("EndpointCtrl Loaded");

    var refreshList = function() {
        console.log('EndpointCtrl.refreshList');
        EndpointService.getAllEndpoints().then(function(endpoints) {
            $scope.endpoints = endpoints;

            $scope.tableParams = new ngTableParams({
                page: 1, // show first page
                count: 10, // count per page
                sorting: {
                    endpoint_id: 'asc' // initial sorting
                }
            }, {
                total: $scope.endpoints.length, // length of $scope.endpoints
                getData: function($defer, params) {
                    // use build-in angular filter
                    var orderedData = params.sorting() ?
                        $filter('orderBy')($scope.endpoints, params.orderBy()) :
                        $scope.endpoints;

                    $defer.resolve(orderedData.slice((params.page() - 1) * params.count(), params.page() * params.count()));
                }
            });
        });

    };

    refreshList();



    $rootScope.$on('afterModification', function() {
        console.log("afterModification Broadcast Received");
        refreshList();
    });

    $scope.create = function() {

        var newEndpoint = $scope.addEndpoint;
        console.log(newEndpoint['name']);

        EndpointService.create(newEndpoint).then(function() {
            refreshList()
        });

        $scope.addEndpoint = "";
    };

    //USE THIS TO REMOVE $$HASHKEY WHEN USING NG-REPEAT
    $scope.hideHashkey = function(object) {
        var output;

        output = angular.toJson(object);
        // output = angular.fromJson(output);

        return angular.fromJson(output);
    };
}