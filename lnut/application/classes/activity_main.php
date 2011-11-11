<?php

class activity_main extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getOneActivity($activity_main_uid){
		$sql="select uid from `activity` WHERE activity_main_uid = '{$activity_main_uid}' LIMIT 1 ";
		$activity=database::arrQuery($sql);
		return (isset($activity[0]['uid']))?$activity[0]['uid']:0;
	}

	public function getActivities($activity_main_uid){
		$sql="select * from `activity` WHERE activity_main_uid = '{$activity_main_uid}' ";
		$activity=database::arrQuery($sql);
		return (count($activity)>0)?$activity[0]['uid']:0;
	}
}

?>