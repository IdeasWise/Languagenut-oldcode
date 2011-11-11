<?php

class activity_reference_materials extends generic_object {

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function isValidCreateUpdate($arrData=array()) {
		
	}

	public function isValidArticleUpdate($arrData=array()) {
		$activity_uid = (isset($arrData['activity_uid']) && is_numeric($arrData['activity_uid']) && $arrData['activity_uid'] > 0) ? $arrData['activity_uid'] : '';
		$article_uid = (isset($arrData['article_uid']) && is_numeric($arrData['article_uid']) && $arrData['article_uid'] > 0) ? $arrData['article_uid'] : '';
		if ($activity_uid != '' && $article_uid != '') {
			if (($material_uid = self::getMaterialUid()) > 0) {
				parent::__construct($material_uid, __CLASS__);
				$this->load();
				$this->set_activity_uid($activity_uid);
				$this->set_material_uid($article_uid);
				$this->save();
			} else {
				$this->set_activity_uid($activity_uid);
				$this->set_material_uid($article_uid);
				$this->insert();
			}
			$objActivity = new activity($activity_uid);
			$objActivity->load();
			$objActivity->set_material_uid($article_uid);
			$objActivity->save();
			return true;
		} else {
			$arrData['message'] = 'Please complete all fields';
		}
		return $arrData;
	}

	public static function getMaterialUid($activity_uid = 0) {
		$uid = 0;
		$query = "SELECT `uid` FROM `activity_reference_materials` WHERE `activity_uid` = '$activity_uid' LIMIT 1";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($res);
			$uid = $row['uid'];
		}
		return $uid;
	}

	public static function getReferenceMaterailsFromActivity($activity_uid = 0) {
		$rows = array();
		$query = "SELECT * FROM `activity_reference_materials` WHERE `activity_uid` = ''";
		return $rows;
	}

}

?>
