angular.module('starter.controller.user', [])

.controller('UserCtrl', function($scope,
	$cordovaImagePicker,
	$cordovaFileTransfer,
	$state,
	$ionicPopup,
	$cordovaToast,
	$ionicHistory,
	md5,
	User)
{

  /**********************************************************/
  // 初始化
  /**********************************************************/
  // 取得当前用户信息
  var user = JSON.parse(localStorage.getItem("user"));

  // 用户名
  $scope.account = {};
  $scope.account.user = localStorage.getItem("name");
  $scope.account.oldpass = "";
  $scope.account.newpass = "";
  $scope.account.confirmpass = "";

  // 绑定用户
  $scope.user = user;

  /**********************************************************/
  // 选择用户头像照片
  /**********************************************************/
  $scope.selectAvatar = function() {
  
    // 上传文件路径
    var upload_url = "/upload";
    var upload_options = {};

    var headers={'authorization':localStorage.getItem("token")};
    upload_options.headers = headers;

    // 打开照片选择器
    var options = {
      maximumImagesCount: 1,
      width: 0,
      height: 0,
      quality: 100
    };

    $cordovaImagePicker.getPictures(options)
      .then(function (results) {
        for (var i = 0; i < results.length; i++) {
          console.log('Image URI: ' + results[i]);
          
          // 上传文件
          $cordovaFileTransfer.upload(upload_url, results[i], upload_options)
            .then(function(result) {
              console.log('upload success. URL: ' + JSON.parse(result.response).url);
              // Success!
              $scope.user.photo_url = '' + JSON.parse(result.response).url;
            }, function(err) {
              console.log('upload error.' + err.status);
              // Error
            }, function (progress) {
              // constant progress updates
            });
        }
    }, function(error) {
      // error getting photos
    });
  }

  /**********************************************************/
  // 更改用户密码
  /**********************************************************/
  $scope.password = function() {

      // 取得画面输入的内容
      var oldpass = $scope.account.oldpass;
      var newpass = $scope.account.newpass;
      var confirmpass = $scope.account.confirmpass;

      if (oldpass == "" || newpass == "" || confirmpass == "") {
        $cordovaToast.showShortCenter("请输入内容。");
        return;
      }

      // 加密密码
      var account = {};
      account.oldpass = md5.createHash(oldpass);
      account.newpass = md5.createHash(newpass);
      account.confirmpass = md5.createHash(confirmpass);

      // 调用api
      User.password(account).then(function() {
		// 清除画面内容
        $scope.account.oldpass = "";
        $scope.account.newpass = "";
        $scope.account.confirmpass = "";
	  });
  };

  /**********************************************************/
  // 退出
  /**********************************************************/
  $scope.logout = function () {
  
	    // 确认对话框
		var confirmPopup = $ionicPopup.confirm({
  			title: '退出',
   			template: '您确定要退出吗？'
		});
		
		// 显示
		confirmPopup.then(function(res) {
	   		if(res) {
		      // 清除所有本地缓存内容
		      localStorage.clear();

			  $ionicHistory.clearCache();
   			  $ionicHistory.clearHistory();
      
		      // 清除所有scope内容
		      $scope.account.user = "";
		      $scope.account.psss = "";

		      // 显示login画面
		      $state.go('login');
     		}
   		});
  }

  /**********************************************************/
  // 更新用户信息
  /**********************************************************/
  $scope.UserInfo = function() {

	// 取得用户信息
	var user = $scope.user;

      // 调用api
      User.userinfo(user).then(function(data) {

		// 设定结果
        localStorage.setItem("user", JSON.stringify(data.user));
	  });
  }
});
