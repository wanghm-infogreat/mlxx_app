<?php
require_once __DIR__ . '/../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 公告列表取得
 */
$app->get ( '/[{id}]', function (Request $request, Response $response, $args) {
	
	// 处理参数
	$id = isset ( $args ['id'] ) ? $args ['id'] : null;
	
	// 检索条件：id < 参数.id
	$conditions = [ ];
	if ($id != null) {
		$conditions = array (
				' id < ?',
				$id 
		);
	}
	
	// 取得全部列表
	$dashs = Dash::find ( 'all', array (
			'conditions' => $conditions,
			'order' => 'id desc',
			'limit' => COMMON_PAGE_SIZE,
			'include' => array (
					'photos',
					'videos' 
			) 
	) );
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $dashs ) );
	return $response;
} );

/**
 * 新建公告
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建公告信息
	$dash = new Dash ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($dash->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $dash->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$dash = $dash->create ( $request->getParsedBody (), false, true );
	
	// 如果存在照片信息
	if (isset ( $request->getParsedBody () ['photo'] ) && is_array ( $request->getParsedBody () ['photo'] )) {
		
		// 取得参数中的照片信息
		$photos = $request->getParsedBody () ['photo'];
		
		// 登录照片信息
		foreach ( $photos as $photo ) {
			
			// 新建照片信息
			$dashPhoto = new DashPhoto ();
			
			// 设定照片信息的公告id
			$dashPhoto->dash_id = $dash->id;
			// 照片地址url
			$dashPhoto->url = $photo;
			// 照片更新者id
			$dashPhoto->modify_user_id = $dash->modify_user_id;
			
			// 验证内容是否合法
			if ($dashPhoto->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $dashPhoto->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			$dashPhoto->save ();
		}
	}
	
	// 如果存在视频信息
	if (isset ( $request->getParsedBody () ['video'] ) && is_array ( $request->getParsedBody () ['video'] )) {
		
		// 取得参数中的视频信息
		$videos = $request->getParsedBody () ['video'];
		
		// 登录视频信息
		foreach ( $videos as $video ) {
			
			// 新建视频信息
			$dashVideo = new DashVideo ();
			
			// 设定视频信息的公告id
			$dashVideo->dash_id = $dash->id;
			// 视频地址url
			$dashVideo->url = $video;
			// 视频更新者id
			$dashVideo->modify_user_id = $dash->modify_user_id;
			
			// 验证内容是否合法
			if ($dashVideo->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $dashVideo->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			$dashVideo->save ();
		}
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得新建的公告
	$dash = Dash::find ( $dash->id, array (
			'include' => array (
					'photos',
					'videos' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'dash' => json_decode ( $dash->to_json () ),
			'message' => '公告信息正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新公告
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告信息
		$dash = Dash::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '公告信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$dash->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($dash->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $dash->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$dash->save ();
	
	// 删除公告照片
	DashPhoto::delete_all ( array (
			'conditions' => array (
					'dash_id = ?',
					$dash->id 
			) 
	) );
	
	// 删除公告视频
	DashVideo::delete_all ( array (
			'conditions' => array (
					'dash_id = ?',
					$dash->id 
			) 
	) );
	
	// 如果存在照片信息
	if (isset ( $request->getParsedBody () ['photo'] ) && is_array ( $request->getParsedBody () ['photo'] )) {
		
		// 取得参数中的照片信息
		$photos = $request->getParsedBody () ['photo'];
		
		// 登录照片信息
		foreach ( $photos as $photo ) {
			
			// 新建照片信息
			$dashPhoto = new DashPhoto ();
			
			// 设定照片信息的公告id
			$dashPhoto->dash_id = $dash->id;
			// 照片地址url
			$dashPhoto->url = $photo;
			// 照片更新者id
			$dashPhoto->modify_user_id = $dash->modify_user_id;
			
			// 验证内容是否合法
			if ($dashPhoto->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $dashPhoto->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			$dashPhoto->save ();
		}
	}
	
	// 如果存在视频信息
	if (isset ( $request->getParsedBody () ['video'] ) && is_array ( $request->getParsedBody () ['video'] )) {
		
		// 取得参数中的视频信息
		$videos = $request->getParsedBody () ['video'];
		
		// 登录视频信息
		foreach ( $videos as $video ) {
			
			// 新建视频信息
			$dashVideo = new DashVideo ();
			
			// 设定视频信息的公告id
			$dashVideo->dash_id = $dash->id;
			// 视频地址url
			$dashVideo->url = $video;
			// 视频更新者id
			$dashVideo->modify_user_id = $dash->modify_user_id;
			
			// 验证内容是否合法
			if ($dashVideo->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $dashVideo->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			$dashVideo->save ();
		}
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得新建的公告
	$dash = Dash::find ( $dash->id, array (
			'include' => array (
					'photos',
					'videos' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'dash' => json_decode ( $dash->to_json () ),
			'message' => '公告信息正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除公告
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得公告信息
		$dash = Dash::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '公告信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除条件
	$conditions = array (
			'conditions' => array (
					'dash_id = ?',
					$dash->id 
			) 
	);
	
	// 删除公告照片
	DashPhoto::delete_all ( $conditions );
	
	// 删除公告视频
	DashVideo::delete_all ( $conditions );
	
	// 删除公告评论
	DashComment::delete_all ( $conditions );
	
	// 删除公告喜欢
	DashLike::delete_all ( $conditions );
	
	// 删除公告分享
	DashShare::delete_all ( $conditions );
	
	// 删除公告
	$dash->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '公告信息正常删除。' 
	) );
	return $response;
} );

// 运行
$app->run ();
