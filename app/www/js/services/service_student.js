/*
 * 学生Service
 */
angular.module('starter.service.student', [])

/*
 * 学生Factory（Student）
 */
.factory('Student', function($http) {

  // 变量定义
  var students = [];              // 学生一览
  var moredata = false;           // 可否加载更多数据标志

  // 处理定义
  return {
  
    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      students = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得学生
    /**********************************************************/
	student: function(studentid) {
		for(var i = 0; i < students.length; i++) {
			if (students[i].student_id == studentid) {
				return students[i];
			}
		}
	},
  
    /**********************************************************/
    // 取得数据
    /**********************************************************/
    list: function() {

      // 取得api
      var listurl = "/mlxx/student/";

      // 判断是否已经初始化
      if (students.length != 0) {
        listurl = listurl + students[students.length - 1].id;
      }

      // 取得学生
      return $http.get(listurl).then(function (response) {
          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存学生一览
          students = students.concat(response.data);

          // 返回结果
          return students;
      });
    },

    /**********************************************************/
    // 是否还有更多数据
    /**********************************************************/
    hasMore: function() {
      return moredata;
    }
  }
})

/*
 * 学生评价Factory（Evaluation）
 */
.factory('Evaluation', function($http) {

  // 变量定义
  var evaluations = [];           // 评价一览
  var moredata = false;           // 可否加载更多数据标志

  // 处理定义
  return {
  
    /**********************************************************/
    // 初始化
    /**********************************************************/
    init: function() {
      evaluations = [];
      moredata = false;
    },

    /**********************************************************/
    // 取得数据
    /**********************************************************/
    list: function(studentid) {

      // 取得api
      var listurl = "/mlxx/student/evaluation/" + studentid + "/";

      // 判断是否已经初始化
      if (evaluations.length != 0) {
        listurl = listurl + evaluations[evaluations.length - 1].id;
      }

      // 取得学生
      return $http.get(listurl).then(function (response) {
          // 设定是否还有更多数据可加载
          moredata = (response.data.length != 0);

          // 保存评价一览
          evaluations = evaluations.concat(response.data);

          // 返回结果
          return evaluations;
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
