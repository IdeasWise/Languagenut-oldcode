<?php

class subscriptions extends generic_object {

	public $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function getList($data = array(), $OrderBy = "date_paid DESC", $all = false) {
		$parts = config::get('paths');
		if (!in_array(@$parts[2], array('school', 'homeuser'))){
			$parts[2] = 'school';
		}
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
			if(isset($_SESSION['user']['tracking_code'])) {
				$WHERE .= " AND `S`.`tracking_code`='".$_SESSION['user']['tracking_code']."'";
			}
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
	public function getExpiredCount() {

		$today = date('Y-m-d H:i:s');
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
		$WHERE.='AND ';
		$WHERE.='`due_date` < "'.$today.'" ';
		$WHERE.='AND ';
		$WHERE.='`expires_dts` > "'.$today.'" ';
		$WHERE.='AND ';
		$WHERE.='`verified`=0 ';

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
		return $max;
	}
	public function dueSchoolList($data = array(),$all = false) {
		$parts = config::get('paths');
		$asField = ' `school` AS `name`, ';
		$asField.='`U`.`active`, ';
		$asField.='`U`.`access_allowed`';
		$today = date('Y-m-d H:i:s');
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
		$WHERE.='AND ';
		$WHERE.='`due_date` < "'.$today.'" ';
		$WHERE.='AND ';
		$WHERE.='`expires_dts` > "'.$today.'" ';
		$WHERE.='AND ';
		$WHERE.='`verified`=0 ';

		if (isset($parts[4]) && !is_numeric($parts[4])) {
			$WHERE .= " and `language_prefix` = '" . $parts[4] . "'";
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
			$query.=" ORDER BY `due_date` ";
			$query.="LIMIT " . $this->get_limit();
			$result = database::query($query);
		} else {
			$query = "SELECT ";
			$query.="`SB`.*, ";
			$query.= $asField . " ";
			$query.="FROM ";
			$query.="`subscriptions` AS `SB` ";
			$query.=$WHERE;
			$query.=" ORDER BY `due_date` ";
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
	public function getListPending($pageId=1) {

		$arrUsers = array();

		$thirty_days_from_now = date('Y-m-d H:i:s',mktime(date('H'),date('i'),date('s'),date('m')+1,date('d'),date('Y')));
		$today = date('Y-m-d H:i:s');
/*
		$query = "SELECT ";
		$query.= "COUNT(`user`.`uid`) AS `max` ";
		$query.= "FROM ";
		$query.= "`subscriptions`, `user` ";
		$query.= "WHERE ";
		$query.= "`subscriptions`.`expires_dts` <= '$thirty_days_from_now' ";
		$query.= "AND `subscriptions`.`expires_dts` >= '$today' ";
		$query.= "AND `subscriptions`.`invoice_for`='school' ";
		$query.= "AND `user`.`uid`=`subscriptions`.`user_uid` ";
		$query.= "AND `user`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
*/
		$query = "SELECT ";
		$query.= "COUNT(`users_schools`.`uid`) AS `max` ";
		$query.= "FROM ";
		$query.= "`subscriptions`, `users_schools` ";
		$query.= "WHERE ";
		$query.= "`subscriptions`.`expires_dts` <= '$thirty_days_from_now' ";
		$query.= "AND `subscriptions`.`expires_dts` >= '$today' ";
		$query.= "AND `subscriptions`.`verified` = '1' ";
		$query.= "AND `subscriptions`.`invoice_for`='school' ";
		$query.= "AND `users_schools`.`user_uid`=`subscriptions`.`user_uid` ";
		$query.= "AND `users_schools`.`language_prefix` IN (".$_SESSION['user']['localeRights'].") ";
		if(isset($_SESSION['user']['tracking_code'])) {
			$query .= " AND `tracking_code`='".$_SESSION['user']['tracking_code']."'";
		}
		
		$result = database::query($query);		
		if($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$max = $row['max'];

			$this->pager(
				$max,
				config::get("pagesize"), //how many records to display at one time
				$pageId, array("php_self" => "")
			);
			$this->set_range(10);

			$query = "SELECT ";
			$query.= "`user`.`email`, ";
			$query.= "`user`.`active`, ";
			$query.= "`user`.`access_allowed`, ";
			$query.= "`user`.`registered_dts`, ";
			$query.= "`subscriptions`.`user_uid`, ";
			$query.= "`subscriptions`.`due_date`, ";
			$query.= "`subscriptions`.`date_paid`, ";
			$query.= "`subscriptions`.`amount`, ";
			$query.= "`subscriptions`.`start_dts`, ";
			$query.= "`subscriptions`.`expires_dts`, ";
			$query.= "`subscriptions`.`invoice_for`, ";
			$query.= "`subscriptions`.`verified`, ";
			$query.= "`subscriptions`.`call_status`, ";
			$query.= "`subscriptions`.`subscription_cancellation_date`, ";
			$query.= "`users_schools`.`name`, ";
			$query.= "`users_schools`.`school`, ";
			$query.= "( SELECT count(`logging_access`.`uid`) FROM `logging_access` WHERE `users_schools`.`uid` = `logging_access`.`school_uid` AND `is_login_entry` = '1' ) AS `AllTime` ";
			$query.= "FROM ";
			$query.= "`subscriptions`, `user`, `users_schools` ";
			$query.= "WHERE ";
			$query.= "`subscriptions`.`expires_dts` <= '$thirty_days_from_now' ";
			$query.= "AND `subscriptions`.`expires_dts` >= '$today' ";
			$query.= "AND `subscriptions`.`invoice_for`='school' ";
			$query.= "AND `user`.`uid`=`subscriptions`.`user_uid` ";
			$query.= "AND `user`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
			$query.= "AND `users_schools`.`user_uid`=`subscriptions`.`user_uid` ";
			if(isset($_SESSION['user']['tracking_code'])) {
				$query .= " AND `tracking_code`='".$_SESSION['user']['tracking_code']."'";
			}
			$query.= "ORDER BY `expires_dts` ASC ";
			$query.= "LIMIT ".(($pageId-1)*10).",10";
			//echo $query; exit;
			$result = database::query($query);
		
			$arrUsers = array();

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row = mysql_fetch_assoc($result)) {
					/*
					$arrData = ($row['invoice_for']=='school') ? users_schools::getByUserUid($row['user_uid']) : '';
					$arrRealData['name'] = '';
					$arrRealData['school'] = '';
					if(is_array($arrData) && count($arrData) > 0) {
						foreach($arrData as $uid=>$data) {
							$arrRealData = $data;
						}
					} else {
						$arrRealData['name'] = '<i>Not Give</i>';
					}*/
					if(empty($row['name'])) {
						$row['name'] = '<i>Not Give</i>';
					}
					if(empty($row['school'])) {
						$row['school'] = '<i>Not Give</i>';
					}
					#echo '<pre>'.print_r($arrData,true).'</pre>';
					$arrUsers[$row['user_uid']] = array(
						'user_uid'		=> $row['user_uid'],
						'email'			=> $row['email'],
						'due_date'		=> $row['due_date'],
						'date_paid'		=> $row['date_paid'],
						'amount'		=> $row['amount'],
						'start_dts'		=> $row['start_dts'],
						'expires_dts'	=> $row['expires_dts'],
						'name'			=> isset($row['name']) ? stripslashes($row['name']) : '',
						'active'		=> $row['active'],
						'access_allowed'=> $row['access_allowed'],
						'verified'		=> $row['verified'],
						'call_status'	=> $row['call_status'],
						'school'		=> isset($row['school']) ? stripslashes($row['school']) : '',
						'registered_dts'=> $row['registered_dts'],
						'cancel_dts'	=> $row['subscription_cancellation_date']
					);
				}
			}
		} else {
			$this->pager(
				0,
				config::get("pagesize"), //how many records to display at one time
				$pageId, array("php_self" => "")
			);
			$this->set_range(10);
		}

		return $arrUsers;
	}
	public static function toCallStatusText($status_id=0) {
		$status_text = 'Not called';

		switch($status_id) {
			case 0:
				$status_text = 'Not called';
			break;
			case 1:
				$status_text = 'Called, No Answer';
			break;
			case 2:
				$status_text = 'Called, Got Lucky';
			break;
			case 3:
				$status_text = 'Called, Left Message';
			break;
		}

		return $status_text;
	}
	public function doSave() {
		if ($this->isValidateFormData() == true) {
			if (isset($_POST['uid']) && is_numeric($_POST['uid']) && $_POST['uid'] > 0) {
				$save_uid = $this->save();

				$subscription_uid = $_POST['uid'];

				if(false !== ($subscription_details = $this->getSubscriptionsDetails($subscription_uid))) {
					$user_uid = $subscription_details['user_uid'];

					// if the type is a school then call the big insert from below
					$type = $subscription_details['invoice_for'];
					$paid = $subscription_details['paid'];
					$upgrade = $subscription_details['upgrade'];
					if($type=='school' && $paid == '0' && $upgrade=='1') {
						$test = $this->ProcessSchoolProductPackages($subscription_uid,$user_uid);
					}
				} else {
					return false;
				}
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
		#if (isset($_POST['date_paid']) && trim($_POST['date_paid']) != '') {
		#	$_POST['date_paid'] = $_POST['date_year'] . '-' . $_POST['date_month'] . '-' . $_POST['date_day'];
		#}
		if (isset($_POST['payverified_dts']) && trim($_POST['payverified_dts']) != '') {
			$_POST['payverified_dts'] = $_POST['payverified_year'] . '-'. $_POST['payverified_month'] . '-'.$_POST['payverified_day'];
		}
		if (isset($_POST['sent_dts']) && trim($_POST['sent_dts']) != '') {
			$_POST['sent_dts'] = $_POST['sent_year'] . '-'. $_POST['sent_month'] . '-'.$_POST['sent_day'];
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
		if(isset($_POST['vat']) && strlen($_POST['vat']) < 1) {
			$_POST['vat'] = 0;
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
				'minChar' => 0,
				'maxChar' => 6,
				'errMinMax' => 'VAT% must be 1 to 6 characters in length.',
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
			'payverified_dts' => array(
				'value' => (isset($_POST['payverified_dts'])) ? trim($_POST['payverified_dts']) : '0000-00-00 00:00:00',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid paid date.',
				'errIndex' => 'error_date_paid'
			),
			'sent_dts' => array(
				'value' => (isset($_POST['sent_dts'])) ? trim($_POST['sent_dts']) : '0000-00-00 00:00:00',
				'checkEmpty' => false,
				'errEmpty' => '',
				'minChar' => 0,
				'maxChar' => 0,
				'errMinMax' => '',
				'dataType' => 'text',
				'errdataType' => 'Please provide valid invoice sent date.',
				'errIndex' => 'error_sent_dts'
			),
			'verified_dts' => array(
				'value' => (isset($_POST['verified_dts'])) ? trim($_POST['verified_dts']) : '0000-00-00 00:00:00',
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
				'value' => (isset($_POST['due_date'])) ? trim($_POST['due_date']) : '0000-00-00 00:00:00',
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
		// $arrFields contains array for fields which needs to be validated and then we are passing class object($this)
		if ($this->isValidarrFields($arrFields, $this) === true) {

			$was_verified = $this->get_verified();
			$verified_dts = $this->get_verified_dts();
			$this->set_user_uid($arrFields['user_uid']['value']);
			$this->set_invoice_number($arrFields['invoice_number']['value']);
			$this->set_amount($arrFields['amount']['value']);
			$this->set_vat((float)$arrFields['vat']['value']);
			$this->set_start_dts($arrFields['start_dts']['value']);
			$this->set_expires_dts($arrFields['expires_dts']['value']);
			$this->set_verified((strlen(trim($arrFields['verified_dts']['value']))>0)?1:$arrFields['verified']['value']);
			$this->set_paid($_POST['payment_verified']==1 ? 1 : 0);
			$this->set_date_paid($arrFields['payverified_dts']['value']);
			$this->set_verified_dts($arrFields['verified_dts']['value']);
			$this->set_sent_dts($arrFields['sent_dts']['value']);
			$this->set_sent(((isset($_POST['invoice_sent']) && $_POST['invoice_sent']==1)?1:0));
			$this->set_due_date($arrFields['due_date']['value']);
			//$this->set_call_status(isset($arrFields['call_status']) ? $arrFields['call_status']['value'] : '');
			//$this->set_name($arrFields['name']['value']);

			if(0==$was_verified && $arrFields['verified']['value']==1 && $verified_dts == '0000-00-00 00:00:00') {
				if(strlen(trim($arrFields['verified_dts']['value']))=='') {
					$this->set_verified_dts(date('Y-m-d H:i:s'));
					$arrFields['verified_dts']['value'] = date('Y-m-d H:i:s');
				}
				$start_dts = $this->get_set_start_dts();
				$expires_dts = date('Y-m-d H:i:s',strtotime($start_dts.' +54 week'));
				$this->set_expires_dts($expires_dts);
				$this->financeNotification($arrFields['user_uid']['value'],$arrFields['verified_dts']['value']);
			} else {
			}
			return true;
		} else {
			return false;
		}
	}

	public function getUserlocale($user_uid=null) {
		if($user_uid!=null && is_numeric($user_uid) && $user_uid > 0) {
			$query = "SELECT `locale` FROM `user` WHERE`uid`='".$user_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
				return $arrRow['locale'];
			}
		}
		return false;
	}
	public function financeNotification($user_uid=null,$verified_dts='0000-00-00 00:00:00') {
		if($user_uid!=null && is_numeric($user_uid) && $user_uid > 0) {
			if($verified_dts=='0000-00-00 00:00:00') {
				$verified_dts = date('Y-m-d H:i:s');
			}
			$locale = $this->getUserlocale($user_uid);
			if($locale!=false) {
				//$query = "SELECT `finance_email` FROM `profile_reseller` WHERE `iuser_uid`=".$_SESSION['user']['uid']." LIMIT 1";
				$query = "SELECT `finance_email` FROM `profile_reseller` WHERE `locale_rights`='".$locale."' LIMIT 1";
				$result = database::query($query);
				if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					$finance_email = $row['finance_email'];
					if(strlen($finance_email) > 0) {

						$query = "SELECT `name`,`school`,`address`,`postcode`,`contact`,`phone_number` FROM `users_schools` WHERE `user_uid`=".$user_uid." LIMIT 1";

						$contact_name = '';
						$school_name = '';
						$school_address = '';
						$phone = '';
						$email = '';

						$result = database::query($query);
						if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
							$row = mysql_fetch_assoc($result);
							$contact_name = $row['contact'];
							$school_name = $row['school'];
							$school_address = $row['address'].", ".$row['postcode'];
							$phone = $row['phone_number'];

							$query = "SELECT `email` FROM `user` WHERE `uid`=".$user_uid." LIMIT 1";
							$result = database::query($query);
							if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
								$row = mysql_fetch_assoc($result);
								$email = $row['email'];
							}
						}

						$hasActiveSubscription = (false == $this->getUserSubscriptionDetails($user_uid)) ? false : true;

						$extraMessage = '';
						if($hasActiveSubscription) {
							$extraMessage.="\n** This is a resubscription\n\n";
						}

						mail(
							$finance_email,
							'Subscription Verified',
							"A Subscription has been verified. Please send out an invoice for the following school.\n\n".
							"Contact Name: $contact_name\n".
							"School Name: $school_name\n".
							"School Address: $school_address\n".
							"Phone: $phone\n".
							"Email: $email\n".
							"Verified Date: ".date('d/m/Y',strtotime($verified_dts))."\n".
							$extraMessage,
							"From: info@languagenut.com"
						);

						mail(
							'jamie@languagenut.com',
							'Subscription Verified',
							"A Subscription has been verified. Please send out an invoice for the following school.\n\n".
							"Contact Name: $contact_name\n".
							"School Name: $school_name\n".
							"School Address: $school_address\n".
							"Phone: $phone\n".
							"Email: $email\n".
							"Verified Date: ".date('d/m/Y',strtotime($verified_dts))."\n".
							$extraMessage,
							"From: info@languagenut.com"
						);
/*
						mail(
							'andrew@languagenut.com',
							'Subscription Verified',
							"A Subscription has been verified. Please send out an invoice for the following school.\n\n".
							"Contact Name: $contact_name\n".
							"School Name: $school_name\n".
							"School Address: $school_address\n".
							"Phone: $phone\n".
							"Email: $email\n".
							"Verified Date: ".date('d/m/Y',strtotime($verified_dts))."\n".
							$extraMessage,
							"From: info@languagenut.com"
						);

						mail(
							'testing@mystream.co.uk',
							'Subscription Verified',
							"A Subscription has been verified. Please send out an invoice for the following school.\n\n".
							"Contact Name: $contact_name\n".
							"School Name: $school_name\n".
							"School Address: $school_address\n".
							"Phone: $phone\n".
							"Email: $email\n".
							"Verified Date: ".date('d/m/Y',strtotime($verified_dts))."\n".
							$extraMessage,
							"From: info@languagenut.com"
						);
*/
					}
				} else {

				}
			}
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
	public function CreateSchoolSubscription($user_uid, $price, $upgrade='',$makeLive=false) {
		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1'] : array();
		$now = date('Y-m-d H:i:s');
		list($date, $time) = explode(' ', $now);
		list($y, $m, $d) = explode('-', $date);
		list($h, $i, $s) = explode(':', $time);
		$start = $now;

		$due_date		= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y)));
		//$expires		= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y + 1)));
		$expires		= $due_date;

		$date_paid		= '0000-00-00 00:00:00';
		$verified_dts	= '0000-00-00 00:00:00';
		$verified		= 0;
		/*
		 * if $makeLive === true means user goes live directly without 14 days trial
		*/
		if($makeLive===true) {
			$date_paid		= date('Y-m-d H:i:s');
			$verified_dts	= date('Y-m-d H:i:s');
			$verified		= 1;
		}
		/*
		 * uncomment following code when you want to start use of promo_code start and end date with
		 * user subscription period
		*/
		/*
		if (isset($form1['promo_code']['value']) && trim($form1['promo_code']['value']) != '') {
			$getPromoCodedetails = promocode::getPromoCodeDetails($form1['promo_code']['value']);
			if (is_array($getPromoCodedetails) && count($getPromoCodedetails) > 0) {
				if ($getPromoCodedetails['override_date'] == 1) {
					$start = date('Y-m-d H:i:s', strtotime($getPromoCodedetails['sub_start_date']));
					$expires = date('Y-m-d H:i:s', strtotime($getPromoCodedetails['sub_end_date']));
				}
			}
		}

		if (isset($_POST['promo_code']) && trim($_POST['promo_code']) != '') {
			$getPromoCodedetails = promocode::getPromoCodeDetails($_POST['promo_code']);
			if (is_array($getPromoCodedetails) && count($getPromoCodedetails) > 0) {
				if ($getPromoCodedetails['override_date'] == 1) {
					$start = date('Y-m-d H:i:s', strtotime($getPromoCodedetails['sub_start_date']));
					$expires = date('Y-m-d H:i:s', strtotime($getPromoCodedetails['sub_end_date']));
				}
			}
		}
		*/

		$priceArray = array();
		$Pricingobject = new currencies();
		$priceArray = $Pricingobject->getPriceAndCurrency('school');
		$this->set_user_uid($user_uid);
		$this->set_invoice_number((1600+$user_uid));
		$this->set_due_date($due_date);
		$this->set_amount($price);
		$this->set_start_dts($start);
		$this->set_expires_dts($expires);
		$this->set_invoice_for('school');
		$this->set_vat((isset($priceArray['vat'])?$priceArray['vat']:0));

		$this->set_date_paid($date_paid);
		$this->set_verified($verified);
		$this->set_verified_dts($verified_dts);
		$this->set_upgrade($upgrade);
		if(isset($_SESSION['sess_package'])) {
			$this->set_package_token(mysql_real_escape_string($_SESSION['sess_package']));
		} else {
			$this->set_package_token('standard');
		}
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
		if(isset($_SESSION['sess_package'])) {
			$this->set_package_token(mysql_real_escape_string($_SESSION['sess_package']));
		} else {
			$this->set_package_token('home');
		}
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
				$result = database::query($query);
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

		mail('andrew@languagenut.com', 'languagenut paypal - pre', print_r($_POST, true), 'From: info@languagenut.com');
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
			mail('andrew@languagenut.com', 'languagenut paypal fp error', $errno . $errstr, 'From: info@languagenut.com');
		} else {
			mail('andrew@languagenut.com', 'languagenut paypal fp success', '', 'From: info@languagenut.com');
			fputs($fp, $header . $req);
			while (!feof($fp)) {
				//$res = fgets($fp, 1024);
				//print $res;
				$res = fgets($fp, 1024);
				if (strcmp($res, "VERIFIED") == 0) {
					$user_uid = $item_number;
					if ($payment_status == 'Completed') {
						// first we check that this transaction has not already been processed
						$query ="SELECT ";
						$query.="`uid` ";
						$query.="FROM ";
						$query.="`subscriptions` ";
						$query.="WHERE `paypal_txn_id` = '".mysql_real_escape_string($txn_id)."' ";
						$result = database::query($query);

						//$this->load(array('paypal_txn_id' => 'paypal_txn_id'), array('paypal_txn_id' => mysql_real_escape_string($txn_id)));
						if (mysql_query()=='' && mysql_num_rows($result)==0) {
							// no records for this transaction id - continue
							// update the subscription to say it has been validated and set the validation date
							// load home user profile
							$query ="SELECT ";
							$query.="* ";
							$query.="FROM ";
							$query.="`profile_homeuser` ";
							$query.="WHERE ";
							$query.="`iuser_uid`='".$item_number."' ";

							$resultProfileHomeuser = database::query($query);
							$arrHomeuser = array();
							if(mysql_error()=='' && mysql_num_rows($resultProfileHomeuser)) {
								$arrHomeuser = mysql_fetch_array($resultProfileHomeuser);
							}
							// load subscriptions
							/*
							$where = array('user_uid' => $homeuserObject->TableData['uid']['Value'], 'invoice_for' => 'homeuser');
							$this->load(array(), $where);
							*/
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
								$query.="`verified_dts`='".date('Y-m-d H:i:s')."', ";
								$query.="`date_paid`='".date('Y-m-d H:i:s')."', ";
								$query.="`due_date`='".date('Y-m-d H:i:s')."', ";
								$query.="`paypal_txn_id`='" . mysql_real_escape_string($txn_id) . "' ";
								$query.="WHERE ";
								$query.="`uid`='" . $sub_uid . "' ";
								$query.="LIMIT 1";
								$result = database::query($query);

								$query = "UPDATE ";
								$query.="`user` ";
								$query.= "SET ";
								$query.= "`access_allowed`='1' ";
								$query.= "WHERE ";
								$query.= "`uid`='" . $item_number . "' ";
								$query.= "LIMIT 1";
								$result = database::query($query);
								if ($result && mysql_error() == '') {
									// finally... send an email to the admins that a payment has been completed
									if (isset($arrHomeuser['vfirstname']) && $arrHomeuser['vfirstname']!='') {
										$username = '';
										$user_vemail = '';
										$username = $arrHomeuser['vfirstname'];
										$user_vemail = $arrHomeuser['vemail'];

										$headers = '';
										$headers .= "Content-Transfer-Encoding: 8bit" . "\n";
										$headers .= 'Content-type: text/html; charset=iso-8859-15' . "\n";
										$headers .= 'From: info@languagenut.com' . "\n";
										$email = 'subs@languagenut.com, jamie@languagenut.com, andrew@languagenut.com';
										$subject = 'Home User account paid via PayPal.';
										$html = '';
										$html .= 'Username:' . $username . '('.$user_vemail.')<br />';
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
											mail('andrew@languagenut.com', 'languagenut: cannot find subject/body for client paypal update', mysql_error() . $query, 'From: info@languagenut.com');
										}
									} else {
										mail('andrew@languagenut.com', 'languagenut: cannot find user for ' . $item_number, mysql_error() . $query, 'From: info@languagenut.com');
									}
								} else {
									mail('andrew@languagenut.com', 'languagenut: subscription [' . $sub_uid . '] not updated after payment', mysql_error() . $query, 'From: info@languagenut.com');
								}
							} else {
								mail('andrew@languagenut.com', 'languagenut: no matching subscription for user [' . $item_number . ']', mysql_error() . $query, 'From: info@languagenut.com');
							}
						} else {
							// payment already been processed - do nothing?
							mail('andrew@languagenut.com', 'languagenut paypal double callback', '', 'From: info@languagenut.com');
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

						mail('jamie@languagenut.com', $pending_subject, $pending_body, 'From: info@languagenut.com');
						mail('andrew@languagenut.com', $pending_subject, $pending_body, 'From: info@languagenut.com');
						// payment is anything other than Completed (i.e. accepted + balance added to paypal account)
						mail('andrew@languagenut.com', 'languagenut paypal not completed', $payment_status, 'From: info@languagenut.com');
					}
				} else if (strcmp($res, "INVALID") == 0) {
					// if the IPN POST was 'INVALID'...do this
					mail('andrew@languagenut.com', 'languagenut paypal INVALID IPN', $errno . $errstr, 'From: info@languagenut.com');
				}
			}
			fclose($fp);
		}
		exit();
	}

	/* End of paypal operations handling functions ( this is used with homeuser subscription ) */
	public function getUserSubscriptionDetails($user_uid=null) {
		if($user_uid==null) {
			return false;
		} else {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`subscriptions` ";
			$query.="WHERE ";
			$query.="`user_uid`='".$user_uid."'";
			$query.="ORDER BY ";
			$query.="`uid` DESC ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				$arrRow = mysql_fetch_array($result);
				return $arrRow;
			}
		}
		return false;
	}
	public function getAllUserSubscriptionsDetails($user_uid=null) {
		if($user_uid==null) {
			return false;
		} else {
			$query ="SELECT ";
			$query.="`subscriptions_products`.`product_locale_uid` ";
			$query.="FROM ";
			$query.="`subscriptions_products` ";
			$query.="LEFT JOIN ";
			$query.="`subscriptions` ";
			$query.="ON ";
			$query.="`subscriptions_products`.`subscriptions_uid` = `subscriptions`.`uid` ";
			$query.="WHERE ";
			$query.="`subscriptions`.`user_uid`='".$user_uid."' ";
			$query.="ORDER BY ";
			$query.="`subscriptions`.`uid` DESC ";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				while ($arrRow = mysql_fetch_array($result)) {
					$subscriptions[] = $arrRow['product_locale_uid'];
				}
				return $subscriptions;
			} else {
				return false;
			}
		}
		return false;
	}
	public function getSubscriptionsDetails($subscription_uid=null) {
		if($subscription_uid==null) {
			return false;
		} else {
			$query ="SELECT ";
			$query.="`subscriptions`.`user_uid`, `subscriptions`.`invoice_for`, `subscriptions`.`paid`, `subscriptions`.`upgrade` ";
			$query.="FROM ";
			$query.="`subscriptions` ";
			$query.="WHERE ";
			$query.="`subscriptions`.`uid`='".$subscription_uid."'";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				return mysql_fetch_array($result);
			} else {
				return false;
			}
		}
	}
	public static function addSchoolSubscription($subscription_uid=null,$user_uid=null) {
		if($subscription_uid!=null && $user_uid!=null) {
			// Set due date
			$date = strtotime("+2 weeks");
			$due_date = date('Y-m-d H:i:s',$date);

			$query = "UPDATE ";
			$query.="`subscriptions` ";
			$query.="SET ";
			$query.="`verified`=1, ";
			$query.="`verified_dts`='0', ";
			$query.="`date_paid`='0', ";
			$query.="`due_date`='" . $due_date . "', ";
			$query.="WHERE ";
			$query.="`uid`='" . $subscription_uid . "' ";
			$query.="LIMIT 1";
			$result = database::query($query);

			$query = "UPDATE ";
			$query.="`user` ";
			$query.= "SET ";
			$query.= "`access_allowed`='1' ";
			$query.= "WHERE ";
			$query.= "`uid`='" . $user_uid . "' ";
			$query.= "LIMIT 1";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_num_rows($result)) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	public static function ProcessSchoolProductPackages($subscription_uid=null,$user_uid=null) {
		if($subscription_uid!=null && $user_uid!=null) {
			$query ="INSERT INTO ";
			$query.="`school_packages` ";
			$query.="( ";
				$query.="`package_uid`,";
				$query.="`reseller_uid`,";
				$query.="`name`,";
				$query.="`support_language_uid`,";
				$query.="`learnable_language`,";
				$query.="`sections`,";
				$query.="`games`,";
				$query.="`price`,";
				$query.="`vat`,";
				$query.="`is_approved`,";
				$query.="`requested_date`,";
				$query.="`approved_date`,";
				$query.="`requested_by_uid`,";
				$query.="`school_uid`,";
				$query.="`approved_by_uid`";
			$query.=") ";
			$query.="SELECT ";
			$query.="`uid`, ";
			$query.="`reseller_uid`,";
			$query.="`name`,";
			$query.="`support_language_uid`,";
			$query.="`learnable_language`,";
			$query.="`sections`,";
			$query.="`games`,";
			$query.="`price`,";
			$query.="`vat`,";
			$query.="'1',";
			$query.="'".date('Y-m-d H:i:s')."',";
			$query.="'".date('Y-m-d H:i:s')."',";
			$query.="'".$user_uid."',";
			$query.="'".$user_uid."',";
			$query.="`reseller_uid` ";
			$query.="FROM ";
			$query.="`reseller_sub_package` ";
			$query.="WHERE ";
			$query.="`uid` ";
			$query.="IN ( ";
				$query.="SELECT ";
				$query.="`sub_package_uid` ";
				$query.="FROM ";
				$query.="`product_package` ";
				$query.="WHERE ";
				$query.="`product_uid` ";
				$query.="IN ( ";
				$query.="SELECT ";
				$query.="`product_locale_uid` ";
				$query.="FROM ";
				$query.="`subscriptions_products` ";
				$query.="WHERE ";
				$query.="`subscriptions_uid`='".$subscription_uid."'";
				$query.=")";
			$query.=")";
			$result = database::query($query);
			if(mysql_error() == '' && mysql_affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	public function getAllSubscribedPackages() {
		$arrPackages = array();
		if(isset($_SESSION['user']['uid'])) {
			$arrPackages = array();
			$query = "SELECT ";
			$query.= "`package_token` ";
			$query.= "FROM ";
			$query.= "`subscriptions` ";
			$query.= "WHERE ";
			$query.= "`user_uid`='".$_SESSION['user']['uid']."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($arrRow = mysql_fetch_array($result)) {
					$arrPackages[] = $arrRow['package_token'];
				}
			}
		}
		return $arrPackages;
	}

	public function upgradeuserPackage($package_token=null,$user_uid=null) {
		if($package_token==null || ($user_uid==null && !isset($_SESSION['user']['uid']))) {
			return false;
		} else {
			if($user_uid==null) {
				$user_uid=$_SESSION['user']['uid'];
			}
			// check is that valid package_token
			if(in_array($package_token,array('standard','eal'))) {
				$package_token = mysql_real_escape_string($package_token);
				// check already subscribed ?
				if($this->isUserAlreadySubscribed($package_token,$user_uid)===false) {
					$now = date('Y-m-d H:i:s');
					list($date, $time) = explode(' ', $now);
					list($y, $m, $d) = explode('-', $date);
					list($h, $i, $s) = explode(':', $time);
					$start = $now;

					$due_date		= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y)));
					//$expires		= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y + 1)));
					$expires		= $due_date;

					$date_paid		= '0000-00-00 00:00:00';
					$verified_dts	= '0000-00-00 00:00:00';
					$verified		= 0;
					$token = '';
					if($package_token=='standard') {
						$token = 'mfl';
					} else if($package_token=='eal') {
						$token = 'eal';
					} 
					$priceArray = array();
					$Pricingobject = new currencies();
					$priceArray = $Pricingobject->getPriceAndCurrency('school');
					$this->set_user_uid($user_uid);
					$this->set_invoice_number($token.(1600+$user_uid));
					$this->set_due_date($due_date);
					$this->set_amount($price);
					$this->set_start_dts($start);
					$this->set_expires_dts($expires);
					$this->set_invoice_for('school');
					$this->set_vat((isset($priceArray['vat'])?$priceArray['vat']:0));

					$this->set_date_paid($date_paid);
					$this->set_verified($verified);
					$this->set_verified_dts($verified_dts);
					$this->set_package_token($package_token);
					return $this->insert();
				}
			}
		}
	}

	public function upgrade_lgfl_package($package_token=null,$user_uid=null) {
		if($package_token==null || ($user_uid==null && !isset($_SESSION['user']['uid']))) {
			return false;
		} else {
			if($user_uid==null) {
				$user_uid=$_SESSION['user']['uid'];
			}
			if($user_uid!=null && is_numeric($user_uid) && $user_uid > 0) {
				$objUser = new user($user_uid);
				$objUser->load();
				$user_uid = $objUser->getSchoolId();
			}
			// check is that valid package_token
			if(in_array($package_token,array('standard','eal'))) {
				$package_token = mysql_real_escape_string($package_token);
				// check already subscribed ?
				if($this->isUserAlreadySubscribed($package_token,$user_uid)===false) {
					$now = date('Y-m-d H:i:s');
					list($date, $time) = explode(' ', $now);
					list($y, $m, $d) = explode('-', $date);
					list($h, $i, $s) = explode(':', $time);
					$start = $now;

					$due_date		= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, $d, ($y + 1)));
					//$expires		= date('Y-m-d H:i:s', mktime($h, $i, $s, $m, ($d + 14), ($y + 1)));
					$expires		= $due_date;

					$date_paid		= date('Y-m-d H:i:s');
					$verified_dts	= date('Y-m-d H:i:s');
					$verified		= 1;
					$token = '';
					if($package_token=='standard') {
						$token = 'mfl';
					} else if($package_token=='eal') {
						$token = 'eal';
					} 
					$priceArray = array();
					$Pricingobject = new currencies();
					$priceArray = $Pricingobject->getPriceAndCurrency('school');
					$this->set_user_uid($user_uid);
					$this->set_invoice_number($token.(1600+$user_uid));
					$this->set_due_date($due_date);
					//$this->set_amount($price);
					$this->set_start_dts($start);
					$this->set_expires_dts($expires);
					$this->set_invoice_for('school');
					$this->set_vat((isset($priceArray['vat'])?$priceArray['vat']:0));

					$this->set_date_paid($date_paid);
					$this->set_verified($verified);
					$this->set_verified_dts($verified_dts);
					$this->set_package_token($package_token);
					return $this->insert();
				}
			}
		}
	}

	private function isUserAlreadySubscribed($package_token=null,$user_uid=null) {
		if($package_token==null || $user_uid==null) {
			return false;
		} else {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`subscriptions` ";
			$query.="WHERE ";
			$query.="`package_token`='".$package_token."' ";
			$query.="AND ";
			$query.="`user_uid`='".$user_uid."' ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if($result && mysql_error()=='' && mysql_num_rows($result)) {
				return true;
			} else {
				return false;
			}
		}
	}

}

?>