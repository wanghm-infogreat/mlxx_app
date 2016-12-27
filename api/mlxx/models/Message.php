<?php
class Message extends BaseModel {
	
	// 数据库表名
	static $table_name = 'sys_message';
	
	/**
	 * 项目定义
	 */
	// 发送用户id
	static $from_user_id;
	// 接收用户id
	static $to_user_id;
	// 发送时间
	static $send_time;
	// 阅读时间
	static $read_time;
	// 消息内容
	static $message;
	
	/**
	 * 关联关系
	 */
	static $belongs_to = array (
			// 用户信息
			array (
					'from',
					'class_name' => 'User',
					'foreign_key' => 'from_user_id' 
			),
			// 用户信息
			array (
					'to',
					'class_name' => 'User',
					'foreign_key' => 'to_user_id' 
			) 
	);
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'from_user_id',
			'to_user_id',
			'send_time',
			'read_time',
			'message',
			'modify_user_id' 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'from_user_id' 
			),
			array (
					'to_user_id' 
			),
			array (
					'send_time' 
			),
			array (
					'message' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
