<?php

class school_registration_templates_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {

		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			$this->save();
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
		$subject = (isset($_POST['subject'])) ? $_POST['subject'] : '';
		$body = (isset($_POST['body'])) ? str_replace(array('{{', '}}'), array('&#123;&#123;', '&#125;&#125;'), $_POST['body']) : '';
		$from = (isset($_POST['from'])) ? $_POST['from'] : '';
		$arrMessages = array();
		if (trim(strlen($subject)) < 5 || trim(strlen($subject)) > 255) {
			$arrMessages['error.subject'] = "Subject must be 5 to 255 characters in length.";
		} else if (!validation::isValid('text', $subject)) {
			$arrMessages['error.subject'] = "Please enter valid subject.";
		}
		if (trim($body) == '') {
			$arrMessages['error.body'] = "Please enter content.";
		} else if (!validation::isValid('text', $body)) {
			$arrMessages['error.body'] = "Please enter valid content.";
		}
		if (trim($from) == '') {
			$arrMessages['error.from'] = "Please enter sender email.";
		} else if (!validation::isValid('email', $from)) {
			$arrMessages['error.from'] = "Please enter valid sender email.";
		}
		if (count($arrMessages) == 0) {
			$this->set_subject($subject);
			$this->set_body($body);
			$this->set_from($from);
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

	public function isEmailTemplateAvailable($email_uid=null, $locale = 'en') {
		$query = "SELECT ";
		$query .= "`uid` ";
		$query .= "FROM ";
		$query .= "`school_registration_templates_translations` ";
		$query .= "WHERE ";
		$query .= "`email_uid` = '" . $email_uid . "' ";
		$query .= "AND ";
		$query .= "`locale` = '" . $locale . "' ";
		$query .= "AND ";
		$query .= "`subject` != '' ";
		$query .= "AND ";
		$query .= "`body` != '' ";
		$query .= "AND ";
		$query .= "`from` != '' ";
		$result = database::query($query);
		if (mysql_error() == '' && mysql_num_rows($result)) {
			return true;
		} else {
			return false;
		}
	}

	public function getEmailTemplate($email_uid=null, $locale = 'en') {
		$query = "SELECT ";
		$query .= "`subject` ";
		$query .= ", `body` ";
		$query .= ", `from` ";
		$query .= "FROM ";
		$query .= "`school_registration_templates_translations` ";
		$query .= "WHERE ";
		$query .= "`email_uid` = '" . $email_uid . "' ";
		$query .= "AND ";
		$query .= "`locale` = '" . $locale . "' ";
		$query .= "LIMIT 0,1 ";
		$result = database::query($query);
		$data = array();
		if (mysql_error() == '' && mysql_num_rows($result)) {
			$data = mysql_fetch_array($result);
			$data['body'] = str_replace(array('&#123;&#123;', '&#125;&#125;'), array('{{', '}}'), $data['body']);
		}
		return $data;
	}

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		$arrValues[] = array(
			"field" => "locale",
			"value" => $locale
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND locale='en'" : "";
		$groupBy = " GROUP BY email_uid";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>