<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 公告评论列表取得
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
		$dashComments = DashComment::find ( 'all', array (
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
		$dashComments = DashComment::find ( 'all', array (
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
	
	// 倒置全部的评论列表顺序
	$dashComments = array_reverse ( $dashComments );
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $dashComments ) );
	return $response;
} );

/**
 * 新建公告评论
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建公告评论
	$dashComment = new DashComment ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($dashComment->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $dashComment->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$dashComment->save ();
	
	// 取得公告id
	$dash_id = $dashComment->dash_id;
	
	// 更新公告的评论数
	Dash::query ( "update mlxx_dash set comments = ( select count(1) from mlxx_dash_comment where dash_id = ? ) where id = ?", array (
			$dash_id,
			$dash_id 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得评论信息
	$dashComment = DashComment::find ( $dashComment->id, array (
			'include' => array (
					'user' 
			) 
	) );
	
	// 从数据库取得公告信息
	$dash = Dash::find ( $dash_id, array (
			'include' => array (
					'photos',
					'videos' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'comment' => json_decode ( $dashComment->to_json () ),
			'dash' => json_decode ( $dash->to_json () ),
			'message' => '公告评论正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新公告评论
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告评论
		$dashComment = DashComment::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '公告评论不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$dashComment->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($dashComment->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $dashComment->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$dashComment->save ();
	
	// 设定返回结果
	$result = array (
			'comment' => json_decode ( $dashComment->to_json () ),
			'message' => '公告评论正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除公告评论
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告评论
		$dashComment = DashComment::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '公告评论不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 取得公告id
	$dash_id = $dashComment->dash_id;
	
	// 删除公告评论
	$dashComment->delete ();
	
	// 更新公告的评论数
	Dash::query ( "update mlxx_dash set comments = ( select count(1) from mlxx_dash_comment where dash_id = ? ) where id = ?", array (
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
			'message' => '公告评论正常删除。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

// 运行
$app->run ();
