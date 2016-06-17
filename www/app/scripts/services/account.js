'use strict';

/**
 * @ngdoc service
 * @name wwwApp.accountService
 * @description
 * # accountService
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .service('accountService', function ($http, $interval, $scope) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    var BASE = 'https://127.0.0.1/memoapi/';

    this.login = function(badge, pin){
      var url = BASE + 'login';
      $http.post(url, {badge: badge, pin: pin})
        .success(function(key){
          $scope.apiKey = key;
        })
        .error(function (data) {
          console.log (data);
        });
    };
  });
