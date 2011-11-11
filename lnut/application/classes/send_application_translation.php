<?php

class send_application_translation extends generic_object {

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
		$email_subject = (isset($_POST['email_subject'])) ? trim($_POST['email_subject']) : '';
		$introduction_text = (isset($_POST['introduction_text'])) ? trim(str_replace(array('{{', '}}'), array('&#123;&#123;', '&#125;&#125;'), $_POST['introduction_text'])) : '';
		$email_notification_text = (isset($_POST['email_notification_text'])) ? trim(str_replace(array('{{', '}}'), array('&#123;&#123;', '&#125;&#125;'), $_POST['email_notification_text'])) : '';
		$arrMessages = array();
		if ($introduction_text == '') {
			$arrMessages['error.introduction_text'] = "Please enter introduction text.";
		} else if (!validation::isValid('text', $introduction_text)) {
			$arrMessages['error.introduction_text'] = "Please enter valid introduction text.";
		}
		if (strlen($email_subject) < 5 || strlen($email_subject) > 255) {
			$arrMessages['error.email_subject'] = "Subject must be 5 to 255 characters in length.";
		} else if (!validation::isValid('text', $email_subject)) {
			$arrMessages['error.email_subject'] = "Please enter valid subject.";
		}
		if ($email_notification_text == '') {
			$arrMessages['error.email_notification_text'] = "Please enter email nitification content.";
		} else if (!validation::isValid('text', $email_notification_text)) {
			$arrMessages['error.email_notification_text'] = "Please enter valid email nitification content.";
		}
		if (count($arrMessages) == 0) {
			$this->set_email_subject($email_subject);
			$this->set_introduction_text($introduction_text);
			$this->set_email_notification_text($email_notification_text);
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

	public function duplicateToLanguage($locale, $languageUid) {
		$arrValues = array();
		$arrValues[] = array(
			"field" => "locale",
			"value" => $locale
		);
		$enUid = language::getUidFromPrefix("en");
		$where = ($enUid) ? " AND locale='en'" : "";
		$groupBy = " LIMIT 1";
		$this->copyToTranslation(__CLASS__, __CLASS__, $where, $arrValues, $groupBy);
	}

}

?>