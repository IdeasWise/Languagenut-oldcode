<?php

class class_package_activity extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function saveActivity() {

		if (isset($_POST['submit']) && isset($_POST['package_uid'])) {

			$query = "DELETE ";
			$query.="FROM ";
			$query.="`class_package_activity` ";
			$query.="WHERE ";
			$query.="`package_uid` = '" . $_POST['package_uid'] . "' ";
			database::query($query);
			$objPackage = new class_package($_POST['package_uid']);
			$objPackage->load();
			$support_language_uid = $objPackage->get_support_language_uid();
			if (isset($_POST['activity']) && is_array($_POST['activity'])) {
				$support_language_uid = $objPackage->get_support_language_uid();
				foreach ($_POST['activity'] as $index => $value) {
					$language_uid = 0;
					$skill_type_uid = 0;
					$unit_uid = 0;
					$activity_uid = 0;
					list(
							$unit_uid,
							$skill_type_uid,
							$activity_uid,
							$language_uid
							) = explode('_', $index);
					$this->set_package_uid($_POST['package_uid']);
					$this->set_skill_type_uid($skill_type_uid);
					$this->set_unit_uid($unit_uid);
					$this->set_activity_uid($activity_uid);
					$this->set_support_language_uid($support_language_uid);
					$this->set_learnable_language_uid($language_uid);
					$this->insert();
				}
			}
		}
	}

	public function checkExist($package_uid=null, $language_uid=null, $activity_uid=null) {
		if ($package_uid != null && $language_uid != null && $activity_uid != null) {
			$query = "SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`class_package_activity` ";
			$query.="WHERE ";
			$query.="`package_uid` = '{$package_uid}' ";
			$query.="AND ";
			$query.="`learnable_language_uid` = '" . $language_uid . "' ";
			$query.="AND ";
			$query.="`activity_uid` = '" . $activity_uid . "'";

			$query.="LIMIT 1";
			$result = database::query($query);
			if (mysql_error() == '' && mysql_num_rows($result)) {
				return ' checked="checked" ';
			}
		}
		return ' ';
	}

}

?>