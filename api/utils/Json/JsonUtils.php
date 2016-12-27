<?php
/**
 * ActiveRecord返回的结果集数组，转换成json用的回调函数
 * 
 * @param unknown $v
 * @return mixed
 */
function array_to_json($v)
{
	return json_decode($v->to_json());
}
?>