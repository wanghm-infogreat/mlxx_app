angular.module('starter.controller.friend', [])

.controller('FriendCtrl', function($scope, $state, Friend) {

 /**********************************************************/
  // 画面初始化
  /**********************************************************/
  Friend.list().then(function(friends) {
    // 设定画面好友一览
    $scope.friends = friends;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    Friend.init();
    
    // 刷新画面内容
    Friend.list().then(function(friends) {

      // 设定画面好友一览
      $scope.friends = friends;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Friend.list().then(function(friends) {

      // 设定画面公告一览
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
  // 删除好友
  /******************************************************************/
  $scope.doDelete = function(friend) {
    // 删除好友
    Friend.delete(friend.id).then(function() {
      // 从画面删除用户
      var friends = Friend.friends();
      var index = friends.indexOf(friend);
      friends.splice(index, 1);
    });
  }

  /******************************************************************/
  // 新加好友检索画面
  /******************************************************************/
  $scope.doNewFriend = function() {
    $state.go('tab.friends-search');
  }
})

/*
 * 好友检索Ctrl
 */
.controller('FriendSearchCtrl', function($scope, Friend, FriendSearch) {

 /**********************************************************/
  // 画面初始化
  /**********************************************************/
  $scope.searchs = [];
  $scope.search = "";

  /**********************************************************/
  // 好友检索
  /**********************************************************/
  $scope.doSearch = function() {
  
    // 取得检索关键字
    var word = $scope.search;

    // 检索
    FriendSearch.search(word).then(function(searchs) {
      // 设定画面用户一览
      $scope.searchs = searchs;
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 检索
    FriendSearch.search(word).then(function(searhcs) {
      // 设定画面用户一览
      $scope.searchs = searchs;
      
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

  /**********************************************************/
  // 新加好友
  /**********************************************************/
  $scope.doAdd = function(userid) {
    // 新加好友
    Friend.add(userid).then(function(friends) {
      $scope.friends = friends;
    });
  }
});
