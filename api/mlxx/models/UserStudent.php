<?php
class UserStudent extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_user_student';
	
	/**
	 * 项目定义
	 */
	// 用户id
	static $user_id;
	// 学生id
	static $student_id;
	
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
			// 用户信息
			array (
					'student',
					'class_name' => 'Student',
					'foreign_key' => 'student_id' 
			) 
	);
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'user_id',
			'student_id',
			'modify_user_id' 
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
					'student_id' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
