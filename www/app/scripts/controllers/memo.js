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
    $scope.memos = memoService.GetMemos();
    $scope.DeleteMemo = function(id){
      $scope.memos.splice(id, 1);
    };
    $scope.NewMemo = function(memo, event){
      if (!event || event.which === 13){
        if (memo.memo){
          $scope.memos.push(angular.copy(memo));
          $scope.newMemo.memo = null;
        }  
      }
    };
  });
