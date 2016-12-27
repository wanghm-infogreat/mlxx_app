<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;
use ActiveRecord\DateTime;

/**
 * 消息好友列表取得
 */
$app->get ( '/users/[{lasttime}]', function (Request $request, Response $response, $args) {
	
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
		$users = MessageUser::find ( 'all', array (
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
		$users = MessageUser::find ( 'all', array (
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
	$response = $response->withJson ( array_map ( 'array_to_json', $users ) );
	return $response;
} );

/**
 * 消息列表取得
 */
$app->get ( '/messages/{friendid}/[{id}]', function (Request $request, Response $response, $args) {
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 处理参数
	$friendid = $args ['friendid'];
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$messages = Message::find ( 'all', array (
				'conditions' => array (
						'user_id = ? and (from_user_id = ? or to_user_id = ?)',
						$userid,
						$friendid,
						$friendid 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'from',
						'to' 
				) 
		) );
	} else {
		// 取得全部列表
		$messages = Message::find ( 'all', array (
				'conditions' => array (
						'user_id = ? and (from_user_id = ? or to_user_id = ?) and id < ?',
						$userid,
						$friendid,
						$friendid,
						$id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'from',
						'to' 
				) 
		) );
	}
	
	// 倒置全部的消息列表顺序
	$messages = array_reverse ( $messages );
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 更新消息的阅读状态
	foreach ( $messages as $message ) {
		
		// 判断消息是否为发送给我，并且是未阅读状态
		if ($message->to_user_id == $userid && $message->read_time == null) {
			
			// 更新阅读时间
			$message->read_time = new DateTime ();
			
			// 保存到数据库
			$message->save ();
		}
	}
	
	// 取得用户的未读消息的件数
	$new_count = Message::count ( array (
			'conditions' => array (
					'user_id = ? and from_user_id = ? and read_time is null',
					$userid,
					$friendid 
			) 
	) );
	
	// 取得用户消息
	$messageUser = MessageUser::first ( array (
			'conditions' => array (
					'user_id = ? and friend_user_id = ?',
					$userid,
					$friendid 
			) 
	) );
	
	// 更新用户的未读消息的件数
	$messageUser->new_count = $new_count;
	$messageUser->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回值
	$result = array (
			'user' => json_decode ( $messageUser->to_json () ),
			'msg' => array_map ( 'array_to_json', $messages ) 
	);
	
	// 设定返回值
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 新建消息
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建消息信息
	$message = new Message ( $request->getParsedBody (), true );
	// 设定用户id
	$message->user_id = $userid;
	// 设定发送用户id
	$message->from_user_id = $userid;
	// 设定发送时间
	$message->send_time = new DateTime ();
	// 设定阅读时间
	$message->read_time = new DateTime ();
	
	// 验证内容是否合法
	if ($message->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $message->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$message->save ();
	
	// 新建发送对象用户的消息
	$friend_message = new Message ( $request->getParsedBody (), true );
	
	// 设定用户id
	$friend_message->user_id = $message->to_user_id;
	// 设定发送用户id
	$friend_message->from_user_id = $userid;
	// 设定发送时间
	$friend_message->send_time = new DateTime ();
	// 设定阅读时间
	$friend_message->read_time = null;
	
	// 验证内容是否合法
	if ($friend_message->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $friend_message->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$friend_message->save ();
	
	// 取得好友的消息用户信息
	$messageUser = MessageUser::first ( array (
			'conditions' => array (
					'user_id = ? and friend_user_id = ?',
					$message->to_user_id,
					$userid 
			) 
	) );
	
	// 如果没有信息，则新建好友消息用户
	if ($messageUser == null) {
		
		// 新建消息用户
		$messageUser = new MessageUser ( $request->getParsedBody (), true );
		// 用户id
		$messageUser->user_id = $message->to_user_id;
		// 好友用户id
		$messageUser->friend_user_id = $userid;
		// 消息数
		$messageUser->message_count = 1;
		// 新消息数
		$messageUser->new_count = 1;
		
		// 保存到数据库
		$messageUser->save ();
	} else {
		
		// 更新数据库（消息数+1）
		$messageUser->message_count = $messageUser->message_count + 1;
		// 更新数据库（新消息数+1）
		$messageUser->new_count = $messageUser->new_count + 1;
		
		// 保存到数据库
		$messageUser->save ();
	}
	
	// 取得消息用户信息
	$messageUser = MessageUser::first ( array (
			'conditions' => array (
					'user_id = ? and friend_user_id = ?',
					$userid,
					$message->to_user_id 
			) 
	) );
	
	// 如果没有信息，则新建消息用户
	if ($messageUser == null) {
		
		// 新建消息用户
		$messageUser = new MessageUser ( $request->getParsedBody (), true );
		// 用户id
		$messageUser->user_id = $userid;
		// 好友用户id
		$messageUser->friend_user_id = $message->to_user_id;
		// 消息数
		$messageUser->message_count = 1;
		// 新消息数
		$messageUser->new_count = 0;
		
		// 保存到数据库
		$messageUser->save ();
	} else {
		
		// 更新数据库（消息数+1）
		$messageUser->message_count = $messageUser->message_count + 1;
		
		// 保存到数据库
		$messageUser->save ();
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 取得消息用户信息
	$messageUser = MessageUser::find ( $messageUser->id, array (
			'include' => array (
					'friend' 
			) 
	) );
	
	// 从数据库重新取得消息内容
	$message = Message::find ( $message->id, array (
			'include' => array (
					'from',
					'to' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'user' => json_decode ( $messageUser->to_json () ),
			'msg' => json_decode ( $message->to_json () ),
			'message' => '消息信息正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新消息
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得消息信息
		$message = Message::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '消息信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$message->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($message->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $message->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$message->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回结果
	$result = array (
			'msg' => json_decode ( $message->to_json () ),
			'message' => '消息信息正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除消息
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得消息信息
		$message = Message::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '消息信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 取得好友的消息用户信息
	$messageUser = MessageUser::first ( array (
			'conditions' => array (
					'user_id = ? and friend_user_id = ?',
					$message->user_id,
					$message->from_user_id 
			) 
	) );
	
	// 更新消息件数
	$messageUser->message_count = $messageUser->message_count - 1;
	$messageUser->save ();
	
	// 删除消息
	$message->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '消息信息正常删除。' 
	) );
	return $response;
} );

/**
 * 删除好友的消息
 */
$app->delete ( '/users/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得好友消息信息
		$messageUser = MessageUser::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '好友消息信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除此好友的所有消息
	Message::delete_all ( array (
			'conditions' => array (
					'user_id = ? and ( from_user_id = ? or to_user_id = ? )',
					$messageUser->user_id,
					$messageUser->friend_user_id,
					$messageUser->friend_user_id 
			) 
	) );
	
	// 删除消息
	$messageUser->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '好友消息信息正常删除。' 
	) );
	return $response;
} );

// 运行
$app->run ();
