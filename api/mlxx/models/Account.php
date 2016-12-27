<?php
class Account extends ActiveRecord\Model {
	
	// 数据库表名
	static $table_name = 'sys_account';
	
	/**
	 * 项目定义
	 */
	// 用户名
	static $user;
	// 密码
	static $pass;
	// 角色
	static $role;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'user',
			'pass',
			'role' 
	);
	
	/**
	 * 关联关系
	 */
	static $belongs_to = array (
			// 用户信息
			array (
					'userinfo',
					'class_name' => 'User',
					'foreign_key' => 'id' 
			) 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'user' 
			),
			array (
					'pass' 
			),
			array (
					'role' 
			) 
	);
}
?>
