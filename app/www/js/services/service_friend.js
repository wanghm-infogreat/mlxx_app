angular.module('starter.service.friend', [])

.factory('Friend', function($http) {

  // 变量定义
  var friends = [];
  var moredata = false;

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      friends = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得全部好友对象
    /**********************************************************/
    friends: function() {
      return friends;
    },

    /**********************************************************/
    // 取得好友一览
    /**********************************************************/
    list: function($scope) {
    
      // 初始化api
      var listurl = "/mlxx/common/friend/";

      // 判断是否已经初始化
      if (friends.length != 0) {
        listurl = listurl + friends[friends.length - 1].modify_datetime;
      }

      // 取得公告一览
      return $http.get(listurl).then(function (response) {

          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存好友一览
          friends = friends.concat(response.data);

          // 返回结果
          return friends;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function($scope) {
      return moredata;
    },

    /**********************************************************/
    // 新加好友
    /**********************************************************/
    add: function(userid) {
    
      // 新加好友
      return $http.put("/mlxx/common/friend/" + userid).then(function (response) {
          // 在画面显示好友
          friends.splice(0, 0, response.data.friend);
          return friends;
      });
    },
    
    /**********************************************************/
    // 删除好友
    /**********************************************************/
    delete: function(friendid) {
    
      // 删除好友
      return $http.delete("/mlxx/common/friend/" + friendid).then(function (response) {
          return;
      });
    }
  };
})

/*
 * 用户检索Factory
 */
.factory('FriendSearch', function($http) {

  // 变量定义
  var searchs = [];
  var moredata = false;

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      searchs = [];
      moredata = false;
    },

    /**********************************************************/
    // 检索
    /**********************************************************/
    search: function(word) {

      // 初始化api
      var listurl = "/mlxx/common/friend/search/" + word;

      // 判断是否已经初始化
      if (searchs.length != 0) {
        listurl = listurl + searchs[searchs.length - 1].id;
      }

      // 取得检索结果一览
      return $http.get(listurl).then(function (response) {
      
          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存好友一览
          searchs = searchs.concat(response.data);

          // 返回结果
          return searchs;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function($scope) {
      return moredata;
    }
  };
});
