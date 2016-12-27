<?php
require_once __DIR__ . '/../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 题库列表取得
 */
$app->get ( '/[{id}]', function (Request $request, Response $response, $args) {
	
	// 处理参数
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$banks = Bank::find ( 'all', array (
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'photos',
						'videos' 
				) 
		) );
	} else {
		// 取得全部列表
		$banks = Bank::find ( 'all', array (
				'conditions' => array (
						' id < ?',
						$id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'photos',
						'videos' 
				) 
		) );
	}
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $banks ) );
	return $response;
} );

/**
 * 新建题库
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建题库信息
	$bank = new Bank ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($bank->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $bank->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$bank = $bank->create ( $request->getParsedBody (), false, true );
	
	// 如果存在照片信息
	if (isset ( $request->getParsedBody () ['photo'] ) && is_array ( $request->getParsedBody () ['photo'] )) {
		
		// 取得参数中的照片信息
		$photos = $request->getParsedBody () ['photo'];
		
		// 登录照片信息
		foreach ( $photos as $photo ) {
			
			// 新建照片信息
			$photo = new BankPhoto ();
			
			// 设定照片信息的题库id
			$photo->bank_id = $bank->id;
			// 照片地址url
			$photo->url = $photo;
			// 照片更新者id
			$photo->modify_user_id = $bank->modify_user_id;
			
			// 验证内容是否合法
			if ($photo->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $photo->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BankPhoto::create ( $photo->to_array (), false, true );
		}
	}
	
	// 如果存在视频信息
	if (isset ( $request->getParsedBody () ['video'] ) && is_array ( $request->getParsedBody () ['video'] )) {
		
		// 取得参数中的视频信息
		$videos = $request->getParsedBody () ['video'];
		
		// 登录视频信息
		foreach ( $videos as $video ) {
			
			// 新建视频信息
			$video = new BankVideo ();
			
			// 设定视频信息的题库id
			$video->bank_id = $bank->id;
			// 视频地址url
			$video->url = $video;
			// 视频更新者id
			$video->modify_user_id = $bank->modify_user_id;
			
			// 验证内容是否合法
			if ($video->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $video->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BankVideo::create ( $video->to_array (), false, true );
		}
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( '题库信息正常登录。' );
	return $response;
} );

/**
 * 更新题库
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得题库信息
		$bank = Bank::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( '题库信息不存在。', DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$bank->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($bank->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $bank->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$bank->save ();
	
	// 删除题库照片
	BankPhoto::delete_all ( array (
			'conditions' => array (
					'bank_id = ?',
					$bank->id 
			) 
	) );
	
	// 删除题库视频
	BankVideo::delete_all ( array (
			'conditions' => array (
					'bank_id = ?',
					$bank->id 
			) 
	) );
	
	// 如果存在照片信息
	if (isset ( $request->getParsedBody () ['photo'] ) && is_array ( $request->getParsedBody () ['photo'] )) {
		
		// 取得参数中的照片信息
		$photos = $request->getParsedBody () ['photo'];
		
		// 登录照片信息
		foreach ( $photos as $photo ) {
			
			// 新建照片信息
			$photo = new BankPhoto ();
			
			// 设定照片信息的题库id
			$photo->bank_id = $bank->id;
			// 照片地址url
			$photo->url = $photo;
			// 照片更新者id
			$photo->modify_user_id = $bank->modify_user_id;
			
			// 验证内容是否合法
			if ($photo->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $photo->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BankPhoto::create ( $photo->to_array (), false, true );
		}
	}
	
	// 如果存在视频信息
	if (isset ( $request->getParsedBody () ['video'] ) && is_array ( $request->getParsedBody () ['video'] )) {
		
		// 取得参数中的视频信息
		$videos = $request->getParsedBody () ['video'];
		
		// 登录视频信息
		foreach ( $videos as $video ) {
			
			// 新建视频信息
			$video = new BankVideo ();
			
			// 设定视频信息的题库id
			$video->bank_id = $bank->id;
			// 视频地址url
			$video->url = $video;
			// 视频更新者id
			$video->modify_user_id = $bank->modify_user_id;
			
			// 验证内容是否合法
			if ($video->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $video->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BankVideo::create ( $video->to_array (), false, true );
		}
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( '题库信息正常更新。' );
	return $response;
} );

/**
 * 删除题库
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得题库信息
		$bank = Bank::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( '题库信息不存在。', DATA_ERROR );
		return $response;
	}
	
	// 删除题库照片
	BankPhoto::delete_all ( array (
			'conditions' => array (
					'bank_id = ?',
					$bank->id 
			) 
	) );
	
	// 删除题库视频
	BankVideo::delete_all ( array (
			'conditions' => array (
					'bank_id = ?',
					$bank->id 
			) 
	) );
	
	// 删除题库
	$bank->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( '题库信息正常删除。' );
	return $response;
} );

// 运行
$app->run ();
