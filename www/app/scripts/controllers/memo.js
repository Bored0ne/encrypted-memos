'use strict';

/**
 * @ngdoc function
 * @name wwwApp.controller:MemoCtrl
 * @description
 * # MemoCtrl
 * Controller of the wwwApp
 */
angular.module('wwwApp')
  .controller('MemoCtrl', function($scope, $window, $log, $routeParams, memoService){
    this.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
    // $scope.log = $log;
    $scope.memos = memoService.memoList;
    $scope.DeleteMemo = function(id){
      memoService.DelMemo($scope.apiKey, id);
    };
    $scope.NewMemo = function(memo, event){
      if (!event || event.which === 13){
        if (memo.memo){
          memoService.AddMemo($scope.apiKey, memo.memo);
        }
      }
    };
  });
