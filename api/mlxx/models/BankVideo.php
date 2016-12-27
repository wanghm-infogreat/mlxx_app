<?php
class BankVideo extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bank_video';
	
	/**
	 * 项目定义
	 */
	// 题库id
	static $bank_id;
	// 视频url
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
