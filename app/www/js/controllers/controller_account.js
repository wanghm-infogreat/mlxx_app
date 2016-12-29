angular.module('starter.controller.account', [])

.controller('AccountCtrl', function($scope, $state, $cordovaToast, md5, Account) {

  /**********************************************************/
  // 初始化登录页面
  /**********************************************************/
  $scope.account = {
    user: '',
    pass: ''
  };

  /**********************************************************/
  // 登录按钮按下
  /**********************************************************/
  $scope.login = function() {

      // 验证用户名和密码是否为空白
      if ($scope.account.user == '' || $scope.account.pass == '') {
        $cordovaToast.show('请输入用户名和密码。', 'short', 'center');
        return;
      }

	  // 用户名
	  var user = $scope.account.user;
      // 加密密码
      var pass = md5.createHash($scope.account.pass);
	  
      // 登录
      Account.login(user, pass).then(function(data) {

		// 保存当前用户的用户名
   	    localStorage.setItem("name", data.name);
      	// 保存当前用户的user
        localStorage.setItem("user", JSON.stringify(data.user));
        // 保存当前用户的token
   	    localStorage.setItem("token", data.token);
              
        // 清除用户名和密码
        $scope.account.user = "";
        $scope.account.pass = "";

        // 返回成功
        $state.go('tab.dash');
  	 });
  };
});
