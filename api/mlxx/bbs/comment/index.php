<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 评论列表取得
 */
$app->get ( '/{titleid}/[{id}]', function (Request $request, Response $response, $args) {
	
	// 处理参数
	$titleid = $args ['titleid'];
	
	$id = null;
	if (isset ( $args ['id'] )) {
		$id = $args ['id'];
	}
	
	// 未设定参数时
	if ($id == null) {
		// 取得全部列表
		$comments = BbsComment::find ( 'all', array (
				'conditions' => array (
						'title_id = ?',
						$titleid 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'photos',
						'videos',
						'user' 
				) 
		) );
	} else {
		// 取得全部列表
		$comments = BbsComment::find ( 'all', array (
				'conditions' => array (
						'title_id = ? and id < ?',
						$titleid,
						$id 
				),
				'order' => 'id desc',
				'limit' => COMMON_PAGE_SIZE,
				'include' => array (
						'photos',
						'videos',
						'user' 
				) 
		) );
	}
	
	// 倒置全部的评论列表顺序
	$comments = array_reverse ( $comments );
	
	// 设定返回值
	$response = $response->withJson ( array_map ( 'array_to_json', $comments ) );
	return $response;
} );

/**
 * 新建评论
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建评论信息
	$comment = new BbsComment ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($comment->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $comment->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$comment = $comment->create ( $request->getParsedBody (), false, true );
	
	// 如果存在照片信息
	if (isset ( $request->getParsedBody () ['photo'] ) && is_array ( $request->getParsedBody () ['photo'] )) {
		
		// 取得参数中的照片信息
		$photos = $request->getParsedBody () ['photo'];
		
		// 登录照片信息
		foreach ( $photos as $photo ) {
			
			// 新建照片信息
			$bbsPhoto = new BbsPhoto ();
			
			// 设定照片信息的组id
			$bbsPhoto->group_id = $comment->group_id;
			// 设定照片信息的话题id
			$bbsPhoto->title_id = $comment->title_id;
			// 设定照片信息的评论id
			$bbsPhoto->comment_id = $comment->id;
			// 照片地址url
			$bbsPhoto->url = $photo;
			// 照片更新者id
			$bbsPhoto->modify_user_id = $comment->modify_user_id;
			
			// 验证内容是否合法
			if ($bbsPhoto->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $bbsPhoto->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BbsPhoto::create ( $bbsPhoto->to_array (), false, true );
		}
	}
	
	// 如果存在视频信息
	if (isset ( $request->getParsedBody () ['video'] ) && is_array ( $request->getParsedBody () ['video'] )) {
		
		// 取得参数中的视频信息
		$videos = $request->getParsedBody () ['video'];
		
		// 登录视频信息
		foreach ( $videos as $video ) {
			
			// 新建视频信息
			$bbsVideo = new BbsVideo ();
			
			// 设定照片信息的组id
			$bbsVideo->group_id = $comment->group_id;
			// 设定照片信息的话题id
			$bbsVideo->title_id = $comment->title_id;
			// 设定视频信息的评论id
			$bbsVideo->comment_id = $comment->id;
			// 视频地址url
			$bbsVideo->url = $video;
			// 视频更新者id
			$bbsVideo->modify_user_id = $comment->modify_user_id;
			
			// 验证内容是否合法
			if ($bbsVideo->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $bbsVideo->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BbsVideo::create ( $bbsVideo->to_array (), false, true );
		}
	}
	
	// 更新公告的评论数
	BbsTitle::query ( "update mlxx_bbs_title set comments = ( select count(1) from mlxx_bbs_comment where title_id = ? ) where id = ?", array (
			$comment->title_id,
			$comment->title_id 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库话题信息
	$title = BbsTitle::find ( $comment->title_id, array (
			'include' => array (
					'user' 
			) 
	) );
	
	// 从数据库取得评论信息
	$comment = BbsComment::find ( $comment->id, array (
			'include' => array (
					'photos',
					'videos',
					'user' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'title' => json_decode ( $title->to_json () ),
			'comment' => json_decode ( $comment->to_json () ),
			'message' => '评论信息正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新评论
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得评论信息
		$comment = BbsComment::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '评论信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$comment->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($comment->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $comment->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$comment->save ();
	
	// 删除评论照片
	BbsPhoto::delete_all ( array (
			'conditions' => array (
					'comment_id = ?',
					$comment->id 
			) 
	) );
	
	// 删除评论视频
	BbsVideo::delete_all ( array (
			'conditions' => array (
					'comment_id = ?',
					$comment->id 
			) 
	) );
	
	// 如果存在照片信息
	if (isset ( $request->getParsedBody () ['photo'] ) && is_array ( $request->getParsedBody () ['photo'] )) {
		
		// 取得参数中的照片信息
		$photos = $request->getParsedBody () ['photo'];
		
		// 登录照片信息
		foreach ( $photos as $photo ) {
			
			// 新建照片信息
			$bbsPhoto = new BbsPhoto ();
			
			// 设定照片信息的组id
			$bbsPhoto->group_id = $comment->group_id;
			// 设定照片信息的话题id
			$bbsPhoto->title_id = $comment->title_id;
			// 设定照片信息的评论id
			$bbsPhoto->comment_id = $comment->id;
			// 照片地址url
			$bbsPhoto->url = $photo;
			// 照片更新者id
			$bbsPhoto->modify_user_id = $comment->modify_user_id;
			
			// 验证内容是否合法
			if ($bbsPhoto->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $bbsPhoto->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BbsPhoto::create ( $bbsPhoto->to_array (), false, true );
		}
	}
	
	// 如果存在视频信息
	if (isset ( $request->getParsedBody () ['video'] ) && is_array ( $request->getParsedBody () ['video'] )) {
		
		// 取得参数中的视频信息
		$videos = $request->getParsedBody () ['video'];
		
		// 登录视频信息
		foreach ( $videos as $video ) {
			
			// 新建视频信息
			$bbsVideo = new BbsVideo ();
			
			// 设定照片信息的组id
			$bbsVideo->group_id = $comment->group_id;
			// 设定照片信息的话题id
			$bbsVideo->title_id = $comment->title_id;
			// 设定视频信息的评论id
			$bbsVideo->comment_id = $comment->id;
			// 视频地址url
			$bbsVideo->url = $video;
			// 视频更新者id
			$bbsVideo->modify_user_id = $comment->modify_user_id;
			
			// 验证内容是否合法
			if ($bbsVideo->is_invalid ()) {
				// rollback
				ConnectionManager::get_connection ()->rollback ();
				// 设定错误信息，返回
				$response = $response->withJson ( $bbsVideo->errors->to_array (), VALIDATION_ERROR );
				return $response;
			}
			
			// 登录数据库
			BbsVideo::create ( $bbsVideo->to_array (), false, true );
		}
	}
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库取得评论信息
	$comment = BbsComment::find ( $comment->id, array (
			'include' => array (
					'photos',
					'videos',
					'user' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'comment' => json_decode ( $comment->to_json () ),
			'message' => '评论信息正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除评论
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得评论信息
		$comment = BbsComment::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '评论信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除评论照片
	BbsPhoto::delete_all ( array (
			'conditions' => array (
					'comment_id = ?',
					$comment->id 
			) 
	) );
	
	// 删除评论视频
	BbsVideo::delete_all ( array (
			'conditions' => array (
					'comment_id = ?',
					$comment->id 
			) 
	) );
	
	// 取得话题id
	$title_id = $comment->title_id;
	
	// 删除评论
	$comment->delete ();
	
	// 更新话题的评论数
	BbsTitle::query ( "update mlxx_bbs_title set comments = ( select count(1) from mlxx_bbs_comment where title_id = ? ) where id = ?", array (
			$title_id,
			$title_id 
	) );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 从数据库话题信息
	$title = BbsTitle::find ( $title_id, array (
			'include' => array (
					'user' 
			) 
	) );
	
	// 设定返回结果
	$result = array (
			'title' => json_decode ( $title->to_json () ),
			'message' => '评论信息正常删除。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

// 运行
$app->run ();
