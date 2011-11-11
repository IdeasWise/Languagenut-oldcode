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

	public function SaveAdminSchoolRegistration() {
		$response = array();
		$response = $this->isValidSchoolRegistrationdata();
		if (count($response) == 0) {
			/**
			 * Add user	to database	and	add	to subscriptions if	necessary too
			 */
			$user_uid = 0;
			$ip_address = addslashes(substr($_SERVER['REMOTE_ADDR'], 0, 32));
			$ObjUser = new user();
			/* Set values to user table	fields */
			$ObjUser->set_registered_dts(date('Y-m-d H:i:s'));
			$ObjUser->set_registration_ip($ip_address);
			$ObjUser->set_email($_POST['email']);
			$ObjUser->set_password(md5($_POST['password']));
			$ObjUser->set_username_open($_POST['username_open']);
			$ObjUser->set_password_open($_POST['password_open']);
			$ObjUser->set_access_allowed(1);
			$ObjUser->set_allow_access_without_sub(1);
			$ObjUser->set_active(1);
			$ObjUser->set_locale($_POST['locale']);
			$ObjUser->set_user_type('school');
			/* Set values to user table	fields END */
			// insert record to	table.
			$user_uid = $ObjUser->insert();
			// the following function will set registration key and that we'll use to cancel account
			$ObjUser->SetRegistrationKey($user_uid, $ip_address);
			/**
			 *  add any entry to user_school( school profile) table
			 */
			/* Set values to users_schools table fields */
			$school_uid = 0;
			$this->arrFields['user_uid']['Value'] = $user_uid;
			$this->arrFields['name']['Value'] = mysql_real_escape_string($_POST['name']);
			$this->arrFields['school']['Value'] = mysql_real_escape_string($_POST['school']);
			$this->arrFields['address']['Value'] = mysql_real_escape_string($_POST['school_address']);
			$this->arrFields['postcode']['Value'] = mysql_real_escape_string($_POST['school_postcode']);
			$this->arrFields['contact']['Value'] = mysql_real_escape_string($_POST['name']);
			$this->arrFields['phone_number']['Value'] = mysql_real_escape_string($_POST['phone_number']);
			// $this->arrFields['affiliate']['Value'] = mysql_real_escape_string($form1['promo_code']['value']);
			$this->arrFields['language_prefix']['Value'] = mysql_real_escape_string($_POST['locale']);
			$addressObject = new lib_property_address_uk(); // initializing address class object
			$addressObject->arrFields['name']['Value'] = mysql_real_escape_string($_POST['name']);
			$addressObject->arrFields['street_name_1']['Value'] = mysql_real_escape_string($_POST['school_address']);
			$addressObject->arrFields['postcode']['Value'] = mysql_real_escape_string($_POST['school_postcode']);
			$this->arrFields['address_id']['Value'] = $addressObject->insert();
			$school_uid = $this->insert();
			/**
			 * 
			 */
			/**
			 * Fetch the page data from the database for this given locale
			 */
			$price = 0.0;
			$query = "SELECT ";
			$query .= "`school_price` ";
			$query .= "FROM ";
			$query .= "`language` ";
			$query .= "WHERE ";
			$query .= "`prefix` = '" . mysql_real_escape_string($_POST['locale']) . "' ";
			$result = database::query($query);
			if (mysql_error() == '' && mysql_num_rows($result)) {
				$row = mysql_fetch_array($result);
				$price = $row['school_price'];
			} else {
				$price = 80;
			}
			/**
			 * Add a subscription from 'now'+ 1 year + 2 weeks
			 */
			$subscribe = new subscriptions();
			$subscribe_uid = 0;
			$subscribe_uid = $subscribe->CreateSchoolSbscription($user_uid, $price);
			/**
			 * Finally send email notification to that school based on selected email template from school registration form
			 */
			if (isset($_POST['locale']) && isset($_POST['email_template']) && trim($_POST['email_template']) != '' && $_POST['locale'] != '') {
				$this->sendRegistrationMail();
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

	public function isValidSchoolRegistrationdata() {
		$message = array();
		if (isset($_POST['name']) && strlen($_POST['name']) < 5 || strlen($_POST['name']) > 255) {
			$message['error.name'] = 'Name must be 5 to 255 characters in length.';
		}
		if (isset($_POST['phone_number']) && strlen($_POST['phone_number']) < 9 || strlen($_POST['phone_number']) > 20) {
			$message['error.phone_number'] = config::translate('field.phone_number.error.9-20');
		}
		if (isset($_POST['email']) && trim($_POST['email']) == '') {
			$message['error.email'] = "Please enter school email.";
		} else if (isset($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$message['error.email'] = "Please enter valid email.";
		} else if (isset($_POST['email']) && trim($_POST['email']) != '') {
			$userObject = new user();
			if ($userObject->email_exist($_POST['email'])) {
				$message['error.email'] = "Email already exists.";
			}
		}
		if (isset($_POST['password']) && trim($_POST['password']) == '') {
			$message['error.password'] = "Please enter password.";
		}
		if (isset($_POST['password']) && isset($_POST['password2']) && $_POST['password'] != $_POST['password2']) {
			$message['error.password2'] = config::translate('field.password.error.missing-or-mismatch');
		}
		if (isset($_POST['locale']) && trim($_POST['locale']) == '') {
			$message['error.locale'] = "Please select school locale.";
		}
		if (isset($_POST['school']) && strlen($_POST['school']) < 5 || strlen($_POST['school']) > 255) {
			$message['error.school'] = 'School name must be 5 to 255 characters in length.';
		}
		if (isset($_POST['school_address']) && strlen($_POST['school_address']) < 5 || strlen($_POST['school_address']) > 255) {
			$message['error.school_address'] = 'School address must be 5 to 255 characters in length.';
		}
		if (isset($_POST['school_postcode']) && strlen($_POST['school_postcode']) < 4 || strlen($_POST['school_postcode']) > 255) {
			$message['error.school_postcode'] = 'School postcode must be 5 to 255 characters in length';
		}
		if (trim($_POST['username_open']) == trim($_POST['email'])) {
			$message['error.username_open'] = config::translate('field.openusername.match.email.error');
		} else if (strlen($_POST['username_open']) < 5 || strlen($_POST['username_open']) > 255) {
			$message['error.username_open'] = config::translate('field.username.error.5-255');
		} else {
			$userObject = new user();
			if ($userObject->username_exist($_POST['username_open'])) {
				$message['error.username_open'] = config::translate('field.username.error.unavailable');
			}
		}
		if (strlen($_POST['password_open']) < 5 || strlen($_POST['password_open']) > 255) {
			$message['error.password_open'] = config::translate('field.password.error.5-255');
		}
		if (isset($_POST['locale']) && isset($_POST['email_template']) && trim($_POST['email_template']) != '' && $_POST['locale'] != '') {
			$objTemplate = new school_registration_templates_translations();
			if (!$objTemplate->isEmailTemplateAvailable($_POST['email_template'], $_POST['locale'])) {
				$message['error.email_template'] = 'Selected email template is not available for selected locale.';
			}
		}
		foreach ($_POST as $idx => $val) {
			$this->arrForm[$idx] = $val;
		}
		return $message;
	}

	private function sendRegistrationMail() {
		$ObjEmailTemplate = new school_registration_templates_translations();
		$data = $ObjEmailTemplate->getEmailTemplate($_POST['email_template'], $_POST['locale']);
		if (is_array($data) && count($data)) {
			$_email = new xhtml();
			$_email->load($data['body'], true);
			$_email->assign(
					array(
						'images' => config::images(),
						'uri' => config::url(),
						'name' => $_POST['name'],
						'school' => $_POST['school'],
						'school_address' => $_POST['school_address'] . '<br>' . $_POST['school_postcode'],
						'username_open' => $_POST['username_open'],
						'password_open' => $_POST['password_open'],
						'email' => $_POST['email'],
						'password' => $_POST['password']
					)
			);
			$message = $_email->get_content();
			$this->mail_html(
					$_POST['email'], $data['subject'], $message, $data['from'], '', '', '', ''
			);
		}
	}

	private function mail_html($to='', $subject='', $message='', $from='', $receiptname='', $receiptmail='', $cc='', $bcc='') {
		$header = "Content-Transfer-Encoding: 8bit";
		$header .="\nContent-Type: text/html; charset=utf-8";
		if ($from != '') {
			$header .="\nFrom: " . $from;
		}
		if ($cc != '') {
			$header .= "\nCc: " . $cc;
		}
		if ($bcc != '') {
			$header .= "\nBcc: " . $bcc;
		}
		if ($receiptname != '' && $receiptmail != '') {
			//Read receipt
			$headers .= "Disposition-Notification-To: Subscriptions<jamie@languagenut.com>\n";
		}
		$message = str_replace(
				array("<br>", "<br />", "<p>"), array("<br>\n", "<br>\n", "<p>\n"), $message
		);
		mail($to, $subject, $message, $header);
	}

}

?>