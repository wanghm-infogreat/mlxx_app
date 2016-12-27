<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 公告分享列表取得
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
		$dashShares = DashShare::find ( 'all', array (
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
		$dashShares = DashShare::find ( 'all', array (
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
	$response = $response->withJson ( array_map ( 'array_to_json', $dashShares ) );
	return $response;
} );

/**
 * 新建公告分享
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建公告信息
	$dashShare = new DashShare ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($dashShare->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $dashShare->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$dashShare->create ( $request->getParsedBody (), false );
	
	// 取得公告id
	$dash_id = $dashShare->dash_id;
	
	// 更新公告信息
	try {
		$dash = Dash::find ( $dash_id );
	} catch ( RecordNotFound $e ) {
		// rollback
		ConnectionManager::get_connection ()->rollback ();
		// 设定错误信息，返回
		$response = $response->withJson ( '公告信息不存在。', DATA_ERROR );
		return $response;
	}
	
	// 更新
	$dash->shares = $dash->shares + 1;
	$dash->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( '公告分享正常登录。' );
	return $response;
} );

/**
 * 更新公告分享
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告信息
		$dashShare = DashShare::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( '公告分享不存在。', DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$dashShare->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($dashShare->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $dashShare->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$dashShare->save ();
	
	// 设定返回内容
	$response = $response->withJson ( '公告分享正常更新。' );
	return $response;
} );

/**
 * 删除公告分享
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告信息
		$dashShare = DashShare::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( '公告分享不存在。', DATA_ERROR );
		return $response;
	}
	
	// 删除公告
	$dashShare->delete ();
	
	// 取得公告id
	$dash_id = $dashShare->dash_id;
	
	// 更新公告信息
	try {
		$dash = Dash::find ( $dash_id );
	} catch ( RecordNotFound $e ) {
		// rollback
		ConnectionManager::get_connection ()->rollback ();
		// 设定错误信息，返回
		$response = $response->withJson ( '公告信息不存在。', DATA_ERROR );
		return $response;
	}
	
	// 更新
	$dash->shares = $dash->shares - 1;
	$dash->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( '公告分享正常删除。' );
	return $response;
} );

// 运行
$app->run ();
