<?php
require_once __DIR__ . '/../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 取得用户
 */
$app->get ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 处理参数
	$id = $args ['id'];
	
	try {
		// 取得用户信息
		$user = User::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '用户信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 设定返回值
	$response = $response->withJson ( json_decode ( $user->to_json () ) );
	return $response;
} );

/**
 * 用户列表取得
 */
$app->get ( '/list/[{id}]', function (Request $request, Response $response, $args) {
	
	// 处理参数
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$users = User::find ( 'all', array (
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE 
		) );
	} else {
		// 取得全部列表
		$users = User::find ( 'all', array (
				'conditions' => array (
						' id < ?',
						$id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE 
		) );
	}
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $users ) );
	return $response;
} );

/**
 * 新建用户
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建用户信息
	$user = new User ( $request->getParsedBody (), true );
	
	// 设定用户的id为当前用户id
	$user->id = $userid;
	
	// 验证内容是否合法
	if ($user->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $user->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$user = $user->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '用户信息正常登录。' 
	) );
	return $response;
} );

/**
 * 更新用户
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得用户信息
		$user = User::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '用户信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$user->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($user->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $user->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$user->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'user' => json_decode ( $user->to_json () ),
			'message' => '用户信息正常更新。' 
	) );
	return $response;
} );

/**
 * 删除用户
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得用户信息
		$user = User::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '用户信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除用户
	$user->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '用户信息正常删除。' 
	) );
	return $response;
} );

// 运行
$app->run ();
