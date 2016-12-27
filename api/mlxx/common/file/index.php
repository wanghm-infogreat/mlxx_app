<?php
require_once __DIR__ . '/../../../vendor/initlize.php';

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 文件上传
 */
$app->post ( '/upload', function (Request $request, Response $response, $args) {
	
	// 取得当前的用户id
	$token = $request->getAttribute ( 'token' );
	$userid = $token->uid;
	
	// 返回结果【文件url】
	$result = null;
	
	// 取得所有上传的文件
	$files = $request->getUploadedFiles ();
	
	// 循环处理所有文件
	foreach ( $files as $file ) {
		
		if ($file->getError ()) {
			$response = $response->withJson ( array (
					'message' => '文件上传失败：code=' + $file->getError () 
			), DATA_ERROR );
			return $response;
		}
		
		$ext = pathinfo ( $file->getClientFilename (), PATHINFO_EXTENSION );
		$uploadFileName = $userid . "_" . date ( 'YmdHis' ) . "_" . md5 ( rand () ) . "." . $ext;
		$file->moveTo ( UPLOAD_FILE_PATH . $uploadFileName );
		
		// 保存文件名
		$result = array (
				"url" => UPLOAD_FILE_URL . $uploadFileName 
		);
	}
	
	// 登录数据库
	$uploadfile = new UploadFile ();
	// 用户id
	$uploadfile->user_id = $userid;
	// 上传时间
	$uploadfile->upload_datetime = new DateTime ();
	// 文件大小
	$uploadfile->size = $file->getSize ();
	// 文件名
	$uploadfile->client_filename = $file->getClientFilename ();
	// 文件类型
	$uploadfile->client_mediatype = $file->getClientMediaType ();
	// 文件路径
	$uploadfile->path = UPLOAD_FILE_PATH . $uploadFileName;
	// 文件url地址
	$uploadfile->url = UPLOAD_FILE_URL . $uploadFileName;
	
	// 保存到数据库
	$uploadfile->save ();
	
	// 设定返回值
	$response = $response->withJson ( $result );
	return $response;
} );

// 运行
$app->run ();
?>
