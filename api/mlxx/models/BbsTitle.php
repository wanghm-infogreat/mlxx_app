<?php
class BbsTitle extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bbs_title';
	
	/**
	 * 项目定义
	 */
	// 组名
	static $name;
	// 所属组id
	static $group_id;
	// 用户id
	static $user_id;
	// 话题数
	static $comments;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'name',
			'group_id',
			'user_id',
			'comments',
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
					'name' 
			),
			array (
					'group_id' 
			),
			array (
					'user_id' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
