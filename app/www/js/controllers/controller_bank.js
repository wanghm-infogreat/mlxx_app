angular.module('starter.controller.bank', [])

.controller('BankCtrl', function($scope, Bank, $cordovaPinDialog, $cordovaDialogs) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  Bank.list().then(function(banks) {
    // 设定画面题库一览
    $scope.banks = banks;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    Bank.init();
    
    // 刷新画面内容
    Bank.list().then(function(banks) {

      // 设定画面题库一览
      $scope.banks = banks;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Bank.list().then(function(banks) {

      // 设定画面题库一览
      $scope.banks = banks;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return Bank.hasMore();
  };

  /**********************************************************/
  // 输入密码，显示答案
  /**********************************************************/
  $scope.inputPassword = function(bank) {
    $cordovaPinDialog.prompt("请输入验证码（可以从推送消息取得）", "答案验证码").then(
      function(result) {
        // result
        if (result.input1 == bank.password) {
          bank.show = true;
        }
    });
  };
});
