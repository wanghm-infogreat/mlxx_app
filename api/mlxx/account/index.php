<?php
require_once __DIR__ . '/../../vendor/initlize.php';

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 用户注册
 */
$app->put ( '/register', function (Request $request, Response $response, $args) {
	
	// 取得参数用户名
	$user = '';
	if (isset ( $request->getParsedBody () ['user'] )) {
		$user = $request->getParsedBody () ['user'];
	}
	
	// 取得用户信息
	$user = Account::find ( 'first', array (
			'conditions' => array (
					'user = ?',
					$user 
			) 
	) );
	
	// 如果用户已经存在
	if ($user != null) {
		// 设定错误信息，返回
		$response = $response->withJson ( '该用户名已经存在。', DATA_ERROR );
		return $response;
	}
	
	// 根据参数，新建账号信息
	$account = new Account ( $request->getParsedBody (), true );
	
	// 加密密码
	$account->pass = md5 ( $account->pass );
	
	// 验证内容是否合法
	if ($account->is_invalid ()) {
		// 设定错误信息，返回
		$response = $response->withJson ( $account->errors->to_array (), VALIDATION_ERROR );
		return $response;
	}
	
	// 保存到数据库
	$account = $account->save ();
	
	// 设定返回值
	$response = $response->withJson ( "账号注册成功。" );
	return $response;
} );

/**
 * 用户修改密码
 */
$app->post ( '/password', function (Request $request, Response $response, $args) {
	
	// 取得当前登录的用户
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 取得用户旧密码
	$old = '';
	if (isset ( $request->getParsedBody () ['oldpass'] )) {
		$old = $request->getParsedBody () ['oldpass'];
	}
	// 取得用户新密码
	$new = '';
	if (isset ( $request->getParsedBody () ['newpass'] )) {
		$new = $request->getParsedBody () ['newpass'];
	}
	// 取得用户确认密码
	$confirm = '';
	if (isset ( $request->getParsedBody () ['confirmpass'] )) {
		$confirm = $request->getParsedBody () ['confirmpass'];
	}
	
	// 验证新密码是否为空白
	if (empty ( $new )) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '请输入新密码。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 验证新密码和确认密码是否一致
	if ($new != $confirm) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '输入新密码和确认密码不一致。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 验证旧密码是否有效
	// 取得账号信息
	$account = Account::find ( 'first', array (
			'conditions' => array (
					'id = ? and pass = ?',
					$userid,
					md5 ( $old ) 
			),
			'include' => array (
					'userinfo' 
			) 
	) );
	
	// 如果账号信息不正确
	if ($account == null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '密码不正确。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 更新用户的新密码
	$account->pass = md5 ( $new );
	$account->save ();
	
	// 设定返回值
	$response = $response->withJson ( array (
			'message' => '密码更新成功。' 
	) );
	return $response;
} );

// 运行
$app->run ();
?>
