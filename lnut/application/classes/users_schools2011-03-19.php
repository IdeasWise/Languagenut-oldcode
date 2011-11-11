<?php

class users_schools extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__, true);
	}

	public function getSchool() {
		$sql = "SELECT uid , school FROM users_schools where school != '' ORDER BY school";
		if (@$_SESSION['user']['admin'] != 1 && @$_SESSION['user']['school_uid'] > 0)
			$sql = "SELECT uid , school FROM users_schools where school != '' and `uid` = '" . @$_SESSION['user']['school_uid'] . "' ORDER BY school";
		$result = database::query($sql);
		$data = array();
		$data[''] = "School Name";
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_array($result)) {
				$data[$row['uid']] = $row['school'];
			}
		}
		return $data;
	}

	public function getSchoolForInvoice() {
		$sql = "SELECT user_uid , school FROM users_schools where school != '' ORDER BY school";
		$result = database::query($sql);
		$data = array();
		$data[''] = "School Name";
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_array($result)) {
				$data[$row['user_uid']] = $row['school'];
			}
		}
		return $data;
	}

	public function doSave() {
		$response = true;
		$response = $this->isValidate();
		if (count($response) == 0) {


			if ($_POST['uid'] > 0)
				$this->save();
			else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
				$sql = database::query("UPDATE user SET user_type = CONCAT(user_type , ',school') where uid = '" . $_POST['user_uid'] . "' ");
			}
			if ($_POST['username_open'] != '' && $_POST['password_open'] != '') {
				$UpdateUser = "UPDATE user SET
                                  username_open = '" . $_POST['username_open'] . "',
                                  password_open = '" . $_POST['password_open'] . "',
                                  password = '" . md5($_POST['password_open']) . "'
                                  where uid = '" . $_POST['user_uid'] . "'
                                  ";
				database::query($UpdateUser);
			}
		} else {
			$msg = NULL;
			foreach ($response as $idx => $val) {
				$this->arrForm[$idx] = 'label_error';
				$msg .= '<li>' . $val . '</li>';
			}
			if ($msg != NULL)
				$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $msg . '</ul>';
		}
		if (count($response) > 0)
			return false;
		else
			return true;
	}

	public function isValidate() {
		if (is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$message = array();
		if (trim($_POST['school']) == '') {
			$message['error_school'] = "Please provide school name";
		}
		if (trim($_POST['username_open']) == '') {
			$message['error_username_open'] = "Please provide open username";
		}
		if (trim($_POST['password_open']) == '') {
			$message['error_password_open'] = "Please provide open password";
		}
		if (trim($_POST['contact']) == '') {
			$message['error_contact'] = "Please provide contact name";
		}
		if (trim($_POST['contact']) == '') {
			$message['error_contact'] = "Please provide contact name";
		}
		if (trim($_POST['phone_number']) == '') {
			$message['error_phone_number'] = "Please provide phone number";
		}

		if (isset($_POST['user_limit']) && !is_numeric($_POST['user_limit'])) {
			$message['error_user_limit'] = "Please enter valid user limit.";
		} else if (isset($_POST['user_limit']) && strlen($_POST['user_limit']) > 4) {
			$message['error_user_limit'] = "Please enter valid user limit.";
		}
		$ignore_array = array('uid', 'submit-edit-profile', 'username_open', 'password_open');
		foreach ($_POST as $idx => $val) {
			$this->arrForm[$idx] = $val;
			if (in_array($idx, $ignore_array))
				continue;
			$this->arrFields[$idx]['Value'] = $val;
		}
		return $message;
	}

	public function getInvoiceList($school_id) {
		$sql = "SELECT SB.*, school as name FROM subscriptions SB , users_schools S where S.user_uid = SB.user_uid and S.user_uid = '" . $school_id . "' ORDER BY date_paid DESC";
		$result = database::query($sql);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$body = new xhtml('body.admin.invoice.school.list.tab');
			$body->load();
			while ($data = mysql_fetch_assoc($result)) {

				$panel = new xhtml('body.admin.invoice.school.list.row.tab');
				$panel->load();
				if ($data['due_date'] != '0000-00-00 00:00:00') {
					$data['time_remains'] = ceil((strtotime($data['due_date']) - time()) / (1 * 24 * 60 * 60));
					if ($data['time_remains'] > 7)
						$data['time_class'] = 'ClassGreen';
					elseif ($data['time_remains'] < 7 && $data['time_remains'] > 0)
						$data['time_class'] = 'ClassOrange';
					else
						$data['time_class'] = 'ClassRed';
					$data['time_remains'] .= 'Days';
				}
				else
					$data['time_remains'] = '___';
				if ($data['verified'] == 1)
					$data['verified'] = 'Yes';
				else
					$data['verified'] = 'No';
				$data['type'] = 'school';
				if ($data['date_paid'] != '0000-00-00 00:00:00') {
					$data['date_paid'] = date('d/m/Y', strtotime($data['date_paid']));
					$data['paid_string'] = 'Paid';
					$data['paid_button_display'] = 'display:none;';
				}
				else
					$data['date_paid'] = '...';
				if ($data['due_date'] != '0000-00-00 00:00:00') {
					$data['due_date'] = date('d/m/Y', strtotime($data['due_date']));
				}
				else
					$data['due_date'] = '...';
				$panel->assign($data);
				$page_rows[] = $panel->get_content();
			}
			$body->assign('list.rows', implode('', $page_rows));
			return $body->get_content();
		}
		return 'Subscriptions not found.';
	}

	public function SubscribeSchoolSave($user_uid) {
		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1'] : array();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2'] : array();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3'] : array();
		/* Set values to users_schools table fields */
		$this->arrFields['user_uid']['Value'] = $user_uid;
		$this->arrFields['name']['Value'] = mysql_real_escape_string($form1['name']['value']);
		$this->arrFields['school']['Value'] = mysql_real_escape_string($form2['school_name']['value']);
		$this->arrFields['address']['Value'] = mysql_real_escape_string($form2['school_address']['value']);
		$this->arrFields['postcode']['Value'] = mysql_real_escape_string($form2['school_postcode']['value']);
		$this->arrFields['contact']['Value'] = mysql_real_escape_string($form1['name']['value']);
		$this->arrFields['phone_number']['Value'] = mysql_real_escape_string($form1['phone_number']['value']);
		$this->arrFields['affiliate']['Value'] = mysql_real_escape_string($form1['promo_code']['value']);
		$this->arrFields['language_prefix']['Value'] = mysql_real_escape_string(config::get('locale'));
		$addressObject = new lib_property_address_uk(); // initializing address class object
		$addressObject->arrFields['name']['Value'] = mysql_real_escape_string($form1['name']['value']);
		$addressObject->arrFields['street_name_1']['Value'] = mysql_real_escape_string($form2['school_address']['value']);
		$addressObject->arrFields['postcode']['Value'] = mysql_real_escape_string($form2['school_postcode']['value']);

		$this->arrFields['address_id']['Value'] = $addressObject->insert();

		/* Set values to users_schools table fields END */
		return $this->insert();
	}

}

?>