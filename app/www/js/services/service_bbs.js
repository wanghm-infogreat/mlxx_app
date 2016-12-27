angular.module('starter.service.bbs', [])

.factory('BbsGroup', function($http) {
  // Might use a resource here that returns a JSON array
  
  var groups = [];
  
  return {
  
    /**
     *
     */
    getGroups: function() {
      return groups;
    },
  
    /**
     * 取得论坛组一览
     */
    list: function($scope) {
    
      var listurl = "/mlxx/bbs/group/";
      
      $http.get(listurl).then(function (response) {
          if (response.data.length != 0) {
            $scope.groups = response.data;
          }
          
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
      });
      return;
    },
    
    /**
     * 取消关注论坛组
     */
    unfavority: function($scope, group) {
    
      // api调用
      var listurl = "/mlxx/bbs/group/favorite/" + group.id;
      
      $http.delete(listurl).then(function (response) {
          if (response.data.length != 0) {
          
            // 取得画面的所有关注对象
            var favorites = $scope.groups.favorites;
      
            // 取得画面的所有未关注对象
            var others = $scope.groups.others;
      
            // 将操作对象加入到未关注列表中
            others.push(group);
      
            // 从关注对象中移除
            for (var i = 0; i < favorites.length; i++) {
                if (favorites[i].id === group.id) {
                favorites.splice(i, 1);
                break;
              }
            }
          
            // 显示结果信息
            alert(response.data);
          }
      });
      return;
    },

    /**
     * 关注论坛组
     */
    favority: function($scope, group) {
    
      // api调用
      var listurl = "/mlxx/bbs/group/favorite/" + group.id;
      
      $http.put(listurl).then(function (response) {
          if (response.data.length != 0) {
          
            // 取得画面的所有关注对象
            var favorites = $scope.groups.favorites;
      
            // 取得画面的所有未关注对象
            var others = $scope.groups.others;
      
            // 将操作对象加入到关注列表中
            favorites.push(group);
      
            // 从未关注对象中移除
            for (var i = 0; i < others.length; i++) {
                if (others[i].id === group.id) {
                others.splice(i, 1);
                break;
              }
            }
          
            // 显示结果信息
            alert(response.data);
          }
      });
      return;
    }


  };
})

.factory('BbsTitle', function($http, $ionicScrollDelegate) {
  
  var titles = [];
  
  var moredata = false;
  
  var moredataFavorite = false;
  
  var favorites = [];
  
  return {
  
    /**
     *
     */
    getTitles: function() {
      return titles;
    },
  
    /**
     * 取得论坛话题一览
     */
    list: function($scope, groupid) {
    
      // 取得画面显示的话题
      titles = $scope.titles;

      var listurl = "/mlxx/bbs/title/" + groupid + "/";
      
      // 判断是否已经初始化
      if (titles.length != 0) {
        listurl = listurl + titles[titles.length - 1].id;
      }
      
      $http.get(listurl).then(function (response) {
          if (response.data.length != 0) {
            moredata = true;
            titles = titles.concat(response.data);
            $scope.titles = titles;
          } else {
            moredata = false;
          }
          $scope.$broadcast('scroll.refreshComplete');
          $scope.$broadcast('scroll.infiniteScrollComplete');
      });
      return;
    },
    
    // 是否还有更多数据
    hasMore: function($scope) {
      return moredata;
    },

    /**
     * 取得收藏的论坛话题一览
     */
    favorites: function($scope) {
    
      // 取得画面显示的话题
      favorites = $scope.title_favorites;

      var listurl = "/mlxx/bbs/title/favorite/";
      
      // 判断是否已经初始化
      if (favorites.length !=0) {
        listurl = listurl + favorites[favorites.length - 1].favorite.id;
      }
      
      $http.get(listurl).then(function (response) {
          if (response.data.length != 0) {
            moredataFavorite = true;
            favorites = favorites.concat(response.data);
            $scope.title_favorites = favorites;
            
            // 保存所有的话题信息
            titles = [];
            for(var i = 0; i < favorites.length; i++) {
              titles.push(favorites[i].title);
            }
          } else {
            moredataFavorite = false;
          }
          $scope.$broadcast('scroll.refreshComplete');
          $scope.$broadcast('scroll.infiniteScrollComplete');
      });
      return;
    },

    // 是否还有更多数据
    hasMoreFavorite: function($scope) {
      return moredataFavorite;
    },
    
    /**
     * 取消收藏论坛话题
     */
    unfavority: function($scope, title) {
    
      // api调用
      var listurl = "/mlxx/bbs/title/favorite/" + title.id;
      
      $http.delete(listurl).then(function (response) {
          if (response.data.length != 0) {

            // 取得画面的所有收藏话题
            var loops = $scope.title_favorites;
      
            // 从收藏对象中移除
            for (var i = 0; i < loops.length; i++) {
                if (loops[i].title.id == title.id) {
                loops.splice(i, 1);
                break;
              }
            }
            $scope.title_favorites = loops;
          }
      });
      return;
    },

    /**
     * 收藏论坛画面
     */
    favority: function($scope, title) {
    
      // api调用
      var listurl = "/mlxx/bbs/title/favorite/" + title.id;
      
      $http.put(listurl).then(function (response) {
      });
      return;
    },
    
    /*
     * 新话题
     */
    newTitle : function($scope, groupid, titlename, groups) {
    
      // 取得画面显示的话题
      titles = $scope.titles;

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
      $http.put("/mlxx/bbs/title/", title).then(function (response) {
          if (response.data.length != 0) {
            
            // 将评论内容添加到评论列表
            titles.unshift(response.data.title);
            $scope.titles = titles;
            
            // 更新论坛组信息
            for(var i = 0; i < groups.length; i++) {
              if (groups[i].id == groupid) {
                groups[i].titles = response.data.group.titles;
                break;
              }
            }
            
            // 滚动到画面顶部
            $ionicScrollDelegate.$getByHandle('scrollTitle').scrollTop();
          }
      });
      return;
    }
  };
})

/**
 * 评论Serivce
 */
.factory('BbsComment', function($rootScope, $http, $ionicScrollDelegate) {
  
  return {
    /**
     * 取得论坛话题一览
     */
    // 取得评论一览
    comments: function($scope, titleid) {

      // 取得画面显示的comments
      comments = $scope.comments;

      var listurl = "/mlxx/bbs/comment/" + titleid + "/";
      // 判断是否已经初始化
      if (comments.length != 0) {
        listurl = listurl + comments[0].id;
      }

      $http.get(listurl).then(function (response) {
          if (response.data.length != 0) {
            comments = response.data.concat(comments);
            $scope.comments = comments;
          }
          $scope.$broadcast('scroll.refreshComplete');
          $scope.$broadcast('scroll.infiniteScrollComplete');
      });
      return;
    },
    
    // 发表评论
    send : function($scope, titleid, titles) {
    
      // 从话题列表中，取得groupid
      var groupid = "";
      for(var i = 0; i < titles.length; i++) {
        if (titles[i].id == titleid) {
          groupid = titles[i].group_id;
          break;
        }
      }

      // 取得画面显示的comments
      var comments = $scope.comments;
      
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
      comment.content = $scope.content;
      
      // 发表评论
      $http.put("/mlxx/bbs/comment/", comment).then(function (response) {
          if (response.data.length != 0) {
            
            // 将评论内容添加到评论列表
            comments = comments.concat(response.data.comment);
            $scope.comments = comments;
            
            // 清除画面的输入内容
            $scope.content = "";
            
            // 更新当前的话题信息
            for(var i = 0; i < titles.length; i++) {
              if (titles[i].id == titleid) {
                titles[i].comments = response.data.title.comments;
                break;
              }
            }
            
            // 滚动到画面底部
            $ionicScrollDelegate.$getByHandle('scrollComment').scrollBottom();
          }
      });
      return;
    }
  };
});
