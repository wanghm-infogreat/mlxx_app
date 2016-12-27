<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 消息好友列表取得
 */
$app->get ( '/[{lasttime}]', function (Request $request, Response $response, $args) {
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 处理参数
	$lasttime = null;
	if (isset ( $args ['lasttime'] )) {
		$lasttime = $args ['lasttime'];
	}
	
	// 未设定参数时
	if ($lasttime == null) {
		// 取得全部列表
		$friends = Friend::find ( 'all', array (
				'conditions' => array (
						'user_id = ?',
						$userid 
				),
				'order' => 'modify_datetime desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'friend' 
				) 
		) );
	} else {
		// 取得全部列表
		$friends = Friend::find ( 'all', array (
				'conditions' => array (
						'user_id = ? and modify_datetime < ?',
						$userid,
						$lasttime 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'friend' 
				) 
		) );
	}
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $friends ) );
	return $response;
} );

/**
 * 好友检索
 */
$app->get ( '/search/[{name}[/{id}]]', function (Request $request, Response $response, $args) {
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 处理参数
	$name = isset ( $args ['name'] ) ? $args ['name'] : null;
	$id = isset ( $args ['id'] ) ? $args ['id'] : null;
	
	// 处理检索条件
	$conditions = [ ];
	// 从列表中去除用户本人
	$conditions [0] = "id <> ?";
	$conditions [] = $userid;
	// 姓名关键字
	if ($name != null) {
		// 姓名或称呼的like检索
		$conditions [0] = $conditions [0] . " and (name like ? or `call` like ?)";
		$conditions [] = "%$name%";
		$conditions [] = "%$name%";
	}
	// 用户id分页显示
	if ($id != null) {
		// id的分页检索
		$conditions [0] = $conditions [0] . " and id < ?";
		$conditions [] = $id;
	}
	
	// 检索所有用户信息
	$users = User::find ( 'all', array (
			'conditions' => $conditions,
			'order' => 'id desc',
			'limit' => COMMON_PAGE_SIZE 
	) );
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $users ) );
	return $response;
} );

/**
 * 新建好友
 */
$app->put ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得参数用户id
	$friendid = $args ['id'];
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 判断好友不可以为用户本人
	if ($userid == $friendid) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '不可以加用户自己为好友。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 验证是否已经加过好友
	$friend = Friend::find ( 'first', array (
			'conditions' => array (
					'user_id = ? and friend_user_id = ?',
					$userid,
					$friendid 
			) 
	) );
	
	// 如果已经加过好友
	if ($friend != null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '您已经加过此好友。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 根据参数，新建好友信息
	$friend = new Friend ( $request->getParsedBody (), true );
	// 设定用户id
	$friend->user_id = $userid;
	// 设定好友id
	$friend->friend_user_id = $friendid;
	
	// 验证内容是否合法
	if ($friend->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $friend->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$friend->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库重新取得好友内容
	$friend = Friend::find ( $friend->id, array (
			'include' => array (
					'friend' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'friend' => json_decode ( $friend->to_json () ),
			'message' => '好友信息正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新好友
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得消息信息
		$friend = Friend::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '好友信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$friend->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($friend->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $friend->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$friend->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回结果
	$result = array (
			'friend' => json_decode ( $friend->to_json () ),
			'message' => '好友信息正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除好友
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得好友信息
		$friend = Friend::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '好友信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除好友
	$friend->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '好友信息正常删除。' 
	) );
	return $response;
} );

// 运行
$app->run ();
