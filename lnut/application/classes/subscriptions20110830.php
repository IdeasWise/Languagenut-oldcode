<?php

class subscriptions extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($data = array(), $OrderBy = "date_paid DESC", $all = false) {
		$parts = config::get('paths');
		if (!in_array(@$parts[2], array('school', 'homeuser')))
			$parts[2] = 'school';
		$asField = ' `school` AS `name`, ';
		$asField.='`U`.`active`, ';
		$asField.='`U`.`access_allowed`';
		if ($parts[2] == 'school') {
			$WHERE = ', users_schools S, ';
			$WHERE.='`user` AS `U` ';
			$WHERE.='WHERE ';
			$WHERE.='`S`.`user_uid` = `SB`.`user_uid` ';
			$WHERE.='AND ';
			$WHERE.='`invoice_for` = "school" ';
			$WHERE.='AND ';
			$WHERE.='`U`.`deleted` != "1" ';
			$WHERE.='AND ';
			$WHERE.='U.uid = S.user_uid ';
			if (isset($parts[4]) && !is_numeric($parts[4])) {
				$WHERE .= " and `language_prefix` = '" . $parts[4] . "'";
			}
		}
		if ($parts[2] == 'homeuser') {
			$asField = " CONCAT(vfirstname, ' ', vlastname) AS `name`, ";
			$asField.="`vemail`, ";
			$asField.="`vphone`, ";
			$asField.="`U`.`active`, ";
			$asField.="`U`.`access_allowed`";
			$WHERE = ', `profile_homeuser` AS `P`, ';
			$WHERE.='`user` AS `U` ';
			$WHERE.='WHERE ';
			$WHERE.='`P`.`iuser_uid` = `SB`.`user_uid` ';
			$WHERE.='AND ';
			$WHERE.='`invoice_for` = "homeuser" ';
			$WHERE.='AND ';
			$WHERE.='`U`.`deleted` != "1" ';
			$WHERE.='AND `U`.`uid` = `P`.`iuser_uid`';
		}
		foreach ($data as $idx => $val) {
			$WHERE .= " AND " . $idx . "='" . $val . "' ";
		}
		if (isset($_SESSION['user']['localeRights'])) {
			$WHERE .= " AND `U`.`locale` IN (" . $_SESSION['user']['localeRights'] . ") ";
		}
		if ($all == false) {
			$query = 'SELECT ';
			$query.='COUNT(`SB`.`uid`) ';
			$query.='FROM ';
			$query.='`subscriptions` AS `SB` ';
			$query.=$WHERE;
			$result = database::query($query);
			$max = 0;
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_array($result);
				$max = $row[0];
			}
			$pageId = '';
			if ($pageId == '') {
				$n = count($parts) - 1;
				if (isset($parts[$n]) && is_numeric($parts[$n]) && $parts[$n] > 0) {
					$pageId = $parts[$n];
				} else {
					$pageId = 1;
				}
			}
			$this->pager(
					$max, //see above
					config::get("pagesize"), //how many records to display at one time
					$pageId, array("php_self" => "")
			);
			$this->set_range(10);
			$query = "SELECT ";
			$query.="`SB`.*, ";
			$query.= $asField . " ";
			$query.="FROM ";
			$query.="`subscriptions` AS `SB` ";
			$query.=$WHERE;
			$query.=" ORDER BY " . $OrderBy . " ";
			$query.="LIMIT " . $this->get_limit();
			$result = database::query($query);
		} else {
			$query = "SELECT ";
			$query.="`SB`.*, ";
			$query.= $asField . " ";
			$query.="FROM ";
			$query.="`subscriptions` AS `SB` ";
			$query.=$WHERE;
			$query.=" ORDER BY " . $OrderBy . " ";
			$result = database::query($query);
		}
		$this->data = array();
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$this->data[] = $row;
			}
		}
		return $this->data;
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
		}
		return false;
	}

	private function isValidateFormData() {
		if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
			parent::__construct($_POST['uid'], __CLASS__);
			$this->load();
		}
		$userType = (isset($_POST['mode']) && $_POST['mode'] == 'homeuser') ? 'home user' : 'school';
		if (isset($_POST['start_dts']) && trim($_POST['start_dts']) != '') {
			$_POST['start_dts'] = $_POST['start_year'] . '-' . $_POST['start_month'] . '-' . $_POST['start_day'];
		}
		if (isset($_POST['expires_dts']) && trim($_POST['expires_dts']) != '') {
			$_POST['expires_dts'] = $_POST['expires_year'] . '-' . $_POST['expires_month'] . '-' . $_POST['expires_day'];
		}
		if (isset($_POST['date_paid']) && trim($_POST['date_paid']) != '') {
			$_POST['date_paid'] = $_POST['date_year'] . '-' . $_POST['date_month'] . '-' . $_POST['date_day'];
		}
		if (isset($_POST['verified_dts']) && trim($_POST['verified_dts']) != '') {
			$_POST['verified_dts'] = $_POST['verified_year'] . '-' . $_POST['verified_month'] . '-' . $_POST['verified_day'];
		}
		if (isset($_POST['due_date']) && trim($_POST['due_date']) != '') {
			$_POST['due_date'] = $_POST['due_year'] . '-' . $_POST['due_month'] . '-' . $_POST['due_day'];
			if (isset($_POST['start_dts']) && trim($_POST['start_dts']) != '') {
				if (strtotime($_POST['due_date']) < strtotime($_POST['start_dts'] . " +14 day")) {
					$_POST['due_date'] = date('Y-m-d', strtotime($_POST['start_dts'] . " +14 day"));
				}
			}
		} else if (isset($_POST['start_dts']) && trim($_POST['start_dts']) != '') {
			$_POST['due_date'] = date('Y-m-d', strtotime($_POST['start_dts'] . " +14 day"));
		}
		$arrFields = array(
			'user_uid' => array(
				'value' => (isset($_POST['user_uid'])) ? trim($_POST['user_uid']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please choose ' . $userType . ' name',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'int',
				'errdataType' => 'Please enter valid ' . $userType . ' name.',
				'errIndex' => 'error_user_uid'
			),
			'invoice_number' => array(
				'value' => (isset($_POST['invoice_number'])) ? trim($_POST['invoice_number']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 1,
				'maxChar' => 11,
				'errMinMax' => 'Invoice number must be 1 to 11 characters in length.',
				'dataType' => 'int',
				'errdataType' => 'Please enter valid invoice number.',
				'errIndex' => 'error_invoice_number'
			),
			'amount' => array(
				'value' => (isset($_POST['amount'])) ? trim($_POST['amount']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 1,
				'maxChar' => 7,
				'errMinMax' => 'Amount must be 1 to 7 characters in length.',
				'dataType' => 'int',
				'errdataType' => 'Please enter valid amount.',
				'errIndex' => 'error_amount'
			),
			'vat' => array(
				'value' => (isset($_POST['vat'])) ? trim($_POST['vat']) : '',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 1,
				'maxChar' => 7,
				'errMinMax' => 'VAT% must be 1 to 7 characters in length.',
				'dataType' => 'int',
				'errdataType' => 'Please enter valid VAT%.',
				'errIndex' => 'error_vat'
			),
			'start_dts' => array(
				'value' => (isset($_POST['start_dts'])) ? trim($_POST['start_dts']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please provide begin from(start) date.',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid begin from(start) date.',
				'errIndex' => 'error_start_dts'
			),
			'expires_dts' => array(
				'value' => (isset($_POST['expires_dts'])) ? trim($_POST['expires_dts']) : '',
				'checkEmpty' => true,
				'errEmpty' => 'Please provide expires on(end) date.',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid expires on(end) date.',
				'errdateFrom' => strtotime($_POST['start_dts']),
				'errdateTo' => strtotime($_POST['expires_dts']),
				'errdate' => 'Expire date should be bigger then begin date.',
				'errIndex' => 'error_expires_dts'
			),
			'verified' => array(
				'value' => (isset($_POST['verified'])) ? trim($_POST['verified']) : '0',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'int',
				'errdataType' => 'Please choose valid payment verified option.',
				'errIndex' => 'error_verified'
			),
			'date_paid' => array(
				'value' => (isset($_POST['date_paid'])) ? trim($_POST['date_paid']) : '0000-00-00',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid paid date.',
				'errIndex' => 'error_date_paid'
			),
			'verified_dts' => array(
				'value' => (isset($_POST['verified_dts'])) ? trim($_POST['verified_dts']) : '0000-00-00',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid verified date.',
				'errIndex' => 'error_verified_dts'
			),
			'due_date' => array(
				'value' => (isset($_POST['due_date'])) ? trim($_POST['due_date']) : '0000-00-00',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid due date.',
				'errIndex' => 'error_date_paid'
			)
		);
		// $arrFields contains array for fields which needs to be validate and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {
			$this->set_user_uid($arrFields['user_uid']['value']);
			$this->set_invoice_number($arrFields['invoice_number']['value']);
			$this->set_amount($arrFields['amount']['value']);
			$this->set_vat($arrFields['vat']['value']);
			$this->set_start_dts($arrFields['start_dts']['value']);
			$this->set_expires_dts($arrFields['expires_dts']['value']);
			$this->set_verified($arrFields['verified']['value']);
			$this->set_date_paid($arrFields['date_paid']['value']);
			$this->set_verified_dts($arrFields['verified_dts']['value']);
			$this->set_due_date($arrFields['due_date']['value']);
			//$this->set_name($arrFields['name']['value']);
			return true;
		} else {
			return false;
		}
	}

	/*
	  public function doSave_old() {
	  $response = $this->isValidate();
	  if (count($response) == 0) {
	  $due_date = 0;
	  $fields = array();
	  if (@$_POST['user_uid'] > 0)
	  $fields[] = "user_uid = '" . @$_POST['user_uid'] . "'";
	  if (!empty($_POST['date_paid']))
	  $fields[] = "date_paid = '" . $_POST['date_year'] . '-' . $_POST['date_month'] . '-' . $_POST['date_day'] . "'";
	  if (!empty($_POST['start_dts'])) {
	  $fields[] = "start_dts = '" . $_POST['start_year'] . '-' . $_POST['start_month'] . '-' . $_POST['start_day'] . "'";
	  $due_date = strtotime(str_replace('/','-',$_POST['start_dts']). " +14 day");
	  }
	  if (!empty($_POST['expires_dts']))
	  $fields[] = "expires_dts = '" . $_POST['expires_year'] . '-' . $_POST['expires_month'] . '-' . $_POST['expires_day'] . "'";
	  if (!empty($_POST['verified_dts']))
	  $fields[] = "verified_dts  = '" . $_POST['verified_year'] . '-' . $_POST['verified_month'] . '-' . $_POST['verified_day'] . "'";
	  if (!empty($_POST['due_date'])) {
	  if( $due_date > strtotime(str_replace('/','-',$_POST['due_date'])) ){
	  $fields[] = "due_date  = '" . date('Y-m-d',$due_date) . "'";
	  }else {
	  $fields[] = "due_date  = '" . $_POST['due_year'] . '-' . $_POST['due_month'] . '-' . $_POST['due_day'] . "'";
	  }            }
	  if (empty($_POST['due_date'])){
	  $fields[] = "due_date  = '" . date('Y-m-d',$due_date) . "'";
	  }
	  if(isset($_POST['invoice_number']) && $_POST['invoice_number'] != '')
	  $fields[] = "invoice_number = '" . $_POST['invoice_number'] . "'";
	  if(isset($_POST['vat']) && $_POST['vat'] != '')
	  $fields[] = "vat = '" . $_POST['vat'] . "'";
	  $fields[] = "verified = '" . $_POST['verified'] . "'";
	  $fields[] = "amount = '" . $_POST['amount'] . "'";
	  $fields[] = "invoice_for = '" . $_POST['mode'] . "'";
	  $fields[] = "paypal_txn_id = '" . @$_POST['paypal_txn_id'] . "'";
	  if ($_POST['uid'] > 0) {
	  $update = "UPDATE subscriptions SET " . implode(', ', $fields);
	  $update .= " WHERE uid = '" . $_POST['uid'] . "'";
	  database::query($update);
	  //$this->save ();
	  } else {
	  $sql = "INSERT INTO subscriptions SET " . implode(', ', $fields);
	  database::query($sql);
	  // $insert = $this->insert();
	  @$this->arrForm['uid'] = mysql_insert_id();
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
	 */

	public function doPaid($uid) {
		if ($uid > 0) {
			$query = "UPDATE  subscriptions SET date_paid = NOW(), verified_dts = NOW(), verified = '1' WHERE uid = '" . $uid . "' ";
			$res = database::query($query);
			if (mysql_affected_rows()) {
				echo '<div class="ClassGreen">Paid</div>';
			}
		}
	}

	public function CreateSchoolSubscription($user_uid, $price) {
		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1'] : array();
		$now = date('Y-m-d H:i:s');
		list($date, $time) = explode(' ', $now);
		list($y, $m, $d) = explode('-', $date);
		list($h, $i, $s) = explode(':', $time);
		$start = $now;
		$expires = date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y + 1)));
		$due_date = date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y)));

		if (isset($form1['promo_code']['value']) && trim($form1['promo_code']['value']) != '') {
			$getPromoCodedetails = promocode::getPromoCodeDetails($form1['promo_code']['value']);
			if (is_array($getPromoCodedetails) && count($getPromoCodedetails) > 0) {
				if ($getPromoCodedetails['override_date'] == 1) {
					$start = date('Y-m-d H:i:s', strtotime($getPromoCodedetails['sub_start_date']));
					$expires = date('Y-m-d H:i:s', strtotime($getPromoCodedetails['sub_end_date']));
				}
			}
		}

		$priceArray = array();
		$Pricingobject = new currencies();
		$priceArray = $Pricingobject->getPriceAndCurrency('school');
		$this->arrFields['user_uid']['Value'] = $user_uid;
		$this->arrFields['invoice_number']['Value'] = 1600 + $user_uid;
		$this->arrFields['date_paid']['Value'] = '0000-00-00 00:00:00';
		$this->arrFields['due_date']['Value'] = $due_date;
		$this->arrFields['amount']['Value'] = $price;
		$this->arrFields['start_dts']['Value'] = $start;
		$this->arrFields['expires_dts']['Value'] = $expires;
		$this->arrFields['verified']['Value'] = 0;
		$this->arrFields['verified_dts']['Value'] = '0000-00-00 00:00:00';
		$this->arrFields['invoice_for']['Value'] = 'school';
		$this->arrFields['vat']['Value'] = (isset($priceArray['vat'])?$priceArray['vat']:0);
		return $this->insert();
	}

	public function CreateHomeUserSubscription($user_uid, $price) {
		$now = date('Y-m-d H:i:s');
		list($date, $time) = explode(' ', $now);
		list($y, $m, $d) = explode('-', $date);
		list($h, $i, $s) = explode(':', $time);
		$start = $now;
		$expires = date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d), ($y + 1)));
		$priceArray = array();
		$Pricingobject = new currencies();
		$priceArray = $Pricingobject->getPriceAndCurrency('homeuser');
		if (config::get('locale') == 'ge' && @$_SESSION['form1']['promo_code']['value'] == 'BELBOOKS')
			$price = 39.00;
		$this->arrFields['user_uid']['Value'] = $user_uid;
		$this->arrFields['invoice_number']['Value'] = 1600 + $user_uid;
		$this->arrFields['date_paid']['Value'] = '0000-00-00 00:00:00';
		$this->arrFields['due_date']['Value'] = '0000-00-00 00:00:00';
		$this->arrFields['amount']['Value'] = $price;
		$this->arrFields['start_dts']['Value'] = $start;
		$this->arrFields['expires_dts']['Value'] = $expires;
		$this->arrFields['verified']['Value'] = 0;
		$this->arrFields['verified_dts']['Value'] = '0000-00-00 00:00:00';
		$this->arrFields['invoice_for']['Value'] = 'homeuser';
		$this->arrFields['vat']['Value'] = $priceArray['vat'];
		return $this->insert();
	}

	public function getOptions($selected_value = NULL) {
		if (empty($selected_value)) {
			$selected_value = NULL;
		}
		$paths = config::get('paths');
		if (isset($paths[2]) && !in_array($paths[2], array('school', 'homeuser'))) {
			$paths[2] = 'school';
		} else if (!isset($paths[2])) {
			$paths[2] = 'school';
		}
		if ($paths[2] == 'school') {
			if (isset($paths[3]) && $paths[3] == 'edit') {
				$objSchool = new users_schools();
				$objSchool->load(array('school' => 'school', 'user_uid' => 'user_uid'), array('user_uid' => $selected_value));
				return '<input type="hidden" name="user_uid" id="user_uid" value="' . $selected_value . '" /><div id="content"><a href="' . config::url() . 'account/users/profile/school/' . $objSchool->get_user_uid() . '/" class="redioText">' . $objSchool->get_school() . '</a></div>';
			} else {
				$objSchool = new users_schools();
				$arrSchools = $objSchool->getSchoolForInvoice();
				return format::to_select(array("name" => "user_uid", "id" => "user_uid", "options_only" => false), $arrSchools, $selected_value);
			}
		}
		if ($paths[2] == 'homeuser') {
			if ($paths[3] == 'edit') {
				$query = "SELECT ";
				$query.="`uid`, ";
				$query.="CONCAT(`vfirstname`, ' ',`vlastname`) AS `UName`, ";
				$query.="`iuser_uid` ";
				$query.="FROM `profile_homeuser` ";
				$query.="WHERE ";
				$query.="`iuser_uid` = '" . $selected_value . "' ";
				$query.="LIMIT 1";
				$result = database::query($sql);
				if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					$row = mysql_fetch_array($result);
					return '<input type="hidden" name="user_uid" id="user_uid" value="' . $selected_value . '" /><div id="content"><a href="' . config::url() . 'account/users/profile/homeuser/' . @$row['iuser_uid'] . '" class="redioText">' . @$row['UName'] . '</a></div>';
				}
			} else {
				$query = "SELECT ";
				$query .= "`iuser_uid`, ";
				$query .= "CONCAT(`vfirstname`, ' ', `vlastname`) as `UName` ";
				$query .= "FROM ";
				$query .= "`profile_homeuser` AS `PH`, ";
				$query .= "`user` AS `U` ";
				$query .= "WHERE ";
				if (isset($_SESSION['user']['localeRights'])) {
					$query .= "AND `U`.`locale` IN (" . $_SESSION['user']['localeRights'] . ") ";
				}
				$query .= "AND `U`.`uid` = `PH`.`iuser_uid` ";
				$query .= "ORDER BY ";
				$query .= "CONCAT(`vfirstname`, ' ', `vlastname`) ";
				$result = database::query($query);
				$data = array();
				$data[''] = "Home User Name";
				if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
					while ($row = mysql_fetch_array($result)) {
						$data[$row['iuser_uid']] = $row['UName'];
					}
				}
				return format::to_select(array("name" => "user_uid", "id" => "user_uid", "options_only" => false), $data, $selected_value);
			}
		}
	}

	/* start of paypal operations handling functions ( this is used with homeuser subscription ) */

	public function Paypalcancel() {
		if (isset($_SESSION['form1'])) {
			$form1 = $_SESSION['form1'];
			unset($_SESSION['form1']);
			unset($_SESSION['stage']);
			$user_uid = $form1['user_uid'];
			$userObject = new user($user_uid);
			$userObject->load();
			$homeuserObject = new profile_homeuser();
			$huwhere = array('iuser_uid' => $user_uid);
			$homeuserObject->load(array(), $huwhere);
			$where = array('user_uid' => $homeuserObject->TableData['uid']['Value'], 'invoice_for' => 'homeuser');
			$this->load(array(), $where);
			// remove the subscription's record from the database
			$this->where_delete($where);
			// remove the home user's profile record from the database
			$homeuserObject->where_delete($huwhere);
			// remove the user's record from the database
			$userObject->delete();
		}
	}

	public function PaypalSuccess() {
		if (isset($_SESSION['form1'])) {
			$form1 = $_SESSION['form1'];
			unset($_SESSION['form1']);
			if (isset($_SESSION['stage'])) {
				unset($_SESSION['stage']);
			}
			// move the user to the login page
			$_SESSION['login_email'] = $form1['username'];
			$_SESSION['login_password'] = $form1['password'];
		}
	}

	public function PaypalIPN() {

		mail('dev@mystream.co.uk', 'languagenut paypal - pre', print_r($_POST, true), 'From: payments@languagenut.com');
		// paypal ipn here
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		$header = '';
		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		// open socket to paypal
		$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);
		// $fp = @fsockopen('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
		// assign posted variables to local variables
		$item_name = mysql_real_escape_string($_POST['item_name']);
		$item_number = mysql_real_escape_string($_POST['item_number']);
		$payment_status = mysql_real_escape_string($_POST['payment_status']);
		$payment_amount = mysql_real_escape_string($_POST['mc_gross']);
		$payment_currency = mysql_real_escape_string($_POST['mc_currency']);
		$txn_id = mysql_real_escape_string($_POST['txn_id']);
		$receiver_email = mysql_real_escape_string($_POST['receiver_email']);
		$payer_email = mysql_real_escape_string($_POST['payer_email']);
		if (!$fp) {
			// HTTP ERROR
			mail('dev@mystream.co.uk', 'languagenut paypal fp error', $errno . $errstr, 'From: errors@languagenut.com');
		} else {
			mail('dev@mystream.co.uk', 'languagenut paypal fp success', '', 'From: errors@languagenut.com');
			fputs($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets($fp, 1024);
				print $res;
				$res = fgets($fp, 1024);
				if (strcmp($res, "VERIFIED") == 0) {
					$user_uid = $item_number;
					if ($payment_status == 'Completed') {
						// first we check that this transaction has not already been processed
						$this->load(array('paypal_txn_id' => 'paypal_txn_id'), array('paypal_txn_id' => mysql_real_escape_string($txn_id)));
						if (empty($this->TableData['paypal_txn_id']['Value'])) {
							// no records for this transaction id - continue
							// update the subscription to say it has been validated and set the validation date
							// load home user profile
							$homeuserObject = new profile_homeuser();
							$huwhere = array('iuser_uid' => $item_number);
							$homeuserObject->load(array(), $huwhere);
							// load subscriptions
							$where = array('user_uid' => $homeuserObject->TableData['uid']['Value'], 'invoice_for' => 'homeuser');
							$this->load(array(), $where);
							$query = "SELECT ";
							$query.="`uid` ";
							$query.="FROM ";
							$query.="`subscriptions` ";
							$query.="WHERE ";
							$query.="`user_uid`='" . $item_number . "' ";
							$query.="AND ";
							$query.="`invoice_for` = 'homeuser' ";
							$query.="ORDER BY `uid` DESC ";
							$query.="LIMIT 1";
							$result = database::query($query);
							if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
								$row = mysql_fetch_assoc($result);
								$sub_uid = $row['uid'];
								$query = "UPDATE ";
								$query.="`subscriptions` ";
								$query.="SET ";
								$query.="`verified`=1, ";
								$query.="`verified_dts`='" . date('Y-m-d H:i:s') . "', ";
								$query.="`paypal_txn_id`='" . mysql_real_escape_string($txn_id) . "' ";
								$query.="WHERE ";
								$query.="`uid`='" . $sub_uid . "' ";
								$query.="LIMIT 1";
								$result = database::query($query);

								$query = "UPDATE ";
								$query.="`user` ";
								$query = "SET ";
								$query = "`access_allowed`='1' ";
								$query = "WHERE ";
								$query = "`uid`='" . $item_number . "' ";
								$query = "LIMIT 1";
								$result = database::query($query);
								if ($result && mysql_error() == '') {
									// finally... send an email to the admins that a payment has been completed
									if (!empty($homeuserObject->TableData['vfirstname']['Value'])) {
										$user_vemail = '';
										$username = $homeuserObject->TableData['vfirstname']['Value'];
										$user_vemail = $homeuserObject->TableData['vemail']['Value'];
										$headers = '';
										$headers .= "Content-Transfer-Encoding: 8bit" . "\n";
										$headers .= 'Content-type: text/html; charset=iso-8859-15' . "\n";
										$headers .= 'From: payments@languagenut.com' . "\n";
										$email = 'subs@languagenut.com, jamie@languagenut.com, dev@mystream.co.uk';
										$subject = 'Home User account paid via PayPal.';
										$html = '';
										$html .= 'Username:' . $username . '<br />';
										$html .= 'Amount:' . $payment_amount . ' ' . $payment_currency . '<br />';
										mail($email, $subject, $html, $headers);
										// send email to the client
										$email = $username;
										$query = "SELECT ";
										$query.="`subject`,";
										$query.="`body` ";
										$query.="FROM ";
										$query.="`page_subscribe_homeuser_stage_4_translations` ";
										$query.="WHERE ";
										$query.="`locale`='" . config::get('locale') . "' ";
										$query.="LIMIT 1";
										$result = database::arrQuery($query, 1);
										
										$result = database::query($query);
										if (count($result) > 0) {
											$row = $result[0];
											$subject = stripslashes($row['subject']);
											$body = stripslashes(str_replace('{terms}', config::url('/terms/'), $row['body']));
											mail($user_vemail, $subject, $body, $headers);
										} else {
											mail('dev@mystream.co.uk', 'languagenut: cannot find subject/body for client paypal update', mysql_error() . $query, 'From: errors@languagenut.com');
										}
									} else {
										mail('dev@mystream.co.uk', 'languagenut: cannot find user for ' . $item_number, mysql_error() . $query, 'From: errors@languagenut.com');
									}
								} else {
									mail('dev@mystream.co.uk', 'languagenut: subscription [' . $sub_uid . '] not updated after payment', mysql_error() . $query, 'From: errors@languagenut.com');
								}
							} else {
								mail('dev@mystream.co.uk', 'languagenut: no matching subscription for user [' . $item_number . ']', mysql_error() . $query, 'From: errors@languagenut.com');
							}
						} else {
							// payment already been processed - do nothing?
							mail('dev@mystream.co.uk', 'languagenut paypal double callback', '', 'From: errors@languagenut.com');
						}
					} else {
						$pending_subject = 'New Pending Transaction';
						$pending_body = 'Dear Jamie,<br/><br/>';
						switch (@$_POST['pending_reason']) {
							case 'multi_currency':
								$pending_body .= '<b>Reason :- </b><u><i>multi-currency:</u></i> You do not have a balance in the currency sent, and you do not have your <b>Payment Receiving Preferences</b> set to automatically convert and accept this payment. You must manually accept or deny this payment.';
								break;
							case 'order':
								$pending_body .= '<b>Reason :- </b><u><i>order:</u></i> You set the payment action to Order and have not yet captured funds.';
								break;
							case 'paymentreview':
								$pending_body .= '<b>Reason :- </b><u><i>paymentreview:</u></i> The payment is pending while it is being reviewed by PayPal for risk.';
								break;
							case 'unilateral':
								$pending_body .= '<b>Reason :- </b><u><i>unilateral:</u></i> The payment is pending because it was made to an email address that is not yet registered or confirmed.';
								break;
							case 'upgrade':
								$pending_body .= '<b>Reason :- </b><u><i>upgrade:</u></i> The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. upgrade can also mean that you have reached the monthly limit for transactions on your account.';
								break;
							case 'verify':
								$pending_body .= '<b>Reason :- </b><u><i>verify:</u></i> The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.';
								break;
							case 'other':
								$pending_body .= '<b>Reason :- </b><u><i>other:</u></i> The payment is pending for a reason other than those listed above. For more information, contact PayPal Customer Service.';
								break;
						}
						$pending_body .= '<br/><br/>';
						$pending_body .= '<b>Payer Email: </b>' . @$_POST['payer_email'] . '<br/>';
						$pending_body .= '<b>User ID: </b>' . @$_POST['item_number'] . '<br/>';

						mail('jamie@languagenut.com', $pending_subject, $pending_body, 'From: errors@languagenut.com');
						mail('dev@mystream.co.uk', $pending_subject, $pending_body, 'From: errors@languagenut.com');
						// payment is anything other than Completed (i.e. accepted + balance added to paypal account)
						mail('dev@mystream.co.uk', 'languagenut paypal not completed', $payment_status, 'From: errors@languagenut.com');
					}
				} else if (strcmp($res, "INVALID") == 0) {
					// if the IPN POST was 'INVALID'...do this
					mail('dev@mystream.co.uk', 'languagenut paypal INVALID IPN', $errno . $errstr, 'From: errors@languagenut.com');
				}
			}
			fclose($fp);
		}
		exit();
	}

	/* End of paypal operations handling functions ( this is used with homeuser subscription ) */
}

?>