<?php

class sections_vocabulary_translations extends generic_object {

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
		$term_uid = (isset($_POST['term_uid'])) ? $_POST['term_uid'] : '0';
		$language_id = (isset($_POST['language_id'])) ? $_POST['language_id'] : '0';
		$active = (isset($_POST['active'])) ? $_POST['active'] : 0;
		$arrMessages = array();
		if (trim(strlen($name)) < 3 || trim(strlen($name)) > 255) {
			$arrMessages['error_name'] = "Vocabulary translation must be 3 to 255 characters in length.";
		} else if (!validation::isValid('text', $name)) {
			$arrMessages['error_name'] = "Please enter valid vocabulary translation.";
		}
		if (trim($term_uid) == '' || trim($term_uid) == "0") {
			$arrMessages['error_term_uid'] = "Please choose term.";
		} else if (!validation::isValid('int', $term_uid)) {
			$arrMessages['error_term_uid'] = "Please choose valid term.";
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
			$this->set_term_uid($term_uid);
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

	public function getVocabTransArray($term_ids=array(), $language_id=null) {
		$arrTerms = array();
		if (is_array($term_ids) && count($term_ids) && is_numeric($language_id) && $language_id > 0) {
			$query = "SELECT ";
			$query.= "`uid`, ";
			$query.= "`name`, ";
			$query.= "`term_uid` ";
			$query.= "FROM ";
			$query.= "`sections_vocabulary_translations` ";
			$query.= "WHERE ";
			$query.= "`term_uid` IN (" . implode(',', $term_ids) . ") ";
			$query.= "AND `language_id`='" . $language_id . "'";
			$result = database::query($query);
			if ($result) {
				if (mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_assoc($result)) {
						$arrTerms[$row['term_uid']]['term'] = stripslashes(str_replace('\\','',$row['name']));
					}
				}
			}
		}
		return $arrTerms;
	}

	public function getVocabTransResult($term_ids=array(), $language_id=null) {
		if (is_array($term_ids) && count($term_ids) && is_numeric($language_id) && $language_id > 0) {
			$query = "SELECT ";
			$query.= "`uid`, ";
			$query.= "`term_uid`, ";
			$query.= "`name` ";
			$query.= "FROM ";
			$query.= "`sections_vocabulary_translations` ";
			$query.= "WHERE ";
			$query.= "`term_uid` IN (" . implode(',', $term_ids) . ") ";
			$query.= "AND `language_id`='" . $language_id . "'";
			return database::query($query);
		}
		return array();
	}

	// used on printables controller
	public function getSecvocabTranslationName($term_uid=null, $support_language_uid=null) {
		$newTranslation = '';
		if (is_numeric($term_uid) && $term_uid > 0 && is_numeric($support_language_uid) && $support_language_uid > 0) {
			$query = "SELECT ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`sections_vocabulary_translations` ";
			$query.="WHERE ";
			$query.="`term_uid`='" . $term_uid . "' ";
			$query.="AND ";
			$query.="`language_id`='" . $support_language_uid . "' ";
			$query.="LIMIT 1 ";
			$result = database::query($query);
			if ($result && mysql_num_rows($result) > 0 && mysql_error() == '') {
				$row = mysql_fetch_assoc($result);
				$newTranslation = stripslashes(str_replace('\\','',$row['name']));
			}
		}
		return $newTranslation;
	}

	// used on printables controller
	public function getSecVocabTransArray($vocab, $language_uid, $language_name) {
		$query = "SELECT ";
		$query.="`term_uid`, ";
		$query.="`name` ";
		$query.="FROM ";
		$query.="`sections_vocabulary_translations` ";
		$query.="WHERE ";
		$query.="`term_uid` IN (" . implode(',', array_keys($vocab)) . ") ";
		$query.="AND ";
		$query.="`language_id`='" . $language_uid . "'";
		$result = database::query($query);
		if ($result) {
			if (mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$vocab[$row['term_uid']][$language_name] = html_entity_decode(stripslashes(stripslashes(stripslashes($row['name']))), ENT_COMPAT, 'ISO-8859-1');
				}
			}
			return $vocab;
		}
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();

		$arrValues[] = array(
			"field" => "language_id",
			"value" => $languageUid
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND language_id='" . $enUid . "'" : "";
		$groupBy = " GROUP BY term_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>