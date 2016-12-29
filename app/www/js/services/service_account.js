/*
 * 户登录Service
 */
angular.module('starter.service.account', [])

/*
 * 用户登录Factory（Account）
 */
.factory('Account', function($http) {

  return {
  
    /**********************************************************/
  	// 用户登录
    /**********************************************************/
    login: function(user, pass) {
      return $http.post("/mlxx/login", {'user':user, 'pass':pass})
        .then(function(response) {
		 return response.data;
      });
    }
  };
});
