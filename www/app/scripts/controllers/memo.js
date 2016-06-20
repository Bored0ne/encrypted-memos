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
    $scope.MemoList = memoService.memoList;
    $scope.DeleteMemo = function DeleteMemo(id){
      memoService.DelMemo(id);
    };
    $scope.NewMemo = function NewMemo(memo, event){
      if (!event || event.which === 13){
        if (memo){
          memoService.AddMemo(memo);
          window.$('#txtMemo').val('');
        }
      }
    };
  });
