/*
 * 公告Controller
 */
angular.module('starter.controller.dash', [])

/*
 * 公告页面Ctrl
 */
.controller('DashCtrl', function($scope, Dash) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  Dash.list().then(function(dashs) {
    // 设定画面公告一览
    $scope.dashs = dashs;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    Dash.init();
    
    // 刷新画面内容
    Dash.list().then(function(dashs) {

      // 设定画面公告一览
      $scope.dashs = dashs;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Dash.list().then(function(dashs) {

      // 设定画面公告一览
      $scope.dashs = dashs;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return Dash.hasMore();
  };
  
  /**********************************************************/
  // 公告点赞
  /**********************************************************/
  $scope.doLike = function(dash) {
    // 调用点赞处理
    Dash.like(dash).then(function(data) {
      // 设定画面的点赞数
      dash.likes = data.dash.likes;
    });
  };

  /**********************************************************/
  // 公告分享
  /**********************************************************/
  $scope.doShare = function(dash) {
    alert('share:'+dash.id);
  };
})

/*
 * 公告评论Ctrl
 */
.controller('DashCommentCtrl', function(
    $scope,
    $stateParams,
    $ionicScrollDelegate,
    Dash, DashComment)
{
  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 根据参数公告id，取得公告对象
  var dash = Dash.dash($stateParams.dashid);

  // 控制画面加载完成后再显示
  $scope.loaded = false;
  // 初始化发表评论内容
  $scope.content = "";
  
  // 初始化评论列表
  DashComment.init();

  // 取得评论列表
  DashComment.list(dash).then(function(comments) {

    // 设定画面公告评论一览
    $scope.comments = comments;
    
    // 滚动到画面底部
    $scope.$on('$ionicView.afterEnter', function() {
      $ionicScrollDelegate.$getByHandle('scrollComment').scrollBottom();
      $scope.loaded = true;
    });
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 刷新画面内容
    DashComment.list(dash).then(function(comments) {

      // 设定画面公告评论一览
      $scope.comments = comments;

      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.loadMore = function() {
    // 刷新画面内容
    DashComment.list(dash).then(function(comments) {

      // 设定画面公告一览
      $scope.comments = comments;

      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 发布评论
  /**********************************************************/
  $scope.doSend = function() {
    // 发表评论
    DashComment.send(dash, $scope.content).then(function(data) {

      // 设定评论一览
      $scope.comments = data.comments;

      // 清除画面的输入内容
      $scope.content = "";

      // 更新当前的公告信息
      dash.comments = data.dash.comments;

      // 滚动到画面底部
      $ionicScrollDelegate.$getByHandle('scrollComment').scrollBottom();
    });
  };
});
