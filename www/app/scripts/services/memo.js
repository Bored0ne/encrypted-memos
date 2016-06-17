'use strict';

/**
 * @ngdoc service
 * @name wwwApp.memo
 * @description
 * # memoService
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .service('memoService', function ($http, $interval, $scope) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    var BASE = 'http://127.0.0.1/memoapi/';
    var memos = [];
    this.GetMemos = function (){
      $http.get(BASE + 'memo', {key: $scope.apiKey})
        .success(function (data){
          memos = data;
        })
        .error(function (data){
          console.log(data);
        });
      return memos;
    };
  });
