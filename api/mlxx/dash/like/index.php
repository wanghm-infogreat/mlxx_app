<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 公告喜欢列表取得
 */
$app->get ( '/{dashid}/[{id}]', function (Request $request, Response $response, $args) {
	
	// 取得公告id
	$dash_id = $args ['dashid'];
	
	// 处理参数
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$dashLikes = DashLike::find ( 'all', array (
				'conditions' => array (
						'dash_id = ?',
						$dash_id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'user' 
				) 
		) );
	} else {
		// 取得全部列表
		$dashLikes = DashLike::find ( 'all', array (
				'conditions' => array (
						'dash_id = ? and id < ?',
						$dash_id,
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
	$response = $response->withJson ( array_map ( 'array_to_json', $dashLikes ) );
	return $response;
} );

/**
 * 新建公告喜欢
 */
$app->put ( '/{dashid}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得参数公告id
	$dashid = $args ['dashid'];
	
	// 验证是否已经为此公告点过赞
	$like = DashLike::find ( 'first', array (
			'conditions' => array (
					'user_id = ? and dash_id = ?',
					$userid,
					$dashid 
			) 
	) );
	
	// 如果已经点过赞
	if ($like != null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '您已经为此公告点过赞。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 根据参数，新建公告信息
	$like = new DashLike ( $request->getParsedBody (), true );
	// 用户id
	$like->user_id = $userid;
	// 公告id
	$like->dash_id = $dashid;
	
	// 验证内容是否合法
	if ($like->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $like->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$like->save ();
	
	// 更新公告信息喜欢数
	Dash::query ( "update mlxx_dash set likes = ( select count(1) from mlxx_dash_like where dash_id = ? ) where id = ?", array (
			$dashid,
			$dashid 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得公告信息
	$dash = Dash::find ( $dashid, array (
			'include' => array (
					'photos',
					'videos' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'like' => json_decode ( $like->to_json () ),
			'dash' => json_decode ( $dash->to_json () ),
			'message' => '公告喜欢正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除公告喜欢
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告信息
		$dashLike = DashLike::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '公告喜欢不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除公告
	$dashLike->delete ();
	
	// 取得公告id
	$dash_id = $dashLike->dash_id;
	
	// 更新公告信息的喜欢数
	Dash::query ( "update mlxx_dash set likes = ( select count(1) from mlxx_dash_like where dash_id = ? ) where id = ?", array (
			$dash_id,
			$dash_id 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得公告信息
	$dash = Dash::find ( $dash_id, array (
			'include' => array (
					'photos',
					'videos' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'dash' => json_decode ( $dash->to_json () ),
			'message' => '公告喜欢正常删除。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

// 运行
$app->run ();
