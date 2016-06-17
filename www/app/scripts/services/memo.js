'use strict';

/**
 * @ngdoc service
 * @name wwwApp.memo
 * @description
 * # memoService
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .service('memoService', function ($http, $interval) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    var BASE = 'http://127.0.0.1/memoapi/';
    this.memoList = [];
    this.GetMemos = function (key){
      $http.get(BASE + 'memo', {key: key})
        .success(function (data){
          this.memoList = data;
        })
        .error(function (data){
          console.log(data);
        });
    };
    this.AddMemo = function(key, str){
      $http.post(BASE + 'memo', {key: key, memo: str})
        .success(function (data){
          this.GetMemos();
        })
        .error(function (data){
          console.log(data);
        });
    };
    this.DelMemo = function(key, id){
      $http.delete(BASE + 'memo', {key: key, id: id})
        .success(function (data){
          this.GetMemos();
        })
        .error(function (data){
          console.log(data);
        });
    };
  });
