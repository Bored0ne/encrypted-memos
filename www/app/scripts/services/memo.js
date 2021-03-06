'use strict';

/**
 * @ngdoc service
 * @name wwwApp.memo
 * @description
 * # memoService
 * Service in the wwwApp.
 */
angular.module('wwwApp')
  .factory('memoService', function ($http, $interval, accountService, alertService) {
    // AngularJS will instantiate a singleton by calling "new" on this function
    var memo = {};
    var BASE = 'http://localhost:8181/memoapi/';
    memo.memoList = [];

    memo.GetMemos = function GetMemos(){
      var apikey = accountService.getApiKey();
      var pin = accountService.getPin();
      console.log(apikey + ' ' + pin);
      $http.get(BASE + 'memos', {headers: {'key': apikey}})
        .success(function (data){
          if(Array.isArray(data)){
            data.forEach(function(element){
              element.memo = window.sjcl.decrypt(pin, JSON.parse(element.memo));
            });
          } else if (!window.$.isEmptyObject(data)){
            data.memo = window.sjcl.decrypt(pin, JSON.parse(data.memo));
          }
          angular.copy(data, memo.memoList);
        })
        .error(function (data){
          angular.copy({}, memo.memoList);
        });
    };
    memo.AddMemo = function AddMemo (str){
      var apikey = accountService.getApiKey();
      var pin = accountService.getPin();
      if (apikey !== '' && pin !== '')
      {
        var encryptedMemo = JSON.stringify(window.sjcl.encrypt(pin, str));
        $http.post(BASE + 'memos', {'key': apikey, 'memo': encryptedMemo})
          .success(function(){
            memo.GetMemos();
            alertService.Alert('success', 'Memo Successfully Added');
          })
          .error(function (data){
            console.log(data);
            alertService.Alert('danger', 'Memo had problem being added. please try again later');
          });
      } else {
        alertService.Alert('danger', 'Bad Password');
      }
    };
    memo.DelMemo = function DelMemo (id){
      var apikey = accountService.getApiKey();
      if (apikey !== '')
      {
        $http.delete(BASE + 'memos/' + id, {headers: {'key': apikey}})
          .success(function (){
            memo.GetMemos();
            alertService.Alert('success', 'Memo Successfully Deleted');
          })
          .error(function (data){
            alertService.Alert('danger', 'Memo had problem being deleted. Please try again later');
          });
      }
    };
    return memo;
  });
