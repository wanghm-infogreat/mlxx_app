angular.module('starter.service.account', [])

.factory('Account', function($state, $http, md5) {
  return {
    login: function($scope, user, pass) {
    
      // 加密密码
      pass = md5.createHash(pass);

      $http.post("/mlxx/login", {'user':user, 'pass':pass})
        .success(
          function(data, status, headers, config){
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
        })
        .error(function(data){
            // 清除密码
            $scope.account.pass = "";
        });
    }
  };
});
