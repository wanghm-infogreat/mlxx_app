<?php
class BbsVideo extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bbs_video';
	
	/**
	 * 项目定义
	 */
	// 论坛组id
	static $group_id;
	// 论坛话题
	static $title_id;
	// 论坛评论id
	static $comment_id;
	// 照片url
	static $url;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'group_id',
			'title_id',
			'comment_id',
			'url',
			'modify_user_id' 
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
					'comment_id' 
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
