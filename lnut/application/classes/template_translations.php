<?php

class template_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function isValidInput() {
		$arrName = array();
		$arrWidth = array();
		$arrHeight = array();
		if (isset($_POST['tname']) && is_array($_POST['tname'])) {
			foreach ($_POST['tname'] as $index => $value) {
				if (trim($value) != '' && strlen(trim($value)) > 255) {
					$arrName[] = '<i><b>' . $_POST['locale'][$index] . '</b></i>';
				}
				$width = (isset($_POST['twidth'][$index]) && trim($_POST['twidth'][$index]) != '') ? $_POST['twidth'][$index] : 0;
				$height = (isset($_POST['theight'][$index]) && trim($_POST['theight'][$index]) != '') ? $_POST['theight'][$index] : 0;
				if (trim($width) != '' && (strlen(trim($width)) > 5 || !is_numeric($width))) {
					$arrWidth[] = '<i><b>' . $_POST['locale'][$index] . '</b></i>';
				}
				if (trim($height) != '' && (strlen(trim($height)) > 5 || !is_numeric($height))) {
					$arrHeight[] = '<i><b>' . $_POST['locale'][$index] . '</b></i>';
				}
			}
		}
		return array(
			'arrName' => $arrName,
			'arrWidth' => $arrWidth,
			'arrHeight' => $arrHeight
		);
	}

	public function SaveTemplateTranslation($template_uid=null) {
		if ($template_uid != null && isset($_POST['tname']) && is_array($_POST['tname'])) {
			foreach ($_POST['tname'] as $index => $value) {
				$width = (isset($_POST['twidth'][$index]) && is_numeric($_POST['twidth'][$index])) ? $_POST['twidth'][$index] : 0;

				$height = (isset($_POST['theight'][$index]) && is_numeric($_POST['theight'][$index])) ? $_POST['theight'][$index] : 0;
				$query = "SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`template_translation` ";
				$query.="WHERE ";
				$query.="`template_uid` = '" . mysql_real_escape_string($template_uid) . "' ";
				$query.="AND ";
				$query.="`language_uid`='" . mysql_real_escape_string($index) . "' ";
				$query.="LIMIT 1";
				$result = database::query($query);
				if (mysql_error() == '' && mysql_num_rows($result)) {
					$row = mysql_fetch_array($result);
					parent::__construct($row['uid'], __CLASS__);
					$this->load();
					$this->set_name($value);
					$this->set_width($width);
					$this->set_height($height);
					$this->save();
				} else {
					$this->set_name($value);
					$this->set_width($width);
					$this->set_height($height);
					$this->set_template_uid($template_uid);
					$this->set_language_uid($index);
					$this->insert();
				}
			}
		}
	}

	public function DeleteTemplateTranslation($template_uid=null) {
		if (is_numeric($template_uid) && $template_uid > 0) {
			$query = "DELETE ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_uid` = '" . mysql_real_escape_string($template_uid) . "' ";
			database::query($query);
		}
	}

}

?>