<?php

class package_sections extends generic_object {

	public $arrForm = array( );
	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function saveSections() {
		if(isset($_POST['submit'])) {

			$query ="DELETE ";
			$query.="FROM ";
			$query.="`package_sections` ";
			$query.="WHERE ";
			$query.="`package_uid` = '".$_POST['package_uid']."' ";
			database::query($query);
			$objPackage = new package($_POST['package_uid']);
			$objPackage->load();
			$objPackage->updatePackagedates($_POST['package_uid']);
			$support_language_uid = $objPackage->get_support_language_uid();
			foreach($_POST['section'] as $index => $value) {
				$language_uid	= 0;
				$year_uid		= 0;
				$unit_uid		= 0;
				$section_uid	= 0;
				list(
					$language_uid,
					$year_uid,
					$unit_uid,
					$section_uid
				) = explode('_',$index);

				$this->set_package_uid($_POST['package_uid']);
				$this->set_support_language_uid($support_language_uid);
				$this->set_learnable_language_uid($language_uid);
				$this->set_year_uid($year_uid);
				$this->set_unit_uid($unit_uid);
				$this->set_section_uid($section_uid);
				$this->insert();

			}
		}
	}
	public function checkExist($package_uid=null,$language_uid=null,$section_uid=null) {
		if($package_uid!=null && $language_uid!=null && $section_uid!=null) {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`package_sections` ";
			$query.="WHERE ";
			$query.="`package_uid` = '".$package_uid."' ";
			$query.="AND ";
			$query.="`learnable_language_uid` = '".$language_uid."' ";
			$query.="AND ";
			$query.="`section_uid` = '".$section_uid."'";
			$query.="LIMIT 1";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				return ' checked="checked" ';
			}
		}
		return ' ';
	}
}
?>