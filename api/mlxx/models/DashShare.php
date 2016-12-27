<?php
class DashShare extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_dash_share';
	
	// 公告id
	static $dash_id;
	// 分享者id
	static $user_id;
	// 分享时间
	static $share_time;
	// 分享目标对象
	static $share_to_code;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'dash_id',
			'user_id',
			'share_time',
			'share_to_code',
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
					'share_time' 
			),
			array (
					'share_to_code' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
