'use strict';

/**
 * @ngdoc function
 * @name wwwApp.controller:AlertCtrl
 * @description
 * # AlertCtrl
 * Controller of the wwwApp
 */
angular.module('wwwApp')
  .controller('AlertCtrl', function ($scope, alertService) {
    this.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
    $scope.alert = alertService;
  });
