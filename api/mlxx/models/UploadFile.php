<?php
class UploadFile extends ActiveRecord\Model {
	
	// 数据库表名
	static $table_name = 'sys_upload_file';
	
	/**
	 * 项目定义
	 */
	// 用户id
	static $user_id;
	// 上传时间
	static $upload_datetime;
	// 文件大小
	static $size;
	// 文件名
	static $client_filename;
	// 文件类型
	static $client_mediatype;
	// 文件路径
	static $path;
	// 文件url地址
	static $url;
	
	// 用户可提交的项目定义
	static $attr_accessible = array (
			'user_id',
			'upload_datetime',
			'size',
			'client_filename',
			'client_mediatype',
			'path',
			'url' 
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
					'user_id' 
			),
			array (
					'upload_datetime' 
			),
			array (
					'size' 
			),
			array (
					'client_filename' 
			),
			array (
					'client_mediatype' 
			),
			array (
					'path' 
			),
			array (
					'url' 
			) 
	);
}
?>
