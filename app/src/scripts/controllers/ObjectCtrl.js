'use strict';

module.exports = function($scope) {

    console.log("ObjectCtrl Loaded");

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