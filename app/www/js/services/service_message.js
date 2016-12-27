/*
 * 消息Service
 */
angular.module('starter.service.message', [])

/*
 * 消息用户Factory
 */
.factory('MessageUser', function($http) {

  // 变量定义
  var users = [];                 // 消息好友一览
  var moredata = false;           // 可否加载更多数据标志

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      users = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得全部好友对象
    /**********************************************************/
    users: function() {
      return users;
    },

    /**********************************************************/
    // 取得消息好友对象
    /**********************************************************/
    user: function(userid) {
      for (var i = 0; i < users.length; i++) {
        if (users[i].id == userid) {
          return users[i];
        }
      }
      return null;
    },

    /**********************************************************/
    // 取得有消息的好友一览
    /**********************************************************/
    list: function() {

      // 初始化api
      var listurl = "/mlxx/common/message/users/";

      // 判断是否已经初始化
      if (users.length != 0) {
        listurl = listurl + users[users.length - 1].modify_datetime;
      }

      // 取得消息好友一览
      return $http.get(listurl).then(function (response) {

          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存消息用户一览
          users = users.concat(response.data);

          // 返回结果
          return users;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function($scope) {
      return moredata;
    },

    /**********************************************************/
    // 删除消息一览中的用户
    /**********************************************************/
    delete: function(user) {

      // 删除
      return $http.delete("/mlxx/common/message/users/" + user.id)
        .success(function (response) {
          // 返回结果
          return response.data;
      });
    }
  };
})

/*
 * 消息Factory
 */
.factory('Message', function($http) {

  // 变量定义
  var messages = [];                 // 消息一览

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      messages = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得消息列表
    /**********************************************************/
    list: function(user) {
    
      // 初始化api
      var listurl = "/mlxx/common/message/messages/" + user.friend_user_id + "/";

      // 判断是否已经初始化
      if (messages.length != 0) {
        listurl = listurl + messages[0].id;
      }

      return $http.get(listurl)
        .then(function (response) {

          // 设定消息一览
          messages = response.data.msg.concat(messages);
		  response.data.messages = messages;
          return response.data;
      });
    },

    /**********************************************************/
    // 发送消息
    /**********************************************************/
    send: function(userid, content) {

      // 设定消息内容
      var message = {};
      // 消息发送对象
      message.to_user_id = userid;
      // 消息内容
      message.message = content;

      // 发送
      return $http.put("/mlxx/common/message/", message)
        .then(function (response) {

          // 在画面显示消息内容
          messages = messages.concat(response.data.msg);
          response.data.messages = messages;

          // 设定返回值
          return response.data;
      });
    }
  }
});
