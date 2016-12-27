<?php
class BankPhoto extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bank_photo';
	
	/**
	 * 项目定义
	 */
	// 题库id
	static $bank_id;
	// 照片url
	static $url;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'bank_id',
			'url',
			'modify_user_id' 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'bank_id' 
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
