<?php
class BbsTitleFavorite extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bbs_title_favorite';
	
	/**
	 * 项目定义
	 */
	// 用户id
	static $user_id;
	// bbs话题id
	static $title_id;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'user_id',
			'title_id',
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
			),
			// bbs组信息
			array (
					'title',
					'class_name' => 'BbsTitle',
					'foreign_key' => 'title_id' 
			) 
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
					'title_id' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
