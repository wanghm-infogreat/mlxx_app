<?php
class Bank extends BaseModel {
	
	// 数据库表名
	static $table_name = 'mlxx_bank';
	
	/**
	 * 项目定义
	 */
	// 发布者代码
	static $class_code;
	// 标题日期
	static $title_date;
	// 标题
	static $title;
	// 标题时间
	static $title_time;
	// 内容
	static $content;
	// 答案
	static $answer;
	// 密码
	static $password;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'class_code',
			'title_date',
			'title',
			'title_time',
			'content',
			'answer',
			'password',
			'modify_user_id' 
	);
	
	/**
	 * 关联关系
	 */
	static $has_many = array (
			// 公告照片
			array (
					'photos',
					'class_name' => 'BankPhoto',
					'foreign_key' => 'bank_id',
					'order' => 'id asc' 
			),
			// 公告视频
			array (
					'videos',
					'class_name' => 'BankVideo',
					'foreign_key' => 'bank_id',
					'order' => 'id asc' 
			) 
	);
	
	/**
	 * 项目验证
	 */
	// 必须
	static $validates_presence_of = array (
			array (
					'class_code' 
			),
			array (
					'title_date' 
			),
			array (
					'title' 
			),
			array (
					'title_time' 
			),
			array (
					'content' 
			),
			array (
					'answer' 
			),
			array (
					'password' 
			),
			array (
					'modify_user_id' 
			) 
	);
}
?>
