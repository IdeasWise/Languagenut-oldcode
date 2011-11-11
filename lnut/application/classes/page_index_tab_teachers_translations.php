<?php

class page_index_tab_teachers_translations extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			$this->save(array(),1);
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
		$html = (isset($_POST['cms-body'])) ? trim($_POST['cms-body']) : '';
		$arrMessages = array();
		if ($html == '') {
			$arrMessages['error.cms-body'] = "Please enter content.";
		} else if (!validation::isValid('text', $html)) {
			$arrMessages['error.cms-body'] = "Please enter valid content.";
		}
		if (count($arrMessages) == 0) {
			$this->set_html($html);
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

}

?>