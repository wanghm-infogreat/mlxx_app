<?php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/../config/include.php';
require_once __DIR__ . '/../utils/include.php';

// 设定默认时间域
// date_default_timezone_set ( 'Asia/Brunei' );
date_default_timezone_set ( 'Europe/London' );

// 初始化数据库链接
ActiveRecord\Config::initialize ( function ($cfg) {
	// 数据模型路径
	$cfg->set_model_directory ( __DIR__ . '/../mlxx/models' );
	
	// 数据库链接
	$cfg->set_connections ( array (
			'development' => 'mysql://mlxx:mlxx@localhost/mlxx;charset=utf8mb4' 
	) );
} );

// 初始化
$configuration = [
		// 设置
		'settings' => [
				// 显示详细错误信息
				'displayErrorDetails' => true,
				// 启用Acl控制
				'determineRouteBeforeAppMiddleware' => true,
				// 日志输出
				'logger' => [ 
						'name' => 'slim-app',
						'level' => Psr\Log\LogLevel::DEBUG,
						'path' => __DIR__ . '/var/log/apache2/slim.log' 
				] 
		] 
];

// 设定默认设置
$config = new \Slim\Container ( $configuration );

// 初始化slim
$app = new \Slim\App ( $config );

// 增加Permission Acl组件
$app->add ( new \SlimApi\Acl\Guard ( [
		
		// define the basepath (default : '/')
		'basepath' => '/dev/mlxx',
		
		// define the default role
		'default_role' => 'guest',
		
		// define all roles
		'roles' => [ 
				'guest' => null,
				'student' => [ 'guest' ],
				'parent' => [ 'student' ],
				'teacher' => [ 'guest'],
				'admin' => [ 'guest' ] 
		],
		
		// set resources
		'resources' => [ 
				'/' => null,
				'/dash/' => '/',
				'/dash/comment/' => '/dash/',
				'/dash/like/' => '/dash/',
				'/dash/share/' => '/dash/',
				'/bbs/group/' => '/',
				'/bbs/title/' => '/bbs/group/',
				'/bbs/comment/' => '/bbs/title/',
				'/bank/' => '/',
				'/account/' => '/',
				'/user/' => '/',
				'/student/' => '/',
				'/student/evaluation/' => '/student/',
				'/common/file/' => '/',
				'/common/message/' => '/',
				'/common/friend/' => '/' 
		],
		
		// set allow rules
		'allows' => [ 
				'/' 					=> [ [ 'guest' ] ],
				'/dash/' 				=> [ [ 'guest' ],[ 'get' ] ],
				'/dash/' 				=> [ [ 'admin' ] ],
				'/dash/comment/' 		=> [ [ 'guest' ] ],
				'/dash/like/' 			=> [ [ 'guest' ] ],
				'/dash/share/' 			=> [ [ 'guest' ] ],
				'/bbs/group/' 			=> [ [ 'guest' ],[ 'get' ] ],
				'/bbs/group/' 			=> [ [ 'admin' ] ],
				'/bbs/title/' 			=> [ [ 'guest' ],[ 'get','put' ] ],
				'/bbs/title/' 			=> [ [ 'admin' ] ],
				'/bbs/comment/' 		=> [ [ 'guest' ] ],
				'/bank/' 				=> [ [ 'guest' ],[ 'get' ] ],
				'/bank/' 				=> [ [ 'admin' ] ],
				'/account/' 			=> [ [ 'guest' ],[ 'put','post' ] ],
				'/user/' 				=> [ [ 'guest' ],[ 'get','put','post' ] ],
				'/user/' 				=> [ [ 'admin' ] ],
				'/student/' 			=> [ [ 'admin' ] ],
				'/student/evaluation/' 	=> [ [ 'guest' ],[ 'get' ] ],
				'/student/evaluation/' 	=> [ [ 'admin','teacher' ] ],
				'/common/file/' 		=> [ [ 'guest' ] ],
				'/common/message/' 		=> [ [ 'guest' ] ],
				'/common/friend/' 		=> [ [ 'guest' ] ] 
		],
		
		// set deny rules
		'denies' => [ ] 
] ) );

// 增加jwt组件
$app->add ( new \Slim\Middleware\JwtAuthentication ( [ 
		"secure" => false,
		"path" => [ 
				"/" 
		],
		"passthrough" => [ 
				"/test",
				"/login",
				"/logout" 
		],
		"secret" => "example_key",
		"regexp" => "/(.*)/",
		"error" => function ($request, $response, $arguments) {
			$data ["status"] = "error";
			$data ["message"] = $arguments ["message"];
			return $response->withJson ( $data );
		} 
] ) );

?>
