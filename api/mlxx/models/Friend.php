<?php
class Friend extends BaseModel {
	
	// 数据库表名
	static $table_name = 'sys_friend';
	
	/**
	 * 项目定义
	 */
	// 用户id
	static $user_id;
	// 好友用户id
	static $friend_user_id;
	
	/**
	 * 关联关系
	 */
	static $belongs_to = array (
			// 用户信息
			array (
					'friend',
					'class_name' => 'User',
					'foreign_key' => 'friend_user_id' 
			) 
	);
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'user_id',
			'friend_user_id',
			'modify_user_id' 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'user_id' 
			),
			array (
					'friend_user_id' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
