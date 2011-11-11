<?php

class profile_homeuser extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__, true);
	}

	public function doSave() {
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$this->set_itime(time());
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
				$query = "UPDATE ";
				$query.="`user` ";
				$query.="SET ";
				$query.="`user_type` = CONCAT(`user_type` , ',homeuser') ";
				$query.="WHERE ";
				$query.="`uid` = '" . mysql_real_escape_string($_POST['iuser_uid']) . "' ";
				$query.="LIMIT 1 ";
				database::query($query);
			}
			return true;
		}
		return false;
	}

	private function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$iuser_uid = (isset($_POST['iuser_uid']) && is_numeric($_POST['iuser_uid'])) ? $_POST['iuser_uid'] : '0';
		$vfirstname = (isset($_POST['vfirstname']) && strlen(trim($_POST['vfirstname'])) > 0) ? $_POST['vfirstname'] : '';
		$vlastname = (isset($_POST['vlastname']) && strlen(trim($_POST['vlastname'])) > 0) ? $_POST['vlastname'] : '';
		$vemail = (isset($_POST['vemail']) && strlen(trim($_POST['vemail'])) > 0) ? $_POST['vemail'] : '';
		$vphone = (isset($_POST['vphone']) && strlen(trim($_POST['vphone'])) > 0) ? $_POST['vphone'] : '';
		$arrMessages = array();
		if (trim(strlen($vfirstname)) < 5 || trim(strlen($vfirstname)) > 250) {
			$arrMessages['error_vfirstname'] = "First name must be 5 to 250 characters in length.";
		} else if (!validation::isValid('text', $vfirstname)) {
			$arrMessages['error_vfirstname'] = "Please enter valid first name.";
		}
		if (trim(strlen($vlastname)) < 3 || trim(strlen($vlastname)) > 250) {
			$arrMessages['error_vlastname'] = "Last name must be 3 to 250 characters in length.";
		} else if (!validation::isValid('text', $vlastname)) {
			$arrMessages['error_vlastname'] = "Please enter valid last name.";
		}
		if (trim(strlen($vemail)) < 5 || trim(strlen($vemail)) > 250) {
			$arrMessages['error_email'] = "Email must be 5 to 250 characters in length.";
		} else if (!validation::isValid('email', $vemail)) {
			$arrMessages['error_email'] = "Please enter valid email.";
		}
		if (trim(strlen($vphone)) < 8 || trim(strlen($vphone)) > 21) {
			$arrMessages['error_vphone'] = "Phone number must be 8 to 21 characters in length.";
		} else if (!validation::isValid('phone', $vphone)) {
			$arrMessages['error_vphone'] = "Please enter valid phone number.";
		}
		if (count($arrMessages) == 0) {
			$this->set_iuser_uid($iuser_uid);
			$this->set_vfirstname($vfirstname);
			$this->set_vlastname($vlastname);
			$this->set_vemail($vemail);
//			$this->set_vfax($vfax);
			$this->set_vphone($vphone);
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

	public function SubscribeHomeuserSave($user_uid) {
		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1'] : array();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2'] : array();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3'] : array();
		/* Set values to profile_homeuser table fields */
		$this->set_iuser_uid($user_uid);
		$this->set_vfirstname(mysql_real_escape_string($form1['name']['value']));
		$this->set_vemail(mysql_real_escape_string($form1['email']['value']));
		$this->set_vphone(mysql_real_escape_string($form1['phone_number']['value']));
		$this->set_itime(time());
		return $this->insert();
		/* Set values to profile_homeuser table fields END */
	}

}

?>