<?php

class school_registration_templates extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($all = false) {
		if ($all == false) {
			$query = "SELECT ";
			$query .= "count(`uid`) AS `ToT` ";
			$query .= "FROM ";
			$query .= "`school_registration_templates` ";
			$this->setPagination($query);
		}
		$query = "SELECT ";
		$query .= "* ";
		$query .= "FROM ";
		$query .= "`school_registration_templates` ";
		$query .= "ORDER BY ";
		$query .= "`template_name` ";
		if ($all == false) {
			$query .= "LIMIT " . $this->get_limit();
		}
		return database::arrQuery($query);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
			}
			return true;
		} else {
			return false;
		}
	}

	private function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$template_name = (isset($_POST['template_name'])) ? $_POST['template_name'] : '';
		$arrMessages = array();
		if (trim(strlen($template_name)) < 5 || trim(strlen($template_name)) > 260) {
			$arrMessages['error.template_name'] = "Templaten name must be 5 to 260 characters in length.";
		} else if (!validation::isValid('text', $template_name)) {
			$arrMessages['error.template_name'] = "Please enter valid template name.";
		}
		if (count($arrMessages) == 0) {
			$this->set_template_name($template_name);
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

	public function getListBox($inputName, $selctedValue = NULL) {
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`template_name` ";
		$query.= "FROM ";
		$query.= "`school_registration_templates` ";
		$query.= "ORDER BY ";
		$query.= "`template_name` ASC";

		if(isset($_SESSION['user']['localeRights']) && !empty($_SESSION['user']['localeRights'])) {
			$query = "SELECT ";
			$query.= "DISTINCT `E`.`uid`, ";
			$query.= "`template_name` ";
			$query.= "FROM ";
			$query.= "`school_registration_templates` AS `E`, ";
			$query.= "`school_registration_templates_translations` AS `ET` ";
			$query.="WHERE ";
			$query.="`E`.`uid`=`email_uid` ";
			$query.="AND ";
			$query.="`locale` IN (".$_SESSION['user']['localeRights'].")";
			$query.= "ORDER BY ";
			$query.= "`template_name` ASC";
		}
		// echo $query;
		$res = database::query($query);
		$data = array();
		$data[0] = 'Email Template';
		if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				$data[$row['uid']] = $row['template_name'];
			}
		}
		return format::to_select(
				array(
			"name" => $inputName,
			"id" => $inputName,
			"options_only" => false
				), $data, $selctedValue
		);
	}

}

?>