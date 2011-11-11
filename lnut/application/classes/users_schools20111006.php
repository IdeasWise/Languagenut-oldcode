<?php

class users_schools extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__, true);
	}

	public function SchoolListBox($name='school_id', $selected_value = null) {
		$html = '';
		$query = "SELECT `uid`, ";
		$query.=" `school` ";
		$query.="FROM ";
		$query.="`users_schools` ";
		$query.="WHERE ";
		$query.="`school` != '' ";
		$query.="ORDER BY `school`";
		if (isset($_SESSION['user']['localeRights'])) {
			$query = "SELECT ";
			$query.="`SC`.`uid` , ";
			$query.="`SC`.`school` ";
			$query.="FROM ";
			$query.="`users_schools` AS SC, ";
			$query.="`user` AS U ";
			$query.="WHERE ";
			$query.="`SC`.`school` != '' ";
			$query.="AND ";
			$query.="`U`.`uid` = `SC`.`user_uid` ";
			$query.="AND ";
			$query.="`locale` IN (" . $_SESSION['user']['localeRights'] . ") ";
			$query.="ORDER BY `school`";
		}
		if (isset($_SESSION['user']['admin']) && isset($_SESSION['user']['school_uid']) && $_SESSION['user']['admin'] != 1 && $_SESSION['user']['school_uid'] > 0) {
			$query = "SELECT ";
			$query.="`uid` , ";
			$query.="`school` ";
			$query.="FROM ";
			$query.="`users_schools` ";
			$query.="WHERE ";
			$query.="`school` != '' ";
			$query.="AND ";
			$query.="`uid` = '" . $_SESSION['user']['school_uid'] . "' ";
			$query.="ORDER BY `school`";
		}
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {

			if (mysql_num_rows($result) == 1) {
				$row = mysql_fetch_array($result);
				$html = '<span>' . $row['school'] . '</span>';
				$html.='<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $row['uid'] . '" />';
			} else {
				$data = array();
				$data[''] = "School Name";
				while ($row = mysql_fetch_array($result)) {
					$data[$row['uid']] = $row['school'];
				}
//				$arrSchool=(isset($arrSchool))?$arrSchool:array();
				$arrSchool=(isset($data))?$data:array();
				$html = format::to_select(
								array("name" => $name, "id" => $name, "options_only" => false), $arrSchool, $selected_value
				);
			}
		}
		return $html;
	}

	public function getSchool() {
		$query = "SELECT `uid`, ";
		$query.=" `school` ";
		$query.="FROM ";
		$query.="`users_schools` ";
		$query.="WHERE ";
		$query.="`school` != '' ";
		$query.="ORDER BY `school`";
		if (isset($_SESSION['user']['localeRights'])) {
			$query = "SELECT ";
			$query.="`SC`.`uid` , ";
			$query.="`SC`.`school` ";
			$query.="FROM ";
			$query.="`users_schools` AS SC, ";
			$query.="`user` AS U ";
			$query.="WHERE ";
			$query.="`SC`.`school` != '' ";
			$query.="AND ";
			$query.="`U`.`uid` = `SC`.`user_uid` ";
			$query.="AND ";
			$query.="`locale` IN (" . $_SESSION['user']['localeRights'] . ") ";
			$query.="ORDER BY `school`";
		}
		if (isset($_SESSION['user']['admin']) && isset($_SESSION['user']['school_uid']) && $_SESSION['user']['admin'] != 1 && $_SESSION['user']['school_uid'] > 0) {
			$query = "SELECT ";
			$query.="`uid` , ";
			$query.="`school` ";
			$query.="FROM ";
			$query.="`users_schools` ";
			$query.="WHERE ";
			$query.="`school` != '' ";
			$query.="AND ";
			$query.="`uid` = '" . $_SESSION['user']['school_uid'] . "' ";
			$query.="ORDER BY `school`";
		}
		$result = database::query($query);
		$data = array();
		$data[''] = "School Name";
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_array($result)) {
				$data[$row['uid']] = $row['school'];
			}
		}
		return $data;
	}

	public function getByUserUid($user_uid='') {

		$arrData = array();

		if(0 < (int)$user_uid) {
			$query = "SELECT ";
			$query.= "`uid`, ";
			$query.= "`name`, ";
			$query.= "`school`, ";
			$query.= "`address`, ";
			$query.= "`postcode`, ";
			$query.= "`contact`, ";
			$query.= "`phone_number`, ";
			$query.= "`affiliate`, ";
			$query.= "`notes`, ";
			$query.= "`user_limit` ";
			$query.= "FROM ";
			$query.= "`users_schools` ";
			$query.= "WHERE ";
			$query.= "`user_uid`='".$user_uid."' LIMIT 1";

			$result = database::query($query);

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					$arrData[$row['uid']] = array(
						'uid'			=> $row['uid'],
						'name'			=> stripslashes($row['name']),
						'school'		=> stripslashes($row['school']),
						'address'		=> stripslashes($row['address']),
						'postcode'		=> stripslashes($row['postcode']),
						'contact'		=> stripslashes($row['contact']),
						'phone_number'	=> $row['phone_number'],
						'affiliate'		=> stripslashes($row['affiliate']),
						'notes'			=> stripslashes($row['notes']),
						'user_limit'	=> $row['user_limit']
					);
				}
			}

			echo mysql_error();
		}

		return $arrData;
	}

	public function getSchoolForInvoice() {
		$query = "SELECT ";
		$query.="`user_uid`, ";
		$query.="`school` ";
		$query.="FROM ";
		$query.="`users_schools` ";
		$query.="WHERE ";
		$query.="`school` != '' ";
		$query.="ORDER BY `school`";
		if (isset($_SESSION['user']['localeRights'])) {
			$query = "SELECT ";
			$query.="`SC`.`user_uid`, ";
			$query.="`SC`.`school` ";
			$query.="FROM ";
			$query.="`users_schools` AS SC, ";
			$query.="`user` AS U ";
			$query.="WHERE ";
			$query.="`SC`.`school` != '' ";
			$query.="AND ";
			$query.="`U`.`uid` = `SC`.`user_uid` ";
			$query.="AND `locale` IN (" . $_SESSION['user']['localeRights'] . ") ";
			$query.="ORDER BY `school`";
		}
		$result = database::query($query);
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
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$this->save();
			} else {
				$insert = $this->insert();
				$this->arrForm['uid'] = $insert;
				$query = "UPDATE ";
				$query.="`user` ";
				$query.="SET ";
				$query.="`user_type` = CONCAT(`user_type` , ',school') ";
				$query.="WHERE ";
				$query.="`uid` = '" . mysql_real_escape_string($_POST['user_uid']) . "' ";
				$query.="LIMIT 1 ";
				database::query($query);

				$query = "SELECT ";
				$query.= "`email` ";
				$query.= "FROM ";
				$query.= "`user` ";
				$query.= "WHERE ";
				$query.= "uid = '" . mysql_real_escape_string($_POST['user_uid']) . "' ";
				$res = database::query($query);
				if (mysql_error() == '' && mysql_num_rows($res)) {
					$row = mysql_fetch_array($res);
					$this->addEmailList($_POST['contact'], $row['email']);
				}
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
		$arrFields = array(
			'user_uid' => array(
				'value' => (isset($_POST['user_uid'])) ? trim($_POST['user_uid']) : 0,
				'checkEmpty' => true,
				'errEmpty' => 'Please provide user id!',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'int',
				'errdataType' => 'Please provide valid school name.',
				'errIndex' => 'error_user_uid'
			),
			'school' => array(
				'value' => (isset($_POST['school'])) ? trim($_POST['school']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 3,
				'maxChar' => 64,
				'errMinMax' => 'School name must be 3 to 64 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid school name.',
				'errIndex' => 'error_school'
			),
			'contact' => array(
				'value' => (isset($_POST['contact'])) ? trim($_POST['contact']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 2,
				'maxChar' => 32,
				'errMinMax' => 'Contact name must be 2 to 32 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid contact name.',
				'errIndex' => 'error_contact'
			),
			'phone_number' => array(
				'value' => (isset($_POST['phone_number'])) ? trim($_POST['phone_number']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 8,
				'maxChar' => 15,
				'errMinMax' => 'Phone number must be 8 to 15 characters in length.',
				'dataType' => 'phone',
				'errdataType' => 'Please enter valid phone number.',
				'errIndex' => 'error_phone_number'
			),
			'user_limit' => array(
				'value' => (isset($_POST['user_limit'])) ? trim($_POST['user_limit']) : 0,
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 1,
				'maxChar' => 4,
				'errMinMax' => 'User limit must be 1 to 4 characters in length.',
				'dataType' => 'int',
				'errdataType' => 'Please enter valid user limit.',
				'errIndex' => 'error_user_limit'
			),
			'notes' => array(
				'value' => (isset($_POST['notes'])) ? trim($_POST['notes']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes.',
				'errIndex' => 'error_notes'
			),
			'notes_2wft_call1' => array(
				'value' => (isset($_POST['notes_2wft_call1'])) ? trim($_POST['notes_2wft_call1']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the two week trial call 1.',
				'errIndex' => 'error_notes_2wft_call1'
			),
			'notes_2wft_call2' => array(
				'value' => (isset($_POST['notes_2wft_call2'])) ? trim($_POST['notes_2wft_call2']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the two week trial call 2.',
				'errIndex' => 'error_notes_2wft_call2'
			),
			'notes_2wft_call3' => array(
				'value' => (isset($_POST['notes_2wft_call3'])) ? trim($_POST['notes_2wft_call3']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the two week trial call 3.',
				'errIndex' => 'error_notes_2wft_call3'
			),
			'notes_courtesy_call1' => array(
				'value' => (isset($_POST['notes_courtesy_call1'])) ? trim($_POST['notes_courtesy_call1']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the courtesy call #1 at 4 months.',
				'errIndex' => 'error_notes_courtesy_call1'
			),
			'notes_courtesy_call2' => array(
				'value' => (isset($_POST['notes_courtesy_call2'])) ? trim($_POST['notes_courtesy_call2']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the courtesy call #2 at 8 months.',
				'errIndex' => 'error_notes_courtesy_call2'
			),
			'notes_renewal_call1' => array(
				'value' => (isset($_POST['notes_renewal_call1'])) ? trim($_POST['notes_renewal_call1']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the remewal call 1.',
				'errIndex' => 'error_notes_renewal_call1'
			),
			'notes_renewal_call2' => array(
				'value' => (isset($_POST['notes_renewal_call2'])) ? trim($_POST['notes_renewal_call2']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid notes for the renewal call 2.',
				'errIndex' => 'error_notes_renewal_call2'
			),
			'username_open' => array(
				'value' => (isset($_POST['username_open'])) ? trim($_POST['username_open']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Open username must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid open username.',
				'errIndex' => 'error_username_open'
			),
			'password_open' => array(
				'value' => (isset($_POST['password_open'])) ? trim($_POST['password_open']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 5,
				'maxChar' => 255,
				'errMinMax' => 'Open password must be 5 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid open password.',
				'errIndex' => 'error_password_open'
			),
			'affiliate' => array(
				'value' => (isset($_POST['affiliate'])) ? trim($_POST['affiliate']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 255,
				'errMinMax' => 'Promocode must be 0 to 255 characters in length.',
				'dataType' => 'text',
				'errdataType' => 'Please enter valid promocode.',
				'errIndex' => 'error_affiliate'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_user_uid($arrFields['user_uid']['value']);
			$this->set_school($arrFields['school']['value']);
			$this->set_name($arrFields['contact']['value']);
			$this->set_contact($arrFields['contact']['value']);
			$this->set_phone_number($arrFields['phone_number']['value']);
			$this->set_user_limit($arrFields['user_limit']['value']);
			$this->set_notes($arrFields['notes']['value']);
			
			$objUser = new user($arrFields['user_uid']['value']);
			if($objUser->get_valid()) {
				$objUser->load();
				$objUser->set_notes_2wft_call1($arrFields['notes_2wft_call1']['value']);
				$objUser->set_notes_2wft_call2($arrFields['notes_2wft_call2']['value']);
				$objUser->set_notes_2wft_call3($arrFields['notes_2wft_call3']['value']);
				$objUser->set_notes_courtesy_call1($arrFields['notes_courtesy_call1']['value']);
				$objUser->set_notes_courtesy_call2($arrFields['notes_courtesy_call2']['value']);
				$objUser->set_notes_renewal_call1($arrFields['notes_renewal_call1']['value']);
				$objUser->set_notes_renewal_call2($arrFields['notes_renewal_call2']['value']);
				$objUser->save();
			}


			$this->set_affiliate($arrFields['affiliate']['value']);

			$objUser = new user($arrFields['user_uid']['value']);
			$objUser->load();

			$this->set_language_prefix($objUser->get_locale());

			$objUser->set_username_open($arrFields['username_open']['value']);
			$objUser->set_password_open($arrFields['password_open']['value']);
			$objUser->save();
			return true;
		} else {
			return false;
		}
	}

	public function getInvoiceList($school_id=null) {
		if ($school_id == null) {
			return 'Subscriptions not found.';
		}
		$query = "SELECT ";
		$query.="`SB`.*, ";
		$query.="`school` AS `name` ";
		$query.="FROM ";
		$query.="`subscriptions` AS `SB` , ";
		$query.="`users_schools` AS `S` ";
		$query.="WHERE ";
		$query.="`S`.`user_uid` = `SB`.`user_uid` ";
		$query.="AND `S`.`user_uid` = '" . mysql_real_escape_string($school_id) . "' ";
		$query.="ORDER BY `date_paid` DESC";
		$result = database::query($query);
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$body = make::tpl('body.admin.invoice.school.list.tab');
			while ($data = mysql_fetch_assoc($result)) {
				$panel = make::tpl('body.admin.invoice.school.list.row.tab');
				if ($data['due_date'] != '0000-00-00 00:00:00') {
					$data['time_remains'] = ceil((strtotime($data['due_date']) - time()) / (1 * 24 * 60 * 60));
					if ($data['time_remains'] > 7) {
						$data['time_class'] = 'ClassGreen';
					} elseif ($data['time_remains'] < 7 && $data['time_remains'] > 0) {
						$data['time_class'] = 'ClassOrange';
					} else {
						$data['time_class'] = 'ClassRed';
					}
					$data['time_remains'] .= 'Days';
				} else {
					$data['time_remains'] = '___';
				}
				if ($data['verified'] == 1) {
					$data['verified'] = 'Yes';
				} else {
					$data['verified'] = 'No';
				}
				$data['type'] = 'school';
				if ($data['date_paid'] != '0000-00-00 00:00:00') {
					$data['date_paid'] = date('d/m/Y', strtotime($data['date_paid']));
					$data['paid_string'] = 'Paid';
					$data['paid_button_display'] = 'display:none;';
				} else {
					$data['date_paid'] = '...';
				}
				if ($data['due_date'] != '0000-00-00 00:00:00') {
					$data['due_date'] = date('d/m/Y', strtotime($data['due_date']));
				} else {
					$data['due_date'] = '...';
				}
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
			$this->set_user_uid($user_uid);
			$this->set_name(mysql_real_escape_string($_POST['name']));
			$this->set_school(mysql_real_escape_string($_POST['school']));
			$this->set_address(mysql_real_escape_string($_POST['school_address']));
			$this->set_postcode(mysql_real_escape_string($_POST['school_postcode']));
			$this->set_contact(mysql_real_escape_string($_POST['name']));
			$this->set_phone_number(mysql_real_escape_string($_POST['phone_number']));
			$this->set_affiliate(mysql_real_escape_string($_POST['promo_code']));
			$this->set_language_prefix(mysql_real_escape_string($_POST['locale']));

			$addressObject = new lib_property_address_uk(); // initializing address class object
			$addressObject->set_name(mysql_real_escape_string($_POST['name']));
			$addressObject->set_street_name_1(mysql_real_escape_string($_POST['school_address']));
			$addressObject->set_postcode(mysql_real_escape_string($_POST['school_postcode']));

			$this->set_address_id($addressObject->insert());

			$school_uid = $this->insert();
			$this->addEmailList($_POST['name'], $_POST['email']);
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
			$please_make_this_user_live = true;
			$subscribe_uid = $subscribe->CreateSchoolSubscription(
				$user_uid,
				$price,
				$please_make_this_user_live
			);
			/**
			 * Finally send email notification to that school based on selected email template from school registration form
			 */
			if (isset($_POST['locale']) && isset($_POST['email_template']) && trim($_POST['email_template']) != '0' && $_POST['locale'] != '') {
				$this->sendRegistrationMail($user_uid, $school_uid);
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
		$arrMessage = array();
		if (isset($_POST['name']) && strlen($_POST['name']) < 5 || strlen($_POST['name']) > 32) {
			$arrMessage['error.name'] = 'Owner name must be 5 to 32 characters in length.';
		} else if (!validation::isValid('text', $_POST['name'])) {
			$arrMessages['error.name'] = "Please enter valid owner name.";
		}
		if (isset($_POST['phone_number']) && strlen($_POST['phone_number']) < 8 || strlen($_POST['phone_number']) > 20) {
			$arrMessage['error.phone_number'] = config::translate('field.phone_number.error.8-20');
		} else if (!validation::isValid('int', $_POST['phone_number'])) {
			$arrMessages['error.phone_number'] = "Please enter valid phone number.";
		}
		if (isset($_POST['email']) && trim($_POST['email']) == '') {
			$arrMessage['error.email'] = "Please enter school email.";
		} else if (!validation::isValid('email', $_POST['email'])) {
			$arrMessages['error.email'] = "Please enter valid email.";
		} else if (isset($_POST['email']) && trim($_POST['email']) != '') {
			$userObject = new user();
			if ($userObject->email_exist($_POST['email'])) {
				$arrMessage['error.email'] = "Email already exists.";
			}
		}
		if (strlen($_POST['password']) < 5 || strlen($_POST['password']) > 255) {
			$arrMessage['error.password'] = 'Password must be 5 to 255 characters in length.';
		} else if (!validation::isValid('text', $_POST['password'])) {
			$arrMessages['error.password'] = "Please enter valid password.";
		}
		if (isset($_POST['password']) && isset($_POST['password2']) && $_POST['password'] != $_POST['password2']) {
			$arrMessage['error.password2'] = 'Password missing or passwords do not match.';
		}
		if (isset($_POST['locale']) && trim($_POST['locale']) == '') {
			$arrMessage['error.locale'] = "Please select school locale.";
		}
		if (isset($_POST['school']) && strlen($_POST['school']) < 5 || strlen($_POST['school']) > 64) {
			$arrMessage['error.school'] = 'School name must be 5 to 64 characters in length.';
		} else if (!validation::isValid('text', $_POST['school'])) {
			$arrMessages['error.school'] = "Please enter valid school name.";
		}

		if (isset($_POST['school_address']) && strlen($_POST['school_address']) < 5 || strlen($_POST['school_address']) > 255) {
			$arrMessage['error.school_address'] = 'School address must be 5 to 255 characters in length.';
		} else if (!validation::isValid('text', $_POST['school_address'])) {
			$arrMessages['error.school_address'] = "Please enter valid school address.";
		}
		if (isset($_POST['school_postcode']) && strlen($_POST['school_postcode']) < 4 || strlen($_POST['school_postcode']) > 255) {
			$arrMessage['error.school_postcode'] = 'School postcode must be 5 to 255 characters in length';
		} else if (!validation::isValid('text', $_POST['school_postcode'])) {
			$arrMessages['error.school_postcode'] = "Please enter valid school postcode.";
		}
		if (trim($_POST['username_open']) == trim($_POST['email'])) {
			$arrMessage['error.username_open'] = 'You can not use same values in open username and email.';
		} else if (strlen($_POST['username_open']) < 5 || strlen($_POST['username_open']) > 255) {
			$arrMessage['error.username_open'] = 'Username must be 5 to 255 characters in length.';
		} else if (!validation::isValid('text', $_POST['username_open'])) {
			$arrMessages['error.username_open'] = "Please enter valid open username.";
		} else {
			$userObject = new user();
			if ($userObject->username_exist($_POST['username_open'])) {
				$arrMessage['error.username_open'] = 'Open username is already in use.';
			}
		}
		if (strlen($_POST['password_open']) < 5 || strlen($_POST['password_open']) > 255) {
			$arrMessage['error.password_open'] = 'Open password must be 5 to 255 characters in length.';
		} else if (!validation::isValid('text', $_POST['password_open'])) {
			$arrMessages['error.password_open'] = "Please enter valid open password.";
		}
		if (isset($_POST['promo_code']) && strlen($_POST['promo_code']) > 255) {
			$arrMessage['error.promo_code'] = 'Promo code must be 0 to 255 characters in length.';
		} else if (isset($_POST['promo_code']) && !validation::isValid('text', $_POST['promo_code'])) {
			$arrMessages['error.promo_code'] = "Please enter valid promo code.";
		}
		if (isset($_POST['locale']) && isset($_POST['email_template']) && trim($_POST['email_template']) != '0' && $_POST['locale'] != '') {
			$objTemplate = new school_registration_templates_translations();
			if (!$objTemplate->isEmailTemplateAvailable($_POST['email_template'], $_POST['locale'])) {
				$arrMessage['error.email_template'] = 'Selected email template is not available for selected locale.';
			}
		}

		foreach ($_POST as $idx => $val) {
			$this->arrForm[$idx] = $val;
		}
		return $arrMessage;
	}

	private function sendRegistrationMail($user_uid, $school_uid) {
		$ObjEmailTemplate = new school_registration_templates_translations();
		$data = $ObjEmailTemplate->getEmailTemplate($_POST['email_template'], $_POST['locale']);
		if (is_array($data) && count($data)) {
			$_email = new xhtml();
			$_email->load($data['body'], true);
			$_email->assign(
					array(
						'images' => config::images_common($_POST['locale'] . '/'),
						'uri' => config::base($_POST['locale'] . '/'),
						'base' => config::base(),
						'name' => $_POST['name'],
						'school' => $_POST['school'],
						'school_address' => $_POST['school'] . '<br />' . $_POST['school_address'] . '<br />' . $_POST['school_postcode'],
						'username_open' => $_POST['username_open'],
						'password_open' => $_POST['password_open'],
						'email' => $_POST['email'],
						'password' => $_POST['password'],
						'school_uid' => $school_uid,
						'registration_key' => md5($user_uid . '-' . $_SERVER['REMOTE_ADDR'])
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
		mail('jamie@languagenut.com, lucy@languagenut.com', 'A new Languagenut School Registration![From Admin Area]', $message, $header);
		if (isset($_POST['locale']) && $_POST['locale'] == 'dk') {
			mail('Vibeke <vibeke@englishcenter.dk>', 'A new Languagenut School Registration![From Admin Area]', $message, $header);
		}
	}

	protected function addEmailList($name, $email) {
		//Your API Key. Go to http://www.campaignmonitor.com/api/required/ to see where to find this and other required keys
		$api_key = '595c7c768c20a86383d81a7066f962d9';
		$client_id = null;
		$campaign_id = null;
		$list_id = '84b28341cf0209713c2dc232651c6bfd';
		$cm = new component_campaignmonitor($api_key, $client_id, $campaign_id, $list_id);
		//Optional statement to include debugging information in the result
		//$cm->debug_level = 1;
		//This is the actual call to the method, passing email address, name.
		$result = $cm->subscriberAdd($email, $name);
	}

}

?>