'use strict';

/**
 * @ngdoc directive
 * @name wwwApp.directive:alert
 * @description
 * # alert
 */
angular.module('wwwApp')
  .directive('alertDirective', function () {
    return {
      templateUrl: 'views/alert.html',
      restrict: 'E',
      controller: 'AlertCtrl',
      replace: true
    };
  });
