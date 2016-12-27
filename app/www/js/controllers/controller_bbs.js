angular.module('starter.controller.bbs', [])

/*
 * 论坛组Ctrl
 */
.controller('BbsGroupCtrl', function($scope, $rootScope, BbsGroup) {
  
  // 初始化论坛组列表
  $scope.groups = {};
  // 取得论坛组列表
  BbsGroup.list($scope);
  
  // 关注论坛组
  $scope.unfavority = function(group) {
    BbsGroup.unfavority($scope, group);
  }

  // 取消关注论坛组
  $scope.favority = function(group) {
    BbsGroup.favority($scope, group);
  }
  
  // 点击论坛组保存信息
  $scope.saveGroup = function(group) {
    $rootScope.group = group;
  }
})

/*
 * 论坛话题Ctrl
 */
.controller('BbsTitleCtrl', function($rootScope, $scope, $stateParams, $ionicPopup, $rootScope, BbsTitle, BbsGroup) {

  // 初始化话题列表
  $scope.titles = [];
  // 取得话题列表
  BbsTitle.list($scope, $stateParams.groupId);
  // 保存当前的论坛组id
  $rootScope.groupid = $stateParams.groupId;

  // 刷新
  $scope.doRefresh = function() {
    $scope.titles = [];
    BbsTitle.list($scope, $rootScope.groupid);
  };
  
  // 加载更多
  $scope.loadMore = function() {
    BbsTitle.list($scope, $rootScope.groupid);
  };
  
  $scope.moreDataCanBeLoaded = function() {
    return BbsTitle.hasMore();
  };

  // 点击话题保存信息
  $scope.saveTitle = function(title) {
    $rootScope.title = title;
  }

  // 收藏话题
  $scope.favority = function(title) {
    BbsTitle.favority($scope, title);
  }
  
  // 新建话题
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
        BbsTitle.newTitle($scope, $rootScope.groupid, res, BbsGroup.getGroups());
      }
    });
  }
})

/**
 * 收藏话题Ctrl
 */
.controller('BbsFavoriteCtrl', function($scope, $stateParams, $rootScope, BbsTitle) {

  // 初始化收藏的话题列表
  $scope.title_favorites = [];
  // 取得收藏的话题列表
  BbsTitle.favorites($scope);
  // 取消收藏话题
  $scope.unfavority = function(title) {
    BbsTitle.unfavority($scope, title);
  }
  
  // 刷新
  $scope.doRefresh = function() {
    // 初始化收藏的话题列表
    $scope.title_favorites = [];
    // 取得收藏的话题列表
    BbsTitle.favorites($scope);
  };
  
  // 加载更多
  $scope.loadMore = function() {
    // 取得收藏的话题列表
    BbsTitle.favorites($scope);
  };
  
  // 是否还有更多数据
  $scope.moreFavoCanBeLoaded = function() {
    return BbsTitle.hasMoreFavorite();
  };

  // 点击话题保存信息
  $scope.saveTitle = function(title) {
    $rootScope.title = title;
  }
})

/**
 * 评论内容ctrl
 */
.controller('BbsCommentCtrl', function($scope, $ionicScrollDelegate, $stateParams, BbsTitle, BbsComment) {

  // 隐藏内容
  $scope.loaded = false;

  // 初始化发表评论内容
  $scope.content = "";
  // 初始化评论列表
  $scope.comments = [];
  // 取得评论列表
  $scope.titleid = $stateParams.titleId;
  BbsComment.comments($scope, $scope.titleid);
  
  // 滚动到底部
  $scope.$on('$ionicView.afterEnter', function() {
    $ionicScrollDelegate.$getByHandle('scrollComment').scrollBottom();
    $scope.loaded = true;
  });

  // 刷新
  $scope.doRefresh = function() {
    $scope.comments = [];
    BbsComment.comments($scope, $scope.titleid);
  };
  
  // 加载更多
  $scope.loadMore = function() {
    BbsComment.comments($scope, $scope.titleid);
  };
  
  // 发布评论
  $scope.doSend = function() {
    // 发表评论
    BbsComment.send($scope, $scope.titleid, BbsTitle.getTitles());
  }
  
});
