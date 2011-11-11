<?php

class flash_tips extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList() {
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`flash_tips` ";
		return database::arrQuery($query);
	}

}

?>