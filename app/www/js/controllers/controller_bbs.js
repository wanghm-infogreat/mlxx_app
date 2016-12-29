angular.module('starter.controller.bbs', [])

/*
 * 论坛组Ctrl
 */
.controller('BbsGroupCtrl', function($scope, $rootScope, BbsGroup) {
  
  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 取得论坛组列表
  BbsGroup.list().then(function(groups) {
	// 设定画面的论坛组信息
  	$scope.groups = groups;
  });
  
  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    BbsGroup.init();
    
    // 刷新画面内容
    BbsGroup.list().then(function(groups) {

      // 设定画面的论坛组信息
      $scope.groups = groups;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 关注论坛组
  /**********************************************************/
  $scope.unfavority = function(group) {
  	// 关注论坛组
    BbsGroup.unfavority(group).then(function(data) {
	
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
	});
  }

  /**********************************************************/
  // 取消关注论坛组
  /**********************************************************/
  $scope.favority = function(group) {
    BbsGroup.favority(group).then(function(data) {
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
	});
  }
  
  /**********************************************************/
  // 点击论坛组保存信息
  /**********************************************************/
  $scope.saveGroup = function(group) {
    $rootScope.group = group;
  }
})

/*
 * 论坛话题Ctrl
 */
.controller('BbsTitleCtrl', function(
	$rootScope,
	$scope,
	$stateParams,
	$ionicPopup,
	$rootScope,
	$ionicScrollDelegate,
	BbsTitle,
	BbsGroup) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 初始化话题列表
  BbsTitle.init();

  // 保存当前的论坛组id
  var groupid = $stateParams.groupId;
  $rootScope.groupid = $stateParams.groupId;

  // 取得话题列表
  BbsTitle.list(groupid).then(function(titles) {
	// 设定画面的话题信息
  	$scope.titles = titles;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    BbsGroup.init();
    
    // 刷新画面内容
    BbsGroup.list(groupid).then(function(groups) {

      // 设定画面的论坛组信息
      $scope.groups = groups;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    BbsTitle.list(groupid).then(function(groups) {

      // 设定画面的论坛组信息
      $scope.groups = groups;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return BbsTitle.hasMore();
  };
  
  /**********************************************************/
  // 点击话题保存信息
  /**********************************************************/
  $scope.saveTitle = function(title) {
    $rootScope.title = title;
  }

  /**********************************************************/
  // 收藏话题
  /**********************************************************/
  $scope.favority = function(title) {
    BbsTitle.favority(title);
  }
  
  /**********************************************************/
  // 新建话题
  /**********************************************************/
  $scope.doNewTitle = function() {

    // 显示输入框
    $ionicPopup.prompt({
      title: '新话题',
      template: ' ',
      inputType: 'text',
      inputPlaceholder: '话题标题',
      okText: '确定',
      cancelText: '取消'
    }).then(function(res) {
      //
      if (res != undefined) {
        // 新建话题
        BbsTitle.newTitle($rootScope.groupid, res).then(function (data) {

			// 设定画面话题一览
			$scope.titles = data.titles;

			// 取得全部论坛组
			var groups = BbsGroup.groups();

            // 更新论坛组信息
            for(var i = 0; i < groups.length; i++) {
              if (groups[i].id == groupid) {
                groups[i].titles = data.group.titles;
                break;
              }
            }
            
            // 滚动到画面顶部
            $ionicScrollDelegate.$getByHandle('scrollTitle').scrollTop();
		});
      }
    });
  }
})

/**
 * 收藏话题Ctrl
 */
.controller('BbsFavoriteCtrl', function($scope, $stateParams, $rootScope, BbsTitle) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 初始化话题列表
  BbsTitle.init();

  // 取得话题列表
  BbsTitle.favorites().then(function(favorites) {
	// 设定画面的话题信息
  	$scope.favorites = favorites;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    BbsTitle.init();

    // 刷新画面内容
    BbsTitle.favorites().then(function(favorites) {

	  // 设定画面的话题信息
	  $scope.favorites = favorites;

      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    BbsTitle.favorites().then(function(favorites) {

	  // 设定画面的话题信息
	  $scope.favorites = favorites;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return BbsTitle.hasMore();
  };
  
  /**********************************************************/
  // 取消收藏话题
  /**********************************************************/
  $scope.unfavority = function(title) {
    BbsTitle.unfavority(title).then(function(data) {
      // 取得画面的所有收藏话题
      var favorites = $scope.favorites;
    
      // 从收藏对象中移除
      for (var i = 0; i < favorites.length; i++) {
        if (favorites[i].title.id == title.id) {
          favorites.splice(i, 1);
          break;
        }
      }
	});
  }
  
  /**********************************************************/
  // 点击话题保存信息
  /**********************************************************/
  $scope.saveTitle = function(title) {
    $rootScope.title = title;
  }
})

/**
 * 评论内容ctrl
 */
.controller('BbsCommentCtrl', function(
	$scope,
	$ionicScrollDelegate,
	$stateParams,
	BbsTitle,
	BbsComment) {

  // 隐藏内容
  $scope.loaded = false;

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 初始化发表评论内容
  $scope.content = "";

  // 初始化话题列表
  BbsComment.init();

  // 取得评论列表
  var titleid = $stateParams.titleId;
  $scope.titleid = titleid;
  BbsComment.list(titleid).then(function(comments) {
	// 设定画面的论列表
  	$scope.comments = comments;
  });

  /**********************************************************/
  // 滚动到底部
  /**********************************************************/
  $scope.$on('$ionicView.afterEnter', function() {
    $ionicScrollDelegate.$getByHandle('scrollComment').scrollBottom();
    $scope.loaded = true;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    BbsComment.init();

    // 刷新画面内容
    BbsComment.list(titleid).then(function(comments) {

	  // 设定画面的论列表
	  $scope.comments = comments;

      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    BbsComment.list(titleid).then(function(comments) {

	  // 设定画面的论列表
	  $scope.comments = comments;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return BbsComment.hasMore();
  };

  /**********************************************************/
  // 发布评论
  /**********************************************************/
  $scope.doSend = function() {
    // 发表评论
    BbsComment.comment(titleid, BbsTitle.titles(), $scope.content).then(function(data) {

	  // 设定画面的论列表
	  $scope.comments = data.comments;

      // 清除画面的输入内容
      $scope.content = "";

      // 更新当前的话题信息
	  titles = BbsTitle.titles();
      for(var i = 0; i < titles.length; i++) {
        if (titles[i].id == titleid) {
          titles[i].comments = data.title.comments;
          break;
        }
      }

      // 滚动到画面底部
      $ionicScrollDelegate.$getByHandle('scrollComment').scrollBottom();
	});
  }
});
