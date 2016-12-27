angular.module('starter.service.common.http', [])

.factory('HttpInterceptor', ['$q', HttpInterceptor]);

function HttpInterceptor($q, $cordovaToast, $location) {
  return {
    request: function(config){
    
      // 判断是否为API请求
      if (config.url.substring(0,5) == "/mlxx") {
        config.headers = config.headers || {};
        token = localStorage.getItem("token");
        if (token != "") {
          config.headers.authorization = token;
        }
        
        //if (ngCordova) {
        //  config.url = "http://192.168.66.29/dev" + config.url;
        //  config.url = "http://192.168.2.111/dev" + config.url;
        //}
        
        if (config.method == 'POST' || config.method == 'PUT') {

          // 在提交数据中，增加用户id
          config.data = config.data == undefined ? {} : config.data || {};

          // 如果用户已登录
          if (localStorage.getItem("user") != null) {
            // 设定用户id
            config.data.modify_user_id = JSON.parse(localStorage.getItem("user")).id;
          }
        }
      }
      return config;
    },
    requestError: function(err){
      return $q.reject(err);
    },
    response: function(res){
      // 如果返回不是空白
      if (res.data != null && res.data.message != null) {
			//$cordovaToast.showShortCenter(res.data.message);
        	alert(res.data.message);
      }
      return res;
    },
    responseError: function(err){
      if(-1 === err.status) {
        // 远程服务器无响应
      } else if(500 === err.status) {
        // 处理各类自定义错误
      } else if(501 === err.status) {
        // ...
      } else if(405 === err.status) {
      // 数据验证错误
      } else if(400 === err.status) {
      // 数据错误
      } else if(401 === err.status) {
      // token验证错误
	  	alert("您的登录失效已过期，请重新登录。");
	  	$location.path("/login");
      } else if(402 === err.status) {
        // 如果返回不是空白
        if (err.data != null && err.data.message != null) {
	  	  //if (ngCordova) {
		//	$cordovaToast.showShortCenter(err.data.message);
		  //} else {
        	alert(err.data.message);
		 // }
        }
      } else if(403 === err.status) {
      	// 无访问权限
	  	alert(err.data.message);
      }
      return $q.reject(err);
    }
  };
};
