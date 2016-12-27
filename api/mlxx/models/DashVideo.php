<?php
class DashVideo extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_dash_video';
	
	/**
	 * 项目定义
	 */
	// 公告id
	static $dash_id;
	// 视频url
	static $url;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'dash_id',
			'url',
			'modify_user_id' 
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
					'url' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
