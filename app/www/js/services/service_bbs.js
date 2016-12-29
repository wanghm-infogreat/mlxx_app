/*
 * 家长会Service
 */
angular.module('starter.service.bbs', [])

/*
 * 家长会论坛组Factory（BbsGroup）
 */
.factory('BbsGroup', function($http) {

  // 变量定义
  var groups = [];				// 论坛组一览
  
  // 处理定义
  return {
  
    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      groups = [];
    },

    /**********************************************************/
    // 取得所有论坛组信息
    /**********************************************************/
    groups: function() {
      return groups;
    },

    /**********************************************************/
    // 取得数据
    /**********************************************************/
    list: function() {
    
      return $http.get("/mlxx/bbs/group/").then(function (response) {
          
          // 将所有论坛组信息保存到数组
          for(var i = 0; i < response.data.fixes.length; i++) {
            groups.push(response.data.fixes[i]);
          }
          for(var i = 0; i < response.data.favorites.length; i++) {
            groups.push(response.data.favorites[i]);
          }
          for(var i = 0; i < response.data.others.length; i++) {
            groups.push(response.data.others[i]);
          }
		  
		  // 返回结果
		  return response.data;
      });
    },
    
    /**********************************************************/
    // 取消关注论坛组
    /**********************************************************/
    unfavority: function(group) {
    
      // api调用
      var listurl = "/mlxx/bbs/group/favorite/" + group.id;
      
      return $http.delete(listurl).then(function (response) {
		return response.data;
      });
    },

    /**********************************************************/
    // 关注论坛组
    /**********************************************************/
    favority: function(group) {
    
      // api调用
      var listurl = "/mlxx/bbs/group/favorite/" + group.id;
      
      return $http.put(listurl).then(function (response) {
         return response.data;
      });
    }
  };
})

/*
 * 家长会论坛组Factory（BbsGroup）
 */
.factory('BbsTitle', function($http) {

  // 变量定义
  var titles = [];					// 话题一览
  var favorites = [];				// 收藏话题一览
  var moredata = false;				// 是否还有更多话题标志

  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      titles = [];
	  favorites = [];
	  moredata = false;
    },

    /**********************************************************/
    // 取得所有话题信息
    /**********************************************************/
    titles: function() {
      return titles;
    },

    /**********************************************************/
    // 取得论坛话题一览
    /**********************************************************/
    list: function(groupid) {

	  // api
      var listurl = "/mlxx/bbs/title/" + groupid + "/";

      // 判断是否已经初始化
      if (titles.length != 0) {
        listurl = listurl + titles[titles.length - 1].id;
      }

	  // 取得话题一览
      return $http.get(listurl).then(function (response) {

          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存公告一览
          titles = titles.concat(response.data);

          // 返回结果
          return titles;
      });
    },

    /**********************************************************/
    // 取得收藏的论坛话题一览
    /**********************************************************/
    favorites: function() {

      var listurl = "/mlxx/bbs/title/favorite/";
      
      // 判断是否已经初始化
      if (favorites.length !=0) {
        listurl = listurl + favorites[favorites.length - 1].favorite.id;
      }
		 
	  // 取得数据
      return $http.get(listurl).then(function (response) {
          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存话题一览
          favorites = favorites.concat(response.data);

          // 保存所有的话题信息
          titles = [];
          for(var i = 0; i < favorites.length; i++) {
            titles.push(favorites[i].title);
          }

          // 返回结果
          return favorites;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function() {
      return moredata;
    },

    /**********************************************************/
    // 收藏论坛画面
    /**********************************************************/
    favority: function(title) {

      // api调用
      var listurl = "/mlxx/bbs/title/favorite/" + title.id;
      return $http.put(listurl).then(function (response) {
	  	return response.data;
      });
    },

    /**********************************************************/
    // 新话题
    /**********************************************************/
    newTitle : function(groupid, titlename) {

      // 取得当前用户信息
      var user = JSON.parse(localStorage.getItem("user"));

      // 新建话题
      var title = {};
      // 话题标题
      title.name = titlename;
      // 论坛组id
      title.group_id = groupid;
      // 用户id
      title.user_id = user.id;

      // 新建话题
      return $http.put("/mlxx/bbs/title/", title).then(function (response) {

          // 将评论内容添加到评论列表
          titles.unshift(response.data.title);

		  // 设定返回值
		  response.data.titles = titles;

          // 返回结果
          return response.data;
      });
    },

    /**********************************************************/
    // 取消收藏论坛话题
    /**********************************************************/
    unfavority: function(title) {

      // api调用
      var listurl = "/mlxx/bbs/title/favorite/" + title.id;
		 
	  // 取消收藏
      return $http.delete(listurl).then(function (response) {
	  	return response.data;
      });
    }
  };
})

/**
 * 评论Serivce
 */
.factory('BbsComment', function($rootScope, $http, $ionicScrollDelegate) {
  
  // 变量定义
  var comments = [];				// 评论一览
  
  // 处理定义
  return {

    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      comments = [];
    },

    /**********************************************************/
    // 取得评论一览
    /**********************************************************/
    list: function(titleid) {

      var listurl = "/mlxx/bbs/comment/" + titleid + "/";

      // 判断是否已经初始化
      if (comments.length != 0) {
        listurl = listurl + comments[0].id;
      }

	  // 取得数据
	  return $http.get(listurl).then(function (response) {

          // 保存评论一览
          comments = response.data.concat(comments);

          // 返回结果
          return comments;
      });
    },
    
    /**********************************************************/
    // 发表评论
    /**********************************************************/
    comment : function(titleid, titles, content) {
    
      // 从话题列表中，取得groupid
      var groupid = "";
      for(var i = 0; i < titles.length; i++) {
        if (titles[i].id == titleid) {
          groupid = titles[i].group_id;
          break;
        }
      }

      // 取得当前用户信息
      var user = JSON.parse(localStorage.getItem("user"));

      // 作成评论对象
      var comment = {};
      // 论坛组id
      comment.group_id = groupid;
      // 话题
      comment.title_id = titleid;
      // 用户id
      comment.user_id = user.id;
      // 评论时间
      comment.comment_time = new Date();
      // 评论内容
      comment.content = content;
      
      // 发表评论
      return $http.put("/mlxx/bbs/comment/", comment).then(function (response) {

          // 将评论内容添加到评论列表
          comments = comments.concat(response.data.comment);

		  // 返回结果
		  response.data.comments = comments;
          return response.data;
      });
    }
  };
});
