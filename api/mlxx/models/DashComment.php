<?php
class DashComment extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_dash_comment';
	
	/**
	 * 项目定义
	 */
	// 公告id
	static $dash_id;
	// 发布者id
	static $user_id;
	// 发布时间
	static $comment_time;
	// 评论内容
	static $content;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'dash_id',
			'user_id',
			'comment_time',
			'content',
			'modify_user_id' 
	);
	
	/**
	 * 关联关系
	 */
	static $belongs_to = array (
			// 用户信息
			array (
					'user',
					'class_name' => 'User',
					'foreign_key' => 'user_id' 
			) 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'dash_id' 
			),
			array (
					'user_id' 
			),
			array (
					'comment_time' 
			),
			array (
					'content' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
