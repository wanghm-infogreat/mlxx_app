<?php
require_once __DIR__ . '/../../vendor/initlize.php';

use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 取得学生列表
 */
$app->get ( '/[{id}]', function (Request $request, Response $response, $args) {
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 处理参数
	$id = isset ( $args ['id'] ) ? $args ['id'] : null;
	
	// 设定取得条件：当前用户所拥有的所有学生
	$conditions = array (
			'user_id = ?',
			$userid 
	);
	
	// 设定id参数时
	if ($id != null) {
		// 设定取得条件
		$conditions [0] = $conditions [0] . " and id > ?";
		$conditions [] = $id;
	}
	
	// 取得全部列表
	$students = UserStudent::find ( 'all', array (
			'conditions' => $conditions,
			'order' => 'id asc',
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
 * 新建学生
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建学生信息
	$student = new Student ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($student->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $student->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$student->save ();
	
	// 根据学生的所属用户id，设定学生用户表
	// 取得提交数据中，学生的所属用户id列表
	$users = [ ];
	if (isset ( $request->getParsedBody () ['userid'] ) && is_array ( $request->getParsedBody () ['userid'] )) {
		$users = $request->getParsedBody () ['userid'];
	}
	
	// 登录学生用户信息
	foreach ( $users as $user ) {
		
		// 新建学生用户信息
		$user_student = new UserStudent ( $request->getParsedBody (), true );
		
		// 设定学生id
		$user_student->student_id = $student->id;
		// 设定用户id
		$user_student->user_id = $user;
		
		// 验证内容是否合法
		if ($user_student->is_invalid ()) {
			// rollback
			ConnectionManager::get_connection ()->rollback ();
			// 设定错误信息，返回
			$response = $response->withJson ( $user_student->errors->to_array (), VALIDATION_ERROR );
			return $response;
		}
		
		// 保存到数据库
		$user_student->save ();
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'student' => json_decode ( $student->to_json () ),
			'message' => '学生信息正常登录。' 
	) );
	return $response;
} );

/**
 * 更新学生
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得学生信息
		$student = Student::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '学生信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$student->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($student->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $student->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$student->save ();
	
	// 删除用户学生表
	UserStudent::delete_all ( array (
			'conditions' => array (
					'student_id = ?',
					$id 
			) 
	) );
	
	// 根据学生的所属用户id，设定学生用户表
	// 取得提交数据中，学生的所属用户id列表
	$users = [ ];
	if (isset ( $request->getParsedBody () ['userid'] ) && is_array ( $request->getParsedBody () ['userid'] )) {
		$users = $request->getParsedBody () ['userid'];
	}
	
	// 登录学生用户信息
	foreach ( $users as $user ) {
		
		// 新建学生用户信息
		$user_student = new UserStudent ( $request->getParsedBody (), true );
		
		// 设定学生id
		$user_student->student_id = $student->id;
		// 设定用户id
		$user_student->user_id = $user;
		
		// 验证内容是否合法
		if ($user_student->is_invalid ()) {
			// rollback
			ConnectionManager::get_connection ()->rollback ();
			// 设定错误信息，返回
			$response = $response->withJson ( $user_student->errors->to_array (), VALIDATION_ERROR );
			return $response;
		}
		
		// 保存到数据库
		$user_student->save ();
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'student' => json_decode ( $student->to_json () ),
			'message' => '学生信息正常更新。' 
	) );
	return $response;
} );

/**
 * 删除学生
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得学生信息
		$student = Student::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '学生信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除学生
	$student->delete ();
	
	// 删除用户学生表
	UserStudent::delete_all ( array (
			'conditions' => array (
					'student_id = ?',
					$id 
			) 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '学生信息正常删除。' 
	) );
	return $response;
} );

// 运行
$app->run ();
