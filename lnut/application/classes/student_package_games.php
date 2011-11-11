<?php

class student_package_games extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function saveGames() {
		if (isset($_POST['submit']) && isset($_POST['game']) && is_array($_POST['game']) && isset($_POST['package_uid'])) {

			$query ="DELETE ";
			$query.=" FROM ";
			$query.=" `student_package_games` ";
			$query.=" WHERE ";
			$query.=" `package_uid` = '" . $_POST['package_uid'] . "' ";
			database::query($query);
			$objPackage = new student_package($_POST['package_uid']);
			$objPackage->load();
			$support_language_uid = $objPackage->get_support_language_uid();
			foreach ($_POST['game'] as $index => $value) {
				$language_uid = 0;
				$section_uid = 0;
				$game_uid = 0;
				list(
						$language_uid,
						$section_uid,
						$game_uid
						) = explode('_', $index);
				$this->set_package_uid($_POST['package_uid']);
				$this->set_support_language_uid($support_language_uid);
				$this->set_learnable_language_uid($language_uid);
				$this->set_section_uid($section_uid);
				$this->set_game_uid($game_uid);
				$this->insert();
			}
		}
	}

	public function checkExist($package_uid=null, $language_uid=null, $section_uid=null, $game_uid=null) {
		if ($package_uid != null && $language_uid != null && $section_uid != null && $game_uid != null) {
			$query = "SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`student_package_games` ";
			$query.="WHERE ";
			$query.="`package_uid` = '" . $package_uid . "' ";
			$query.="AND ";
			$query.="`learnable_language_uid` = '" . $language_uid . "' ";
			$query.="AND ";
			$query.="`game_uid` = '" . $game_uid . "'";
			$query.="AND ";
			$query.="`section_uid` = '" . $section_uid . "'";
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