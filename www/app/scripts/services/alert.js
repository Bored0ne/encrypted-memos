'use strict';

/**
 * @ngdoc service
 * @name wwwApp.alert
 * @description
 * # alert
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .factory('alertService', function ($timeout) {
    var hide = 'hide';
    var alert = {show: hide, type: '', message: ''};
    alert.Alert = function(type, message){
      alert.type = angular.copy(type);
      alert.message = angular.copy(message);
      alert.show = angular.copy('fade.in');
      $timeout(function(){
        alert.show = angular.copy('fade');
      }, 2000, 1, true);
    };

    return alert;
  });
