<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \ActiveRecord\ConnectionManager;
use \ActiveRecord\RecordNotFound;

/**
 * 论坛组列表取得
 */
$app->get ( '/', function (Request $request, Response $response) {
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得全部列表
	$groups = BbsGroup::find ( 'all', array (
			'order' => 'id asc' 
	) );
	
	// 取得用户关注的论坛组信息
	$userGroups = BbsGroupFavorite::find ( 'all', array (
			'conditions' => array (
					' user_id = ?',
					$userid 
			),
			'order' => 'id asc',
			'include' => array (
					'user',
					'group' 
			) 
	) );
	
	// 取得用户已关注的所有论坛组id
	$userGroupIds = [ ];
	$favoriteGroups = [ ];
	foreach ( $userGroups as $userGroup ) {
		$userGroupIds [] = $userGroup->group_id;
		$favoriteGroups [] = $userGroup->group;
	}
	
	// 从全部的列表中，去除用户已关注的论坛组信息
	$fixGroups = [ ];
	$otherGroups = [ ];
	foreach ( $groups as $group ) {
		
		// 判断groupid是否已关注
		if (! in_array ( $group->id, $userGroupIds )) {
			// 根据是否固定标志
			if ($group->fix_flag == '1') {
				// 加入到固定论坛组中
				$fixGroups [] = $group;
			} else {
				// 加入到其他论坛组中
				$otherGroups [] = $group;
			}
		}
	}
	
	// 组合论坛组和用户的关注列表
	$result = array (
			'fixes' => array_map ( 'array_to_json', $fixGroups ),
			'favorites' => array_map ( 'array_to_json', $favoriteGroups ),
			'others' => array_map ( 'array_to_json', $otherGroups ) 
	);
	
	// 设定返回值
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 新建论坛组
 */
$app->put ( '/', function (Request $request, Response $response) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 根据参数，新建论坛组信息
	$group = new BbsGroup ( $request->getParsedBody (), true );
	
	// 验证内容是否合法
	if ($group->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $group->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$group = $group->create ( $request->getParsedBody (), false, true );
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回结果
	$result = array (
			'group' => json_decode ( $group->to_json () ),
			'message' => '论坛组信息正常登录。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 更新论坛组
 */
$app->post ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得论坛组信息
		$group = BbsGroup::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '论坛组信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新内容
	$group->update_attributes ( $request->getParsedBody () );
	
	// 验证内容是否合法
	if ($group->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $group->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$group->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回结果
	$result = array (
			'group' => json_decode ( $group->to_json () ),
			'message' => '论坛组信息正常更新。' 
	);
	
	// 设定返回内容
	$response = $response->withJson ( $result );
	return $response;
} );

/**
 * 删除论坛组
 */
$app->delete ( '/{id}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得id参数
	$id = $args ['id'];
	
	try {
		// 取得论坛组信息
		$group = BbsGroup::find ( $id );
	} catch ( RecordNotFound $e ) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '论坛组信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除论坛组照片
	BbsPhoto::delete_all ( array (
			'conditions' => array (
					'group_id = ?',
					$group->id 
			) 
	) );
	
	// 删除论坛组视频
	BbsVideo::delete_all ( array (
			'conditions' => array (
					'group_id = ?',
					$group->id 
			) 
	) );
	
	// 删除论坛组评论
	BbsComment::delete_all ( array (
			'conditions' => array (
					'group_id = ?',
					$group->id 
			) 
	) );
	
	// 删除论坛组话题
	BbsTitle::delete_all ( array (
			'conditions' => array (
					'group_id = ?',
					$group->id 
			) 
	) );
	
	// 删除论坛组
	$group->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '论坛组信息正常删除。' 
	) );
	return $response;
} );

/**
 * 关注论坛组
 */
$app->put ( '/favorite/{groupid}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得参数的论坛组id
	$groupid = $args ['groupid'];
	
	// 根据参数，新建关注论坛组信息
	$group = new BbsGroupFavorite ( $request->getParsedBody () );
	$group->user_id = $userid;
	$group->group_id = $groupid;
	
	// 验证内容是否合法
	if ($group->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $group->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$group = $group->save ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '论坛关注正常登录。' 
	) );
	return $response;
} );

/**
 * 取消关注论坛组
 */
$app->delete ( '/favorite/{groupid}', function (Request $request, Response $response, $args) {
	
	// 开始事务
	ConnectionManager::get_connection ()->transaction ();
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得参数的论坛组id
	$groupid = $args ['groupid'];
	
	// 取得论坛组信息
	$group = BbsGroupFavorite::find ( 'first', array (
			'conditions' => array (
					' user_id = ? and group_id = ?',
					$userid,
					$groupid 
			) 
	) );
	
	// 未找到时，设定错误信息
	if ($group == null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '关注的论坛组信息不存在。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 删除关注的论坛组
	$group->delete ();
	
	// 结束事务
	ConnectionManager::get_connection ()->commit ();
	
	// 设定返回内容
	$response = $response->withJson ( array (
			'message' => '论坛组的关注正常取消。' 
	) );
	return $response;
} );

// 运行
$app->run ();
