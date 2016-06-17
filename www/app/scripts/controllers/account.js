'use strict';

/**
 * @ngdoc function
 * @name wwwApp.controller:AccountCtrl
 * @description
 * # AccountCtrl
 * Controller of the wwwApp
 */
angular.module('wwwApp')
  .controller('AccountCtrl', function ($scope, $window, accountService) {
    this.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
    $scope.apiKey = '';
    $scope.LogIn = new function(user){
      $scope.apiKey = accountService.login($scope.username, $scope.password);
    };
  });
