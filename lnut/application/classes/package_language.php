<?php

class package_language extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function AddLearnableLanguages($package_uid=null) {
		if ($package_uid != null && isset($_POST['learnable_language_uid']) && count($_POST['learnable_language_uid'])) {
			foreach ($_POST['learnable_language_uid'] as $learnable_language_uid) {
				$this->AddLanguage($package_uid, $learnable_language_uid);
			}
		}
	}

	public function EditLearnableLanguages($package_uid=null) {
		if ($package_uid != null && isset($_POST['learnable_language_uid']) && count($_POST['learnable_language_uid'])) {
			$query = "DELETE ";
			$query.="FROM ";
			$query.="`package_language` ";
			$query.="WHERE ";
			$query.="`package_uid` = '" . $package_uid . "'";
			database::query($query);
			$objPackage = new package();
			$objPackage->updatePackagedates($package_uid);
			foreach ($_POST['learnable_language_uid'] as $learnable_language_uid) {
				$this->AddLanguage($package_uid, $learnable_language_uid);
			}
		}
	}

	private function AddLanguage($package_uid=null, $learnable_language_uid=null) {
		if ($package_uid != null && $learnable_language_uid != null) {
			$this->set_package_uid($package_uid);
			$this->set_learnable_language_uid($learnable_language_uid);
			$this->insert();
		}
	}

	public function getPackageLearnableLanguage($package_uid=null) {
		$arrLearnableLanguage = array();
		if ($package_uid != null) {
			$query = "SELECT ";
			$query.="`learnable_language_uid` ";
			$query.="FROM ";
			$query.="`package_language`";
			$query.="WHERE ";
			$query.="`package_uid` = '" . $package_uid . "'";
			$result = database::query($query);
			if (mysql_error() == '' && mysql_num_rows($result)) {
				while ($row = mysql_fetch_array($result)) {
					$arrLearnableLanguage[] = $row['learnable_language_uid'];
				}
			}
		}
		return $arrLearnableLanguage;
	}

}

?>