/*
 * 用户Service
 */
angular.module('starter.service.user', [])

/*
 * 用户Factory
 */
.factory('User', function($http) {

  // 变量定义
  var user = {};					// 用户信息

  return {
  
    /**********************************************************/
    // 取得用户信息
    /**********************************************************/
    user: function() {
      return user;
    },
    
    /**********************************************************/
    // 用户修改密码
    /**********************************************************/
    password: function(account) {

      return $http.post("/mlxx/account/password", account);
    },

    /**********************************************************/
    // 用户信息更新
    /**********************************************************/
    userinfo: function(user) {
    
      var userid = JSON.parse(localStorage.getItem("user")).id;
    
      return $http.post("/mlxx/user/" + userid, user)
        .then(function (response) {
		  // 返回结果
		  return response.data;
      });
    }
  };
});
