/*
 * 学园Service
 */
angular.module('starter.service.bank', [])

/*
 * 学园Factory（Bank）
 */
.factory('Bank', function($http) {

  // 变量定义
  var banks = [];                 // 学园题库一览
  var moredata = false;           // 可否加载更多数据标志

  // 处理定义
  return {
  
    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      banks = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得数据
    /**********************************************************/
    list: function() {

      // 取得api
      var listurl = "/mlxx/bank/";

      // 判断是否已经初始化
      if (banks.length != 0) {
        listurl = listurl + banks[banks.length - 1].id;
      }

      // 取得题库
      return $http.get(listurl).then(function (response) {
          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存题库一览
          banks = banks.concat(response.data);

          // 返回结果
          return banks;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function() {
      return moredata;
    }
  }
});
