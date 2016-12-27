<?php
class BbsGroupFavorite extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bbs_group_favorite';
	
	/**
	 * 项目定义
	 */
	// 用户id
	static $user_id;
	// bbs组id
	static $group_id;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'user_id',
			'group_id',
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
			// 论坛组信息
			array (
					'group',
					'class_name' => 'BbsGroup',
					'foreign_key' => 'group_id' 
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
					'group_id' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
