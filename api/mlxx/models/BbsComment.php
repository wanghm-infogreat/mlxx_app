<?php
class BbsComment extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bbs_comment';
	
	/**
	 * 项目定义
	 */
	// 论坛组id
	static $group_id;
	// 论坛话题id
	static $title_id;
	// 用户id
	static $user_id;
	// 发表时间
	static $comment_time;
	// 内容
	static $content;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'group_id',
			'title_id',
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
	static $has_many = array (
			// 公告照片
			array (
					'photos',
					'class_name' => 'DashPhoto',
					'foreign_key' => 'dash_id',
					'order' => 'id asc' 
			),
			// 公告视频
			array (
					'videos',
					'class_name' => 'DashVideo',
					'foreign_key' => 'dash_id',
					'order' => 'id asc' 
			) 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'group_id' 
			),
			array (
					'title_id' 
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
