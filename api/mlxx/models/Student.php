<?php
class Student extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_student';
	
	/**
	 * 项目定义
	 */
	// 学生姓名
	static $name;
	// 班级代码
	static $class_code;
	// 头像url
	static $photo_url;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'name',
			'class_code',
			'photo_url',
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
					'modify_user_id' 
			) 
	);
}
?>
