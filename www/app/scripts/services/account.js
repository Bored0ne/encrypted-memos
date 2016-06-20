'use strict';

/**
 * @ngdoc service
 * @name wwwApp.account
 * @description
 * # account
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .service('accountService', function ($http, alertService) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    var BASE = 'http://localhost:8181/memoapi/';
    var apiKey = '';
    var myPin = '';

    this.login = function(badge, pin, callback){
      var url = BASE + 'login';
      $http.post(url,{'badge': badge, 'pin': pin})
        .success(function(key){
          apiKey = key;
          myPin = pin;
          callback(true);
          alertService.Alert('success', 'You have logged in successfully');
        })
        .error(function (data) {
          apiKey = '';
          myPin = '';
          alertService.Alert('danger', data);
          callback(false);
        });
    };

    this.getApiKey = function(){
      return apiKey;
    };

    this.getPin = function(){
      return myPin;
    };
  });
