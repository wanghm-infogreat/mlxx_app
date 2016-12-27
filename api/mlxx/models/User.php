<?php
class User extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_user';
	
	/**
	 * 项目定义
	 */
	// 用户姓名
	static $name;
	// 头像照片
	static $photo_url;
	// 称呼
	static $call;
	// 推送标志
	static $push_flag;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'name',
			'photo_url',
			'call',
			'push_flag',
			'modify_user_id' 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'name' 
			),
			array (
					'push_flag' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
