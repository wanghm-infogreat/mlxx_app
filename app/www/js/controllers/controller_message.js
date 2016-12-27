angular.module('starter.controller.message', [])

.controller('MessageUserCtrl', function($scope, $state, MessageUser) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  MessageUser.list().then(function(users) {
    // 取得消息用户列表
    $scope.users = users;
  });
    
  /******************************************************************/
  // 刷新
  /******************************************************************/
  $scope.doRefresh = function () {
    // 初始化
    MessageUser.init();
    
    // 刷新画面内容
    MessageUser.list().then(function(users) {

      // 设定画面一览
      $scope.users = users;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    MessageUser.list().then(function(users) {

      // 设定画面一览
      $scope.users = users;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return MessageUser.hasMore();
  };

  /******************************************************************/
  // 新消息
  /******************************************************************/
  $scope.doNewMessage = function() {
    $state.go('tab.new-message');
  }

  /******************************************************************/
  // 删除消息一览中的用户
  /******************************************************************/
  $scope.doDelete = function(user) {
    // 调用点赞处理
    MessageUser.delete(user).then(function(data) {
      // 从画面删除用户
      var users = MessageUser.users();
      var index = users.indexOf(user);
      users.splice(index, 1);
    });
  }
})

/*
 * 消息Ctrl
 */
.controller('MessageCtrl', function($scope, $stateParams, $ionicScrollDelegate, Message, MessageUser) {

  // 设定当前用户信息
  var user = JSON.parse(localStorage.getItem("user"));
  $scope.user = user;

  // 取得参数的用户
  var user = MessageUser.user($stateParams.userid);

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 初始化
  Message.init();

  // 取得消息一览
  Message.list(user).then(function(data) {
    // 取得消息列表
    $scope.messages = data.messages;
    
    // 更新未读消息件数
    user.new_count = data.user.new_count;
    
    // 滚动到画面底部
    $ionicScrollDelegate.$getByHandle('scrollMessage').scrollBottom();
  });

  /******************************************************************/
  // 刷新
  /******************************************************************/
  $scope.doRefresh = function () {
    // 初始化
    Message.init();
    
    // 刷新画面内容
    Message.list(user).then(function(data) {

      // 设定画面一览
      $scope.messages = data.messages;

      // 更新未读消息件数
	  user.new_count = data.user.new_count;

      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Message.list(user).then(function(data) {

      // 设定画面一览
      $scope.messages = data.messages;

      // 更新未读消息件数
	  user.new_count = data.user.new_count;

      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return Message.hasMore();
  };

  /******************************************************************/
  // 发送消息
  /******************************************************************/
  $scope.doSend = function() {

    // 取得消息内容
    var content = $scope.content;

    // 验证消息内容
    if (content == null || content == "") {
      alert("请输入消息内容。");
      return;
    }

    // 发送消息
    Message.send(user.friend_user_id, content).then(function(data) {

      // 设定消息一览
      $scope.messages = data.messages;

      // 清除画面的输入内容
      $scope.content = "";

      // 更新当前的消息用户信息
      user.message_count = data.user.message_count;
      user.new_count = data.user.new_count;

      // 滚动到画面底部
      $ionicScrollDelegate.$getByHandle('scrollMessage').scrollBottom();
    });
  }
})

/*
 * 新消息Ctrl
 */
.controller('NewMessageCtrl', function($scope, $stateParams, $state, Message, MessageUser, Friend) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 初始化
  Friend.init();

  // 取得好友一览
  Friend.list().then(function(friends) {
    // 取得好友列表
    $scope.friends = friends;
  });

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Friend.list().then(function(friends) {

      // 设定画面一览
      $scope.friends = friends;

      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };

  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return Friend.hasMore();
  };

  /******************************************************************/
  // 发送新消息
  /******************************************************************/
  $scope.doSendList = function() {

    // 取得所有好友列表
    var friends = $scope.friends;

    // 发送内容
    var message = $scope.content;
    if (message == null || message == "") {
      alert("请输入消息内容。");
      return;
    }

    // 更新消息好友画面的数据
    var users = MessageUser.users();

    // 验证是否选择发送对象
    var checked = false;
    for(var i = 0; i < friends.length; i++) {
    
      var friend = friends[i];
    
      // 如果选择
      if (friend.checked) {
        // 发送消息
        Message.send(friend.friend_user_id, message).then(function(data) {

          // 是否为新消息用户
          var newfriend = true;

          // 更新消息好友画面的数据
          for(var j = 0; j < users.length; j++) {
            if (users[j].friend_user_id == data.msg.to_user_id) {
              users[j].message_count = data.user.message_count;
              newfriend = false;
              break;
            }
          }

          // 如果是新用户
          if (newfriend) {
            users.splice(0, 0, data.user);
          }
        });

        // 有选择的对象
        checked = true;
      }
    }

    // 未选择
    if (!checked) {
      alert("请选择发送对象。");
      return;
    }
    
    // 清空画面输入内容
    $scope.content = "";
  }
});
