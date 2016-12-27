angular.module('starter.controller.account', [])

.controller('AccountCtrl', function($scope, $state, $cordovaToast, Account) {

  // 初始化登录页面
  $scope.account = {
    user: '',
    pass: ''
  };

  // 登录按钮按下
  $scope.login = function() {

      // 验证用户名和密码是否为空白
      if ($scope.account.user == '' || $scope.account.pass == '') {
        $cordovaToast.show('请输入用户名和密码。', 'short', 'center');
        return;
      }
      
      // 登录
      Account.login($scope, $scope.account.user, $scope.account.pass);
  };
});
