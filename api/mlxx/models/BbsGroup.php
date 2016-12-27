<?php
class BbsGroup extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bbs_group';
	
	/**
	 * 项目定义
	 */
	// 组名
	static $name;
	// 发布者代码
	static $class_code;
	// 标题数
	static $titles;
	// 固定标志（1:固定，2:非固定）
	static $fix_flag;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'name',
			'class_code',
			'titles',
			'fix_flag',
			'modify_user_id' 
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
					'class_code' 
			),
			array (
					'titles' 
			),
			array (
					'fix_flag' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
