<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 取得学生评价
 */
$app->get ( '/{studentid}/[{id}]', function (Request $request, Response $response, $args) {
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 处理参数
	$studentid = $args ['studentid'];
	$id = isset ( $args ['id'] ) ? $args ['id'] : null;
	
	// 验证用户是否拥有对象学生
	$student = UserStudent::first ( array (
			'conditions' => array (
					'user_id = ? and student_id = ?',
					$userid,
					$studentid 
			) 
	) );
	
	// 如果不是对象学生，设定错误信息
	if ($student == null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '您没有访问此学生的权限。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 设定检索条件：学生id
	$conditions = array (
			'student_id = ?',
			$studentid 
	);
	
	// 设定id参数时
	if ($id != null) {
		// 设定取得条件
		$conditions [0] = $conditions [0] . " and id < ?";
		$conditions [] = $id;
	}
	
	// 取得全部列表
	$students = StudentEvaluation::find ( 'all', array (
			'conditions' => $conditions,
			'order' => 'id desc',
			'limit' => COMMON_PAGE_SIZE,
			'include' => array (
					'user',
					'student' 
			) 
	) );
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $students ) );
	return $response;
} );

/**
 * 新建学生评价
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建学生信息
	$evalution = new StudentEvaluation ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($evalution->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $evalution->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$evalution->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'evalution' => json_decode ( $evalution->to_json () ),
			'message' => '学生评价信息正常登录。' 
	) );
	return $response;
} );

/**
 * 更新学生评价
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得学生评价信息
		$evalution = StudentEvaluation::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '学生评价信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$evalution->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($evalution->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $evalution->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$evalution->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'evalution' => json_decode ( $evalution->to_json () ),
			'message' => '学生评价信息正常更新。' 
	) );
	return $response;
} );

/**
 * 删除学生评价
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得学生评价信息
		$evalution = StudentEvaluation::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '学生评价信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除学生评价
	$evalution->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '学生评价信息正常删除。' 
	) );
	return $response;
} );

// 运行
$app->run ();
