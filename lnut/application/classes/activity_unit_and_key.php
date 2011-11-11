<?php

class activity_unit_and_key extends generic_object {
	public $arrForm = array();
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}
	public function SaveActivityUnitAndKey() {
		$activity_uid	= (isset($_POST['activity_uid']) && is_numeric($_POST['activity_uid']))?$_POST['activity_uid']:'';
		$unit_uid		= (isset($_POST['unit_uid']) && is_numeric($_POST['unit_uid']))?$_POST['unit_uid']:'';
		$key			= (isset($_POST['key']))?$_POST['key']:'';
		$material_type_uid = (isset($_POST['material_type_uid']) && is_numeric($_POST['material_type_uid']))?$_POST['material_type_uid']:'';
		$arrError = array();
		if($activity_uid=='') {
			$arrError[] = '<li>Please provide activity uid.</li>';
		}
		if($unit_uid=='') {
			$arrError[] = '<li>Please choose any unit from following list.</li>';
		}
		if(strlen($key) > 256) {
			$arrError[] = '<li>Key must be up to 256 characters in length.</li>';
		}
		if(count($arrError) === 0) {
			$this->doSave($activity_uid,$unit_uid,$key,$material_type_uid);
			return true;
		} else {
			// intilize error
			$this->arrForm['activity_uid']	= $activity_uid;
			$this->arrForm['unit_uid']		= $unit_uid;
			$this->arrForm['key']			= $key;
			$this->arrForm['message_error']	= '<p>Please correct the errors below:</p><ul>'.implode($arrError).'</ul>';
			return false;
		}
	}

	private function doSave($activity_uid=null,$unit_uid=null,$key=null,$material_type_uid=null) {
		if($activity_uid!=null && $unit_uid!=null && $key!=null && $material_type_uid!=null) {
			$query ="UPDATE ";
			$query.="`activity_unit_and_key` ";
			$query.="SET ";
			$query.="`archived` = '".time()."' ";
			$query.="WHERE ";
			$query.="`material_type_uid` = '".mysql_real_escape_string($material_type_uid)."' ";
			$query.="AND ";
			$query.="`archived` = '0' ";
			$query.="AND ";
			$query.="`activity_uid` = '".mysql_real_escape_string($activity_uid)."' ";
			database::query($query);
			$this->set_activity_uid($activity_uid);
			$this->set_unit_uid($unit_uid);
			$this->set_key($key);
			$this->set_material_type_uid($material_type_uid);
			$this->set_archived(0);
			$this->insert();
		}
	}

	public function getActivityUnitAndKey($activity_uid=null,$material_type_uid=null) {
		$arrResult = array();
		$query = "SELECT ";
		$query.="* ";
		$query.="FROM ";
		$query.="`activity_unit_and_key` ";
		$query.="WHERE ";
		$query.="`material_type_uid` = '".mysql_real_escape_string($material_type_uid)."' ";
		$query.="AND ";
		$query.="`archived` = '0' ";
		$query.="AND ";
		$query.="`activity_uid` = '".mysql_real_escape_string($activity_uid)."' ";
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			$arrResult = mysql_fetch_array($result);
		}
		return $arrResult;
	}
}

?>