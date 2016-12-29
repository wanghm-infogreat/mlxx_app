/*
 * 公告Service
 */
angular.module('starter.service.dash', [])

/*
 * 公告Factory（Dash）
 */
.factory('Dash', function($http) {

  // 变量定义
  var dashs = [];                     // 公告一览
  var moredata = false;               // 是否还有更多可加载数据标志

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      dashs = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得公告对象
    /**********************************************************/
    dash: function(dashid) {
      for (var i = 0; i < dashs.length; i++) {
        if (dashs[i].id == dashid) {
          return dashs[i];
        }
      }
      return null;
    },

    /**********************************************************/
    // 取得公告一览
    /**********************************************************/
    list: function() {

      // 初始化api
      var listurl = "/mlxx/dash/";

      // 判断是否已经初始化
      if (dashs.length != 0) {
        listurl = listurl + dashs[dashs.length - 1].id;
      }

      // 取得公告一览
      return $http.get(listurl).then(function (response) {

          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存公告一览
          dashs = dashs.concat(response.data);

          // 返回结果
          return dashs;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function() {
      return moredata;
    },

    /**********************************************************/
    // 点赞
    /**********************************************************/
    like: function(dash) {
      // 调用api
      return $http.put("/mlxx/dash/like/" + dash.id)
        .then(function (response) {
          return response.data;
      });
    }
  };
})

/*
 * 公告Factory（DashComment）
 *
 * 参数：$http                         http访问
 *      $ionicScrollDelegate          滚动条控制
 */
.factory('DashComment', function($http, $ionicScrollDelegate) {

  var comments = [];                  // 公告评论一览
  var moredata = false;               // 是否还有更多可加载数据标志

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      comments = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得评论一览
    /**********************************************************/
    list: function(dash) {

      // 设定api路径
      var listurl = "/mlxx/dash/comment/" + dash.id + "/";
      // 判断是否已经初始化
      if (comments.length != 0) {
        listurl = listurl + comments[0].id;
      }

      // 取得公告评论一览
      return $http.get(listurl).then(function(response) {

          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存公告一览
          comments = response.data.concat(comments);

          // 返回结果
          return comments;
      });
    },

    /**********************************************************/
    // 发表评论
    /**********************************************************/
    send : function(dash, content) {

      // 取得当前用户信息
      var user = JSON.parse(localStorage.getItem("user"));

      // 作成评论对象
      var comment = {};
      // 公告id
      comment.dash_id = dash.id;
      // 用户id
      comment.user_id = user.id;
      // 评论时间
      comment.comment_time = new Date();
      // 评论内容
      comment.content = content;
      
      // 发表评论
      return $http.put("/mlxx/dash/comment/", comment)
        .then(function (response) {

          // 将评论内容添加到评论列表
          comments = comments.concat(response.data.comment);
          response.data.comments = comments;

          // 返回结果
          return response.data;
      });
    }
  };
});
