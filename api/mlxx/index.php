<?php
require_once __DIR__ . '/../vendor/initlize.php';

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;

/**
 * 用户登录
 */
$app->post ( '/login', function (Request $request, Response $response, $args) {
	
	// 取得参数用户名
	$username = '';
	if (isset ( $request->getParsedBody () ['user'] )) {
		$username = $request->getParsedBody () ['user'];
	}
	// 取得密码
	$password = '';
	if (isset ( $request->getParsedBody () ['pass'] )) {
		$password = md5 ( $request->getParsedBody () ['pass'] );
	}
	
	// 取得账号信息
	$account = Account::find ( 'first', array (
			'conditions' => array (
					'user = ? and pass = ?',
					$username,
					$password 
			),
			'include' => array (
					'userinfo' 
			) 
	) );
	
	// 如果账号信息不正确
	if ($account == null) {
		// 设定错误信息，返回
		$response = $response->withJson ( array (
				'message' => '用户名或密码不正确。' 
		), DATA_ERROR );
		return $response;
	}
	
	// 根据账号信息，生成token信息
	$key = "example_key";
	// header
	$header = array (
			"typ" => "JWT",
			"alg" => "HS256" 
	);
	// playlod
	$token = array (
			"iss" => "http://cn.com.varsudio/mlxx",
			"aud" => "http://cn.com.varsudio/mlxx",
			"iat" => 1356999524,
			"nbf" => 1357000000,
			"uid" => $account->id,
			"role" => $account->role 
	);
	// encode
	$jwt = JWT::encode ( $token, $key, 'HS256', null, $header );
	
	// 返回用户id和token
	$result = array (
			'name' => $account->user,
			'user' => json_decode ( $account->userinfo->to_json () ),
			'token' => $jwt 
	);
	
	// 设定返回值
	$response = $response->withJson ( $result );
	return $response;
} );

// 运行
$app->run ();
?>
