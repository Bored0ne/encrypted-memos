'use strict';

/**
 * @ngdoc service
 * @name wwwApp.account
 * @description
 * # account
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .service('accountService', function ($http, $interval) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    var BASE = 'http://127.0.0.1/memoapi/';
    this.apiKey = '';
    this.login = function(badge, pin){
      var url = BASE + 'login';
      $http.post(url, {badge: badge, pin: pin})
        .success(function(key){
          this.apiKey = key;
        })
        .error(function (data) {
          console.log (data);
        });
    };
  });
