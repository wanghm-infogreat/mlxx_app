<?php
use ActiveRecord\DateTime;
class BaseModel extends ActiveRecord\Model {
	
	/**
	 * 项目定义
	 */
	// create user id
	static $create_user_id;
	
	// create datetime
	static $create_datetime;
	
	// modify user id
	static $modify_user_id;
	
	// modify datetime
	static $modify_datetime;
	
	/**
	 * 新建前，设定创建者和更新者信息
	 */
	static $before_create = array (
			'when_create' 
	);
	public function when_create() {
		$this->create_user_id = $this->modify_user_id;
	}
	
	/**
	 * 更新前，设定更新者信息
	 */
	static $before_save = array (
			'when_update' 
	);
	public function when_update() {
		$this->modify_datetime = new DateTime();
	}
}
?>
