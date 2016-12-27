/*
 * Student Controller
 */
angular.module('starter.controller.student', [])

/*
 * StudentCtrl
 */
.controller('StudentCtrl', function($scope, $state, Student) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 取得评价一览
  Student.list().then(function(students) {
  
    // 设定画面学生一览
    $scope.students = students;

  	// 根据学生数决定显示画面
	// if (students.length == 1) {
	//	$state.go('tab.evaluation', {studentid:students[0].student.id});
	//	return;
	// }
  });

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Student.list().then(function(students) {

      // 设定画面学生一览
      $scope.students = students;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return Student.hasMore();
  };
})

/*
 * EvaluationCtrl
 */
.controller('EvaluationCtrl', function($scope, $stateParams, Student, Evaluation) {

  /**********************************************************/
  // 画面初始化
  /**********************************************************/
  // 取得参数学生id
  var studentid = $stateParams.studentid;
  var student = Student.student(studentid);
  
  // 显示当前学生信息
  $scope.student = student;
  
  // 初始化
  Evaluation.init();

  // 取得评价一览
  Evaluation.list(studentid).then(function(evaluations) {
    // 设定画面评价一览
    $scope.evaluations = evaluations;
  });

  /**********************************************************/
  // 刷新
  /**********************************************************/
  $scope.doRefresh = function() {
    // 初始化
    Evaluation.init();
    
    // 刷新画面内容
    Evaluation.list(studentid).then(function(evaluations) {

      // 设定画面评价一览
      $scope.evaluations = evaluations;
      
      // 关闭画面刷新动画效果
      $scope.$broadcast('scroll.refreshComplete');
    });
  };

  /**********************************************************/
  // 加载更多
  /**********************************************************/
  $scope.doLoadMore = function() {
    // 刷新画面内容
    Evaluation.list(studentid).then(function(evaluations) {

      // 设定画面评价一览
      $scope.evaluations = evaluations;
      
      // 关闭画面加载动画效果
      $scope.$broadcast('scroll.infiniteScrollComplete');
    });
  };
  
  /**********************************************************/
  // 能否加载更多数据控制
  /**********************************************************/
  $scope.hasMore = function() {
    return Evaluation.hasMore();
  };
});
