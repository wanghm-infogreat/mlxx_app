<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 话题列表取得
 */
$app->get ( '/{groupid:[0-9]+}/[{id}]', function (Request $request, Response $response, $args) {
	
	// 处理参数
	$groupid = $args ['groupid'];
	
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$titles = BbsTitle::find ( 'all', array (
				'conditions' => array (
						'group_id = ?',
						$groupid 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'user' 
				) 
		) );
	} else {
		// 取得全部列表
		$titles = BbsTitle::find ( 'all', array (
				'conditions' => array (
						'group_id = ? and id < ?',
						$groupid,
						$id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'user' 
				) 
		) );
	}
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $titles ) );
	return $response;
} );

/**
 * 新建话题
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建话题信息
	$title = new BbsTitle ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($title->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $title->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$title = $title->create ( $request->getParsedBody (), false, true );
	
	// 更新论坛组的话题数
	BbsGroup::query ( "update mlxx_bbs_group set titles = ( select count(1) from mlxx_bbs_title where group_id = ? ) where id = ?", array (
			$title->group_id,
			$title->group_id 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得新建的话题信息
	$title = BbsTitle::find ( $title->id, array (
			'include' => array (
					'user' 
			) 
	) );
	
	// 从数据库取得论坛组信息
	$group = BbsGroup::find ( $title->group_id );
	
	// 设定返回结果
	$result = array (
			'group' => json_decode ( $group->to_json () ),
			'title' => json_decode ( $title->to_json () ),
			'message' => '话题信息正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新话题
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得话题信息
		$title = BbsTitle::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '话题信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$title->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($title->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $title->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$title->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得话题信息
	$title = BbsTitle::find ( $title->id, array (
			'include' => array (
					'user' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'title' => json_decode ( $title->to_json () ),
			'message' => '话题信息正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除话题
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得话题信息
		$title = BbsTitle::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '话题信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除评论照片
	BbsPhoto::delete_all ( array (
			'conditions' => array (
					'title_id = ?',
					$title->id 
			) 
	) );
	
	// 删除话题视频
	BbsVideo::delete_all ( array (
			'conditions' => array (
					'title_id = ?',
					$title->id 
			) 
	) );
	
	// 删除话题评论
	BbsComment::delete_all ( array (
			'conditions' => array (
					'title_id = ?',
					$title->id 
			) 
	) );
	
	// 删除话题
	$title->delete ();
	
	// 更新论坛组的话题数
	BbsGroup::query ( "update mlxx_bbs_group set titles = ( select count(1) from mlxx_bbs_title where group_id = ? ) where id = ?", array (
			$title->group_id,
			$title->group_id 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得论坛组信息
	$group = BbsGroup::find ( $title->group_id );
	
	// 设定返回结果
	$result = array (
			'group' => json_decode ( $group->to_json () ),
			'message' => '话题信息正常删除。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 收藏话题列表
 */
$app->get ( '/favorite/[{id}]', function (Request $request, Response $response, $args) {
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 处理参数
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$titles = BbsTitleFavorite::find ( 'all', array (
				'conditions' => array (
						'user_id = ?',
						$userid 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE 
		) );
	} else {
		// 取得全部列表
		$titles = BbsTitleFavorite::find ( 'all', array (
				'conditions' => array (
						'user_id = ? and id < ?',
						$userid,
						$id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE 
		) );
	}
	
	// 重新设定返回结果
	$result = [ ];
	
	// 取得所有话题下的用户信息
	foreach ( $titles as $title ) {
		// 从论坛话题表中，取得所有对象
		$title_get = BbsTitle::find ( $title->title_id, array (
				'include' => array (
						'user' 
				) 
		) );
		
		// 设定返回结果
		$result [] = array (
				'favorite' => json_decode ( $title->to_json () ),
				'title' => json_decode ( $title_get->to_json () ) 
		);
	}
	
	// 设定返回值
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 收藏话题
 */
$app->put ( '/favorite/{titleid}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得参数的话题id
	$titleid = $args ['titleid'];
	
	// 取得话题信息
	$title = BbsTitleFavorite::find ( 'first', array (
			'conditions' => array (
					' user_id = ? and title_id = ?',
					$userid,
					$titleid 
			) 
	) );
	
	// 找到时，设定错误信息
	if ($title != null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '您已收藏过此话题。' 
		) );
		return $response;
	}
	
	// 根据参数，新建收藏话题信息
	$title = new BbsTitleFavorite ( $request->getParsedBody () );
	$title->user_id = $userid;
	$title->title_id = $titleid;
	
	// 验证内容是否合法
	if ($title->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $title->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$title = $title->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '话题收藏正常登录。' 
	) );
	return $response;
} );

/**
 * 取消收藏话题
 */
$app->delete ( '/favorite/{titleid}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得参数的话题id
	$titleid = $args ['titleid'];
	
	// 取得话题信息
	$title = BbsTitleFavorite::find ( 'first', array (
			'conditions' => array (
					' user_id = ? and title_id = ?',
					$userid,
					$titleid 
			) 
	) );
	
	// 未找到时，设定错误信息
	if ($title == null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '话题收藏信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除收藏的话题
	$title->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '话题收藏正常取消。' 
	) );
	return $response;
} );

// 运行
$app->run ();
