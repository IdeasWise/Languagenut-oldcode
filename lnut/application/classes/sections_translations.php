<?php

class sections_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
			}
			return true;
		} else {
			return false;
		}
	}

	public function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$name = (isset($_POST['name'])) ? $_POST['name'] : '';
		$section_uid = (isset($_POST['section_uid'])) ? $_POST['section_uid'] : '0';
		$language_id = (isset($_POST['language_id'])) ? $_POST['language_id'] : '0';
		$active = (isset($_POST['active'])) ? $_POST['active'] : 0;
		$arrMessages = array();
		if (trim(strlen($name)) < 3 || trim(strlen($name)) > 255) {
			$arrMessages['error_name'] = "Section translation must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid section name.";
		}
		if (trim($section_uid) == '' || trim($section_uid) == "0") {
			$arrMessages['error_section_uid'] = "Please choose section.";
		} else if (!validation::isValid('int', $section_uid)) {
			$arrMessages['error_section_uid'] = "Please choose valid section.";
		}
		if (trim($language_id) == '' || trim($language_id) == "0") {
			$arrMessages['error_language_id'] = "Please choose language.";
		} else if (!validation::isValid('int', $language_id)) {
			$arrMessages['error_language_id'] = "Please choose valid language.";
		}
		if (!validation::isValid('int', $active)) {
			$arrMessages['error_active'] = "Please choose valid section active option.";
		}
		if (count($arrMessages) == 0) {
			$this->set_name($name);
			$this->set_section_uid($section_uid);
			$this->set_language_id($language_id);
			$this->set_active($active);
		} else {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}
		foreach ($_POST as $index => $value) {
			$this->arrForm[$index] = $value;
		}
		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getSectionTranslationName($language_support_id=null, $section_id=null) {
		$name = '';
		if (is_numeric($language_support_id) && is_numeric($section_id) && $section_id > 0 && $language_support_id > 0) {
			$query = "SELECT ";
			$query.="IF(`st`.`name`!='',`st`.`name`, (SELECT `subst`.`name` FROM `sections_translations` AS `subst` WHERE `subst`.`language_id`=14 AND `subst`.`section_uid`=`st`.`section_uid`) ) AS `section_name` ";
			$query.="FROM ";
			$query.="`sections_translations` AS `st` ";
			$query.="WHERE ";
			$query.="`st`.`language_id` = '" . $language_support_id . "' ";
			$query.="AND ";
			$query.="`st`.`section_uid` = '" . $section_id . "' ";
			$query.="LIMIT 1 ";
			$result = database::query($query);
			if ($result && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$name = stripslashes(str_replace('\\','',$row['section_name']));
			}
		}
		return $name;
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();

		$arrValues[] = array(
			"field" => "language_id",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND language_id='" . $enUid . "'" : "";
		$groupBy = " GROUP BY section_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>