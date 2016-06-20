'use strict';

/**
 * @ngdoc function
 * @name wwwApp.controller:AccountCtrl
 * @description
 * # AccountCtrl
 * Controller of the wwwApp
 */
angular.module('wwwApp')
  .controller('AccountCtrl', function ($scope, $window, accountService, memoService) {
    this.awesomeThings = [
      'HTML5 Boilerplate',
      'AngularJS',
      'Karma'
    ];
    // UNCOMMENT FOR SERVER HOSTED MEMOS
    // $scope.LogMeIn = function LogMeIn(event){
    //   if (!event || event.which === 13){
    //     accountService.login($scope.username, $scope.password, function(status){
    //       if (status){
    //         memoService.GetMemos();
    //         window.$('.navbar-collapse').collapse('hide');
    //       } else {
    //         memoService.GetMemos();
    //       }
    //     });
    //   }
    // };
    $scope.LogMeIn = function LogMeIn(event){
      if (!event || event.which === 13){
        accountService.login($scope.password, function(){
          memoService.GetMemos();
          window.$('.navbar-collapse').collapse('hide');
          window.$('#txtMemo').focus();
        });
      }
    };
  });
