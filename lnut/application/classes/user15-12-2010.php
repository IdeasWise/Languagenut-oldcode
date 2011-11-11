<?php

class user extends generic_object {

	private	$arrForm = array();

	public function	__construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function	get_next_auto_increment_value()	{

		$uid	= 0;
		$sql	= "SHOW	TABLE STATUS LIKE 'user'";
		$result	= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{

			$row	= mysql_fetch_assoc($result);
			$uid	= $row['Auto_increment'];

		}

		return $uid;
	}

	public static function getDistinctLocales()	{

		$response =	false;

		$sql = "SELECT DISTINCT	";
		$sql.= "`locale` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`locale` !=	''";

		$result		= database::query( $sql	);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{

			$response =	array ();

			while($row=mysql_fetch_assoc($result)) {
				$response[]	= $row['locale'];
			}
		}

		return $response;
	}

	// listing of the users
	public function	get_users($pageId =	'',$all	= false) {
		$parts = config::get('paths');

		$where = '';
		$userTypes = array (
			'school',
			'schooladmin',
			'schoolteacher',
			'student',
			'homeuser',
			'affiliate',
			'reseller'
		);
		$pag = '';
		if($pageId=='')	{
			$pag = $parts[count($parts)-1];
			// if(is_numeric($pag))
			if(isset($pag) && is_numeric($pag) && $pag > 0)	{
				$pageId	= $pag;
			} else {
				$pageId	= 1;
			}
		}

		// this	needs clean	up without @ and should	be checked first
		if(	in_array( strtolower( @$parts[2] ),	$userTypes ) ) {
			$where = "WHERE	FIND_IN_SET('".strtolower( $parts[2] )."',`user_type`)";
		}
		// parts[3]	should be checked before being used
		if(	isset($parts[3]) &&	!is_numeric($parts[3]) ) {
			$where .= "	and	`locale` = '".$parts[3]."'";
		}

		if($_SESSION['user']['admin'] != 1 && $where ==	'' && isset($_SESSION['user']['school_uid'])){
			$where = " where 1 = 1 ";
			if($_SESSION['user']['userRights'] == 'school')	{
				$where.= " AND (";
					$where.= "`uid`	IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schooladmin`	";
						$where.= "WHERE	";
						$where.= "`school_id` =	'".$_SESSION['user']['school_uid']."'";
					$where.= ")	OR `uid` IN	(";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schoolteacher` ";
						$where.= "WHERE	";
						$where.= "`school_id` =	'".$_SESSION['user']['school_uid']."'";
					$where.= ")	OR `uid` IN	(";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student`	";
						$where.= "WHERE	";
						$where.= "`school_id` =	'".$_SESSION['user']['school_uid']."'";
					$where.= ")";
				$where.= ")";
			}

			if($_SESSION['user']['userRights'] == 'schooladmin'	&& isset($_SESSION['user']['school_uid'])) {
				$where.= " AND (";
					$where.= "`uid`	IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_schoolteacher` ";
						$where.= "WHERE	";
						$where.= "`school_id` =	'".$_SESSION['user']['school_uid']."'";
					$where.= ")	OR `uid` IN	(";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student`	";
						$where.= "WHERE	";
						$where.= "`school_id` =	'".$_SESSION['user']['school_uid']."'";
					$where.= ")";
				$where.= ")";
			}

			if($_SESSION['user']['userRights'] == 'schoolteacher' && isset($_SESSION['user']['school_uid'])){
				$where .= "	AND	(";
					$where.= "`uid`	IN (";
						$where.= "SELECT ";
						$where.= "`iuser_uid` ";
						$where.= "FROM ";
						$where.= "`profile_student`	";
						$where.= "WHERE	";
						$where.= "`school_id` =	'".$_SESSION['user']['school_uid']."'";
					$where.= ")";
				$where.= ")";
			}
		}
		
		if($where != '')
			$where .= "  and deleted != '1' ";
        else
			$where = " where deleted != '1' ";

		if($all	== false) {
			$sql = 'SELECT ';
			$sql.= 'COUNT(`uid`) ';
			$sql.= 'FROM ';
			$sql.= '`user` '.$where;

			if(isset($_SESSION['user']['admin']) &&	$_SESSION['user']['admin'] != 1) {

				if(isset($parts[2])	&& strtolower( @$parts[2] )	== 'schooladmin'){
					$sql = "SELECT ";
					$sql.= "COUNT(`U`.`uid`) ";
					$sql.= "FROM ";
					$sql.= "`user` AS `U`,";
					$sql.= "`profile_schooladmin` AS `P` ";
					$sql.= "WHERE `U`.`deleted` != '1' and ";
					$sql.= "FIND_IN_SET('schooladmin',`user_type`) ";
					$sql.= "AND	`P`.`iuser_uid`	= `U`.`uid`	";
					$sql.= "AND	`P`.`school_id`	= '".$_SESSION['user']['school_uid']."'";
				}

				if(isset($parts[2])	&& strtolower( @$parts[2] )	== 'schoolteacher' && isset($_SESSION['user']['school_uid'])) {
					$sql = "SELECT ";
					$sql.= "COUNT(`U`.`uid`) ";
					$sql.= "FROM ";
					$sql.= "`user` AS `U`,";
					$sql.= "`profile_schoolteacher`	AS `P` ";
					$sql.= "WHERE `U`.`deleted` != '1' and ";
					$sql.= "FIND_IN_SET('schoolteacher',`user_type`) ";
					$sql.= "AND	`P`.`iuser_uid`	= `U`.`uid`	";
					$sql.= "AND	`P`.`school_id`	= '".$_SESSION['user']['school_uid']."'";
				}

				if(isset($parts[2])	&& strtolower( @$parts[2] )	== 'student' &&	isset($_SESSION['user']['school_uid']))	{
					$sql = "SELECT ";
					$sql.= "COUNT(`U`.`uid`) ";
					$sql.= "FROM ";
					$sql.= "`user` AS `U`,";
					$sql.= "`profile_student` AS `P` ";
					$sql.= "WHERE `U`.`deleted` != '1' and ";
					$sql.= "FIND_IN_SET('student',`user_type`) ";
					$sql.= "AND	`P`.`iuser_uid`	= `U`.`uid`	";
					$sql.= "AND	`P`.`school_id`	= '".$_SESSION['user']['school_uid']."'";
				}
			}
			
			$result	= database::query($sql);
			$max = 0;
			if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
				$row = mysql_fetch_array($result);
				$max = $row[0];
			}
			

			$this->pager(
				$max,						//see above
				config::get("pagesize"),	//how many records to display at one time
				$pageId,
				array("php_self" =>	"")
			);
			$this->set_range(10);

			if(isset($parts[2])	&& strtolower( @$parts[2] )	== 'school') {
				$where .=" AND `U`.`uid` = `SC`.`user_uid`";
				$sql = "SELECT ";
				$sql.= "`U`.*, ";
				$sql.= "`SC`.`school` ";
				$sql.= "FROM ";
				$sql.= "`user` AS `U`, ";
				$sql.= "`users_schools`	AS `SC`	";
				$sql.= $where;
				$sql.= " ORDER BY ";
				$sql.= "`registered_dts` DESC ";
				$sql.= "LIMIT ".$this->get_limit();
			} else {
				$sql = "SELECT ";
				$sql.= "* ";
				$sql.= "FROM ";
				$sql.= "`user` ".$where." ";
				$sql.= "ORDER BY ";
				$sql.= "`registered_dts` DESC ";
				$sql.= "LIMIT ".$this->get_limit();
			}

			if($_SESSION['user']['admin'] != 1)	{
				if(isset($parts[2])	&& strtolower( @$parts[2] )	== 'schooladmin' &&	isset($_SESSION['user']['school_uid']))	{
					$sql = "SELECT ";
					$sql.= "`U`.* ";
					$sql.= "FROM ";
					$sql.= "`user` AS `U`,";
					$sql.= "`profile_schooladmin` AS `P` ";
					$sql.= "WHERE `U`.`deleted` != '1' and ";
					$sql.= "FIND_IN_SET('schooladmin',`user_type`) ";
					$sql.= "AND	`P`.`iuser_uid`	= `U`.`uid`	";
					$sql.= "AND	`P`.`school_id`	= '".$_SESSION['user']['school_uid']."'	";
					$sql.= "ORDER BY ";
					$sql.= "`registered_dts` DESC ";
					$sql.= "LIMIT ".$this->get_limit();
				}

				if(isset($parts[2])	&& strtolower( $parts[2] ) == 'schoolteacher' && isset($_SESSION['user']['school_uid'])) {
					$sql = "SELECT ";
					$sql.= "`U`.* ";
					$sql.= "FROM ";
					$sql.= "`user` AS `U`,";
					$sql.= "`profile_schoolteacher`	AS `P` ";
					$sql.= "WHERE `U`.`deleted` != '1' and ";
					$sql.= "FIND_IN_SET('schoolteacher',`user_type`) ";
					$sql.= "AND	`P`.`iuser_uid`	= `U`.`uid`	";
					$sql.= "AND	`P`.`school_id`	= '".$_SESSION['user']['school_uid']."'	";
					$sql.= "ORDER BY ";
					$sql.= "`registered_dts` DESC ";
					$sql.= "LIMIT ".$this->get_limit();
				}

				if(isset($parts[2])	&& strtolower( @$parts[2] )	== 'student' &&	isset($_SESSION['user']['school_uid']))	{
					$sql = "SELECT ";
					$sql.= "`U`.* ";
					$sql.= "FROM ";
					$sql.= "`user` AS `U`,";
					$sql.= "`profile_student` AS `P` ";
					$sql.= "WHERE `U`.`deleted` != '1' and ";
					$sql.= "FIND_IN_SET('student',`user_type`) ";
					$sql.= "AND	`P`.`iuser_uid`	= `U`.`uid`	";
					$sql.= "AND	`P`.`school_id`	= '".$_SESSION['user']['school_uid']."'	";
					$sql.= "ORDER BY ";
					$sql.= "`registered_dts` DESC ";
					$sql.= "LIMIT ".$this->get_limit();
				}
			}

			$result	= database::query( $sql	);
		} else {
			$result	= database::query("SELECT *	FROM `user`	".$where." ORDER BY	`registered_dts` DESC");
		}

		$this->data	= array();

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			while($row=mysql_fetch_assoc($result)) {
				$this->data[] =	$row;
			}
		}
		return $this->data;
	}

	public function	isCreateSuccessful() {

		$insert_id	= 0;
		$response	= $this->isValidData();

		if(!empty ($response) && $response[0] == false)	{

			$insert_id		= $this->insertFromAdmin();
			$response[1][]	= "User	Added Successfully";

		}

		$message_type	= ($response[0]	== true)?"error":"success";

		return array($message_type,$response[1],$insert_id);

	}

	public function	insertFromAdmin() {

		$insert_id	= null;
		$email		= mysql_real_escape_string($this->arrFields['email']['Value']);
		$password	= mysql_real_escape_string(md5($this->arrFields['password']['Value']));
		$referral	= mysql_real_escape_string($_POST['referral']);

		$sql = "INSERT INTO	`user` (";
			$sql.= "`registered_dts`, ";
			$sql.= "`email`, ";
			$sql.= "`password`,	";
			$sql.= "`access_allowed`, ";
			$sql.= "`deleted`, ";
			$sql.= "`registration_ip`, ";
			$sql.= "`registration_key`,	";
			$sql.= "`allow_access_without_sub`,	";
			$sql.= "`optin`, ";
			$sql.= "`referral`,	";
			$sql.= "`verified_dts`,	";
			$sql.= "`locale`";
		$sql.= ") VALUES (";
			$sql.= "NOW(), ";
			$sql.= "'{$email}',	";
			$sql.= "'{$password}', ";
			$sql.= "'{$this->arrFields['access_allowed']['Value']}', ";
			$sql.= "'{$this->arrFields['deleted']['Value']}', ";
			$sql.= "'".$_SERVER['REMOTE_ADDR']."', ";
			$sql.= "'".session_id()."',	";
			$sql.= "'".$_POST['allow_access_without_sub']."', ";
			$sql.= "'".$_POST['optin']."', ";
			$sql.= "'".addslashes($referral)."', ";
			$sql.= "NOW(), ";
			$sql.= "'".$_POST['locale']."'";
		$sql.= ")";

		$result	= database::query($sql);

		if($result && mysql_error()=='') {
			$insert_id	= mysql_insert_id();
		}

		$this->SetRegistrationKey( $insert_id, $_SERVER['REMOTE_ADDR'] );
		return $insert_id;
	}

	public function	isUpdateSuccessFul() {

		$response	= $this->isValidData(true);
		$insert_id	= 0;

		if(!empty ($response) && $response[0] == false)	{
			$this->save();
			$insert_id		= $this->get_uid();
			//$this->insertChangeInTransaction($insert_id);
			$response[1][]	= "User	Updated	Successfully";
		}

		$message_type =	($response[0] == true)?"error":"success";

		return array($message_type,$response[1],$insert_id);

	}

	public function	insertChangeInTransaction($row_uid = 0)	{
		if(!empty($this->arrForm)	&& is_numeric($row_uid)	&& $row_uid	> 0) {

			$objUT = new user_transaction();

			foreach($this->arrForm as	$key =>	$val) {
				$objUT->set_row_uid($row_uid);
				$objUT->set_field_name($key);
				$objUT->set_field_value_was($val);
				$objUT->set_field_updated_dts(date("Y-m-d H:i:s"));
				$objUT->set_changed_by_user_uid($_SESSION['user']['uid']);
				$objUT->set_changed_by_user_type(1);
				$objUT->set_session_uid(session_id());
				$objUT->set_changed_by_ip_address($_SERVER['REMOTE_ADDR']);
				$objUT->insert_transaction();
			}
			$objUT->commit_transaction();
			unset($this->arrForm);
		}
	}

	protected function isValidData($update = false)	{
		$user_uid			= (isset($_POST['user_uid'])	  ?	format::to_integer($_POST['user_uid']) : '0');
		$email				= (isset($_POST['email'])		  ?	format::to_string($_POST['email']) : '');
		$password			= (isset($_POST['password'])	  ?	format::to_string($_POST['password']) :	'');
		$conf_password		= (isset($_POST['conf_password']) ?	format::to_string($_POST['conf_password']) : '');
		$allow_access		= (isset($_POST['allow_access'])  ?	format::to_integer($_POST['allow_access']) : '');
		$is_admin			= (isset($_POST['is_admin'])	  ?	format::to_integer($_POST['is_admin']) : '');
		$is_deleted			= (isset($_POST['deleted'])		  ?	format::to_integer($_POST['deleted']) :	'');

		$error				= false;
		$message			= array();

		if(is_numeric($user_uid) &&	$user_uid >	0) {
			parent::__construct($user_uid,__CLASS__);
			$this->load();
		}

		if(strlen($email) <= 0 || strlen($email) > 255)	{

			$error = true;
			$message['email_error']	= "Please Provide Valid	Email";

		} else if($this->email_exist($email)) {

			$error = true;
			$message['email_error']	= "Email Already Exist";

		}

		if($update == false) {
			if(strlen($password) <=	0 || strlen($password) > 10) {
				$error =   true;
				$message['pass_error'] = "Please Provide Password";
			} else if($password	!= $conf_password) {
				$error =   true;
				$message['cpass_error']	= "Password	and	confirm	Password do	not	match";
			}
		} else if($password	!= $conf_password) {
			$error = true;
			$message['cpass_error']	= "Password	and	confirm	Password do	not	match";
		}

		if(isset($_POST['user_type']) && $_POST['user_type'] ==	'')	{
			$error =   true;
			$message['user_type_error']	= "Please seelect user type.";
		}

		if(isset($_POST['locale']) && $_POST['locale'] == '') {
			$error =   true;
			$message['locale_error'] = "Please seelect locale.";
		}

		if(!$error)	{

			// get the old values to store in the transaction
			if($update)	{
				if($this->get_email() != $email) {
					$this->arrForm['email'] =	$this->get_email();;
				}
				if($this->get_access_allowed() != (int)$allow_access) {
					$this->arrForm['access_allowed'] = $this->get_access_allowed();
				}
				if($this->get_is_admin() !=	(int)$is_admin)	{
					$this->arrForm['is_admin'] = $this->get_is_admin();
				}
				if($this->get_deleted()	!= (int)$is_deleted) {
					$this->arrForm['deleted']	= $this->get_deleted();
				}
			}

			$this->set_email($email);
			if($password !=	"")	{
				$this->set_password(md5($password));
			}
			$this->set_access_allowed($allow_access);
			$this->set_is_admin($is_admin);
			$this->set_deleted($is_deleted);
			$this->set_allow_access_without_sub($_POST['allow_access_without_sub']);
			$this->set_optin($_POST['optin']);
			$this->set_referral($_POST['referral']);
			$this->set_locale($_POST['locale']);

			if(isset($_POST['user_type'])) {
				$this->set_user_type($_POST['user_type']);
			}
		}
		return array($error,$message);
	}

	public function	isAdmin() {
		return ($this->get_is_admin() == 1)	? true : false;
	}
	
	public function	userRedirectUrl() {
		if(isset($_SESSION['user'])	&& isset($_SESSION['user']['admin']) &&	$_SESSION['user']['admin'] == 1) {
			return "admin";
		} else if(isset($_SESSION['user']) && isset($_SESSION['user']['user_type'])	&& is_array($_SESSION['user']['user_type'])	) {
			if(	in_array('student',$_SESSION['user']['user_type']) || in_array('homeuser',$_SESSION['user']['user_type']) )	{
				return "flash";
			} else if($_SESSION['user']['ByOpenUserName'] == 1){
				return 'flash';
			} else {
				return "account";
			}
		}
	}

	public function	isUser() {
		return $this->get_active();
	}

	public static function isLoggedIn()	{
		$response =	false;
		if(isset($_SESSION['user'])	&& isset($_SESSION['user']['uid']) && $_SESSION['user']['uid'] > 0)	{
			$response =	true;
		}
		return $response;
	}

	public function	email_exist($email = "") {

		$found	= false;
		$email	= mysql_real_escape_string($email);

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`email`	= '$email' ";

		if($this->get_uid()	!= null) {
			$sql .=	" AND `uid`	!= '{$this->get_uid()}'";
		}
		$sql .=	" LIMIT	1";

		$result	= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$found = true;
		}

		return $found;
	}

	public function	username_exist($username_open =	"")	{

		$found = false;
		$username_open = mysql_real_escape_string($username_open);

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`username_open`	= '$username_open' ";

		if($this->get_uid()	!= null) {
			$sql .=	" AND `uid`	!= '{$this->get_uid()}'";
		}

		$sql .=" LIMIT 1";

		$result	=	database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$found = true;
		}

		return $found;
	}

	public function	SubscribeSave()	{

		/**
		 * NOTE: change	$this->arrFields['key']['Value'] = 'something';
		 *		to $this->set_$key('something');
		 */

		$response =	false;

		$message = '';
		$errors	= array	();

		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1']	: array	();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2']	: array	();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3']	: array	();

		if(count($form1) > 0 &&	count($form2) >	0 && count($form3) > 0)	{
			/**
			 * Add user	to database	and	add	to subscriptions if	necessary too
			 */
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
			$registration_key	= md5($form2['school_name']['value'].$ip_address);

			$paths = config::get('paths');

			/* Set values to user table	fields */
			$this->arrFields['registered_dts']['Value']		= date('Y-m-d H:i:s',$time);
			$this->arrFields['registration_ip']['Value']	= $ip_address;
			$this->arrFields['registration_key']['Value']	= $registration_key;
			$this->arrFields['affiliate']['Value']			= mysql_real_escape_string(@$_SESSION['aff']);
			$this->arrFields['email']['Value']				= mysql_real_escape_string($form1['email']['value']);

			if(isset($form3['username_open']['value']) && $form3['username_open']['value'] != '') {
				$this->arrFields['username_open']['Value'] = mysql_real_escape_string($form3['username_open']['value']);
			}

			$this->arrFields['password']['Value']			= md5($form1['password']['value']);
			$this->arrFields['password_open']['Value']		= mysql_real_escape_string($form3['password_open']['value']);

			/*
			if(isset($form3['password_open']['value']) && $form3['password_open']['value'] != '')
			$this->arrFields['password']['Value'] =	md5($form1['password']['value']);
			$this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);
			 * 
			 */

			$this->arrFields['access_allowed']['Value']		= 1;
			$this->arrFields['allow_access_without_sub']['Value'] =	1;
			$this->arrFields['active']['Value']				= 1;
			$this->arrFields['locale']['Value']				= mysql_real_escape_string(config::get('locale'));
			$this->arrFields['user_type']['Value']			= mysql_real_escape_string(@$paths[1]);		 
			
			/* Set values to user table	fields END */
			// insert record to	table.
			$response =	$this->insert();
			
			// the following function will set registration key and that we'll use to cancel account
			$this->SetRegistrationKey( $response, $ip_address );
		}

		return $response;

	}

	public function	SubscribeSaveHomeUser()	{
		/**
		 * Fix calls to	$this->arrFields - to use $this->set_fieldname($value);	instead
		 */
		$response = false;
		$message = '';
		$errors	= array	();

		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1']	: array	();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2']	: array	();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3']	: array	();

		if(count($form1) > 0) {	
			/**
			 * Add user	to database	and	add	to subscriptions if	necessary too
			 */
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
			$registration_key	= md5($form1['email']['value'].$ip_address);

			/* Set values to user table	fields */
			$this->arrFields['registered_dts']['Value']	= date('Y-m-d H:i:s',$time);
			$this->arrFields['registration_ip']['Value'] = $ip_address;
			$this->arrFields['registration_key']['Value'] =	$registration_key;
			$this->arrFields['affiliate']['Value'] = mysql_real_escape_string(@$_SESSION['aff']);
			$this->arrFields['email']['Value'] = mysql_real_escape_string($form1['email']['value']);

			if(isset($form3['username_open']['value']) && $form3['username_open']['value'] != '')
				$this->arrFields['username_open']['Value'] = mysql_real_escape_string($form3['username_open']['value']);

			$this->arrFields['password']['Value'] =	md5($form1['password']['value']);
			$this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);

			/*
			if(isset($form3['password_open']['value']) && $form3['password_open']['value'] != '')
			$this->arrFields['password']['Value'] =	md5($form1['password']['value']);
			$this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);
			 *
			 */

			$this->arrFields['allow_access_without_sub']['Value'] =	1;
			$this->arrFields['active']['Value']	= 1;
			$this->arrFields['locale']['Value']	= mysql_real_escape_string(config::get('locale'));
			$paths = config::get('paths');
			$this->arrFields['user_type']['Value'] = mysql_real_escape_string(@$paths[1]);

			/* Set values to user table	fields END */
			// insert record to	table.
			$response = $this->insert();
			
			// the following function will set registration key and that we'll use to cancel account
			$this->SetRegistrationKey( $response, $ip_address );
			return $response;
		}
	}
	
	public function CreateStudentUser( $username, $password, $locale ) {
		$response = false;
		$time				= time();
		$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
		$this->arrFields['registered_dts']['Value']	= date('Y-m-d H:i:s',$time);
		$this->arrFields['registration_ip']['Value'] = $ip_address;
//		$this->arrFields['email']['Value']			= mysql_real_escape_string($username);
		$this->arrFields['password']['Value']		= md5($password);
		$this->arrFields['access_allowed']['Value']	= 1;
		$this->arrFields['allow_access_without_sub']['Value'] =	1;
		$this->arrFields['active']['Value']			= 1;
		$this->arrFields['locale']['Value']			= mysql_real_escape_string($locale);
		$this->arrFields['user_type']['Value']			= 'student';	
		$response = $this->insert();
		$this->SetRegistrationKey( $response, $ip_address );
		$this->SetUserName( $response, $username );
		return $response;
		
	}
	
	public function SetUserName( $uid, $username ) {
		$email = $username.$uid;
		$sql = "UPDATE `user` SET ";
		$sql .="`email` = '".strtolower(mysql_real_escape_string($email))."' ";
		$sql .="WHERE `uid` = '".$uid."'";
		database::query($sql);
	}
	
	public function SetRegistrationKey( $uid, $ip_address ) {
		$md5 = md5( $uid .'-'. $ip_address );
		$sql = "UPDATE `user` SET ";
		$sql .="`registration_key` = '".$md5."' ";
		$sql .="WHERE `uid` = '".$uid."'";
		database::query($sql);
	}


	public static function getUserByEmailAddress($email	= "") {

		$user_uid	= false;
		$email		= mysql_real_escape_string($email);

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`email`	= '$email' ";
		$sql.= "LIMIT 1";

		$result		= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$row		= mysql_fetch_assoc($result);
			$user_uid	= $row['uid'];
		}

		return $user_uid;
	}

	public static function getUserByOpenUserName($username_open	= "") {

		$user_uid		= false;
		$username_open	= mysql_real_escape_string($username_open);

		$sql = "SELECT ";
		$sql.= "`uid` ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`username_open`	= '$username_open' ";
		$sql.= "LIMIT 1";

		$result		= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$row		= mysql_fetch_assoc($result);
			$user_uid	= $row['uid'];
		}

		return $user_uid;
	}

	// operations to perform
	public function	isValidLogin() {
		$response =	array (
			'fields'=>array	(
				'email'		=> array (
					'default'	=> 'Email Address',
					'message'	=> '',
					'highlight'	=> false,
					'error'		=> false,
					'value'		=> ''
				),
				'password'		=> array (
					'default'	=> '',
					'message'	=> '',
					'error'		=> false,
					'highlight'	=> false
				)
			),
			'message' => ''
		);

		$user_uid	= 0;
		$error		= false;

		// validation start	here
		if(validation::isPresent('email',$_POST)) {
			if(validation::isValid('text',$_POST['email']))	{
				if(	 (($user_uid  =	self::getUserByEmailAddress($_POST['email'])) === false) &&	(($user_uid	 = self::getUserByOpenUserName($_POST['email'])) === false)	 ) { 
					$response['fields']['email']['message']		= "The details you have	entered	do not match our records.";
					$response['fields']['email']['error']		= true;
					$response['fields']['email']['highlight']	= true;
				} else {
					$response['fields']['email']['value']		= $_POST['email'];
				}
			} else {
				$response['fields']['email']['message']			= 'Please Enter	a valid	email address';
				$response['fields']['email']['error']			= true;
				$response['fields']['email']['highlight']		= true;
			}
		} else {
			$response['fields']['email']['message']				= 'Email address is	requried';
			$response['fields']['email']['error']				= true;
			$response['fields']['email']['highlight']			= true;
		}

		if(validation::isPresent('password',$_POST)) {
			if(!validation::isValid('text',$_POST['password']))	{
				$response['fields']['password']['message']	= 'Please Enter	a valid	password';
				$response['fields']['password']['error']	= true;
				$response['fields']['password']['highlight']= true;
			} else {
			}
		} else {
			$response['fields']['password']['message']		= 'Password	is requried';
			$response['fields']['password']['error']		= true;
			$response['fields']['password']['highlight']	= true;
		}
		if(is_numeric($user_uid) &&	$user_uid >	0) {

			parent::__construct($user_uid);
			$this->load();


			$oldPassword = $this->get_password();
			$oldOpenPassword = $this->get_password_open();

			if($this->get_access_allowed() == 0) {
				$response['message'] = 'The	details	you	have entered do	not	match our records.';
			} else if(self::getUserByOpenUserName($_POST['email']) === $user_uid) {
				if($_POST['password'] != $oldOpenPassword) {
					$response['message'] = 'The	details	you	have entered do	not	match our records.';
				}
			} else if(self::getUserByEmailAddress($_POST['email']) === $user_uid) {
				if(md5($_POST['password']) != $oldPassword)	{
					$response['message'] = 'The	details	you	have entered do	not	match our records.';
				}
			}
			if($this->get_deleted()	== 1) {
				$response['message'] = 'The	details	you	have entered do	not	match our records.';
			}
			if($this->get_is_admin() ==	0){
				if($this->has_active_subscription()	== false) {
					$response['message']	=	"Your subscription period is expire	please renew now.";
				}
			}
		} else {
		}		

		if(count($response['fields']) >	0) {
			foreach($response['fields']	as $key	=> $data) {
				if($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}
		if($response['message']	!= "") {
			$error = true;
		}

		if(!$error)	{
			return true;
		} else {
			return $response;
		}
	}

	public function	has_active_subscription() {

		$return		= false;
		$user_uid	= $this->getSchoolId(); 

		if($user_uid ==	0) {
			return false;
		}

		$objSubscriptions =	new	subscriptions();
		$objSubscriptions->load(array(), array('user_uid'=>	$user_uid));

		$now = time();

		if(	strtotime($objSubscriptions->TableData['due_date']['Value']) > $now) {
			$return	=  true;
		}

		if(	strtotime($objSubscriptions->TableData['expires_dts']['Value'])	> $now)	{ // fix all verified dates
			$return	= true;
		}

		return $return;
	}

	public function	isValidRegister() {
		$response =	array (
			'fields'=>array	(
				'email'				=> array (
					'default'	=> 'Email Address',
					'message'	=> '',
					'highlight'	=> false,
					'error'		=> false,
					'value'		=> ''
				),
				'password'			=> array (
					'default'	=> '',
					'message'	=> '',
					'error'		=> false,
					'highlight'	=> false
				),
				'confirm_password'	=> array (
					'default'	=> '',
					'message'	=> '',
					'error'		=> false,
					'highlight'	=> false
				)
			),
			'message' => ''
		);

		$user_uid	= 0;
		$error		= false;

		// validation start	here
		if(validation::isPresent('email',$_POST)) {
			if(validation::isValid('text',$_POST['email'])) {
				if(($user_uid  = self::getUserByEmailAddress($_POST['email'])) !== false) {
					$response['fields']['email']['message']		= "Email Address is	not	available";
					$response['fields']['email']['error']		= true;
					$response['fields']['email']['highlight']	= true;
				} else {
					$response['fields']['email']['value']		= $_POST['email'];
					$this->set_email($_POST['email']);
				}
			} else {
				$response['fields']['email']['message']			= 'Please Enter	a valid	email address';
				$response['fields']['email']['error']			= true;
				$response['fields']['email']['highlight']		= true;
			}
		} else {
			$response['fields']['email']['message']				= 'Email address is	requried';
			$response['fields']['email']['error']				= true;
			$response['fields']['email']['highlight']			= true;
		}

		if(validation::isPresent('password',$_POST)) {
			if(!validation::isValid('text',$_POST['password']))	{
				$response['fields']['password']['message']	= 'Please Enter	a valid	password';
				$response['fields']['password']['error']	= true;
				$response['fields']['password']['highlight']= true;
			} else {
				$this->set_password($_POST['password']);
			}
		} else {
			$response['fields']['password']['message']		= 'Password	is requried';
			$response['fields']['password']['error']		= true;
			$response['fields']['password']['highlight']	= true;
		}

		if(validation::isPresent('confirm_password',$_POST)) {
			if(!validation::isValid('text',$_POST['confirm_password']))	{
				$response['fields']['confirm_password']['message']	= 'Please Enter	a valid	Confirm	password';
				$response['fields']['confirm_password']['error']	= true;
				$response['fields']['confirm_password']['highlight']= true;
			} else if($_POST['confirm_password'] !=	$_POST['password'])	{
				$response['fields']['confirm_password']['message']	= 'Password	and	Confirm	password do	not	match';
				$response['fields']['confirm_password']['error']	= true;
				$response['fields']['confirm_password']['highlight']= true;
			}
		} else {
			$response['fields']['confirm_password']['message']		= 'Confirm Password	is requried';
			$response['fields']['confirm_password']['error']		= true;
			$response['fields']['confirm_password']['highlight']	= true;
		}		
		if(count($response['fields']) >	0) {
			foreach($response['fields']	as $key	=> $data) {
				if($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}

		if(!$error)	{
			return true;
		} else {
			return $response;
		}
	}

	/**
	 * DEPRECATED?
	 */
	public function	isValidLink($link =	"")	{
		$valid					= false;
		$user_registration_uid	= 0;

		if($link !=	"")	{
			if(($user_registration_uid = user_registration::ket_exists($link)) !== false) {
				$user_registration	= new user_registration($user_registration_uid);
				if($user_registration->get_valid())	{
					$user_registration->load();
					parent::__construct($user_registration->get_user_uid(),__CLASS__);
					$this->load();
					$user_registration->delete();
					$valid = true;
				}
			}
		}
		return $valid;
	}

	public function	login($returnUrl = false) {

		$_SESSION['user']['uid']			= $this->get_uid();
		$_SESSION['user']['email']			= $this->get_email();
		$_SESSION['user']['type']			= $this->get_is_admin();
		$_SESSION['user']['admin']			= $this->get_is_admin();
		$_SESSION['user']['user_type']		= explode(',',@$this->get_user_type());
		$_SESSION['user']['prefix']			= $this->get_locale();
		$_SESSION['user']['logged_in']		= true;
		$_SESSION['user']['school_uid']		= $this->getSchoolIdForAccount();
		$_SESSION['user']['ByOpenUserName']	= 0;
		$_SESSION['user']['defaultPage']	= 'admin/users/school/';
		
		if(is_array(@$_SESSION['user']['user_type']) &&	$this->get_is_admin() != 1)	{
			$_SESSION['user']['defaultPage'] = 'account/users/student/';
			$_SESSION['user']['defaultMenu'] = 'menu.schoolteacher.user';
			$_SESSION['user']['userRights']	= 'student';

			if(	in_array('school',@$_SESSION['user']['user_type']) ) {
				$_SESSION['user']['defaultPage'] = 'account/users/schooladmin/';
				$_SESSION['user']['defaultMenu'] = 'menu.school.user';
				$_SESSION['user']['userRights']	= 'school';
			} else if( in_array('schooladmin',@$_SESSION['user']['user_type']) ) {
				$_SESSION['user']['defaultPage'] = 'account/users/schoolteacher/';
				$_SESSION['user']['defaultMenu'] = 'menu.schooladmin.user';
				$_SESSION['user']['userRights']	= 'schooladmin';
			} else if( in_array('schoolteacher',@$_SESSION['user']['user_type']) ){
				$_SESSION['user']['defaultPage'] = 'account/classes/list/';
				$_SESSION['user']['defaultMenu'] = 'menu.schoolteacher.user';
				$_SESSION['user']['userRights']	= 'schoolteacher';
			}
		}

		if(self::getUserByOpenUserName($_POST['email'])	===	$this->get_uid()) {
			if($_POST['password'] == $this->get_password_open()) {
				$_SESSION['user']['ByOpenUserName']	= 1;
			}
		}

		if($returnUrl == true) {
			return $this->userRedirectUrl();
		} else {
			$this->redirectTo($this->userRedirectUrl());
		}
	}

	public function	getSchoolId() {

		$userType =	null;
		$userType =	explode(',',@$this->get_user_type());

		if(	is_array( $userType	) ){
			if(	in_array( 'school',	$userType )	) {

				return $this->get_uid();

			} elseif( in_array(	'schooladmin', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`S`.`user_uid` ";
				$sql.= "FROM ";
				$sql.= "`profile_schooladmin` AS `T`, ";
				$sql.= "`users_schools`	AS `S` ";
				$sql.= "WHERE ";
				$sql.= "`T`.`school_id`	= `S`.`uid`	";
				$sql.= "AND	`T`.`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['user_uid'];
				}

			} elseif( in_array(	'schoolteacher', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`S`.`user_uid` ";
				$sql.= "FROM ";
				$sql.= "`profile_schoolteacher`	AS `T`,	";
				$sql.= "`users_schools`	AS `S` ";
				$sql.= "WHERE ";
				$sql.= "`T`.`school_id`	= `S`.`uid`	";
				$sql.= "AND	`T`.`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['user_uid'];
				}

			}  elseif( in_array('student', $userType ) ) {

				$sql = "SELECT ";
				$sql.= "`S`.`user_uid` ";
				$sql.= "FROM ";
				$sql.= "`profile_student`	AS `PT`,	";
				$sql.= "`users_schools`	AS `S` ";
				$sql.= "WHERE ";
				$sql.= "`PT`.`school_id`	= `S`.`uid`	";
				$sql.= "AND `PT`.`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['user_uid'];
				}

			} else if( in_array( 'homeuser', $userType ) ) {
				return $this->get_uid();
			}
		}

		return 0;
	}

	public function	getSchoolIdForAccount()	{

		$userType =	explode(',',@$this->get_user_type());

		if(	is_array( $userType	) ){
			if(	in_array( 'school',	$userType )	) {

				$sql = "SELECT ";
				$sql.= "`uid` ";
				$sql.= "FROM ";
				$sql.= "`users_schools`	";
				$sql.= "WHERE ";
				$sql.= "`user_uid` = '".$this->get_uid()."'	";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['uid'];
				}

			} else if( in_array( 'schooladmin',	$userType )	) {

				$sql = "SELECT ";
				$sql.= "`school_id`	";
				$sql.= "FROM ";
				$sql.= "`profile_schooladmin` ";
				$sql.= "WHERE ";
				$sql.= "`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['school_id'];
				}
			} else if( in_array( 'schoolteacher', $userType	) )	{

				$sql = "SELECT ";
				$sql.= "`school_id`	";
				$sql.= "FROM ";
				$sql.= "`profile_schoolteacher`	";
				$sql.= "WHERE ";
				$sql.= "`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['school_id'];
				}

			}
			else if( in_array( 'student', $userType	) )	{

				$sql = "SELECT ";
				$sql.= "`school_id`	";
				$sql.= "FROM ";
				$sql.= "`profile_student`	";
				$sql.= "WHERE ";
				$sql.= "`iuser_uid`	= '".$this->get_uid()."' ";
				$sql.= "LIMIT 0,1";

				$result	= database::query($sql);

				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					return $row['school_id'];
				}

			}
		}
		return 0;
	}

	public function	register($sendEmail	= true)	{
		$success = false;
		$this->set_registered_dts(date("Y-m-d H:i:s"));

		if($sendEmail) {
			$this->set_access_allowed(0);
		} else {
			$this->set_access_allowed(1);
		}

		$this->set_is_admin(0);
		$this->set_deleted(0);

		if(($insert_id = $this->insert()) !== false) {

			parent::__construct($insert_id,	__CLASS__);
			$this->load();
			$success = true;

			if($sendEmail) {
				$this->sendRegistrationEmail();
			}
			$this->login();
		}
		return $success;
	}

	public function	verify($sendEmail =	true) {
		if($this->get_access_allowed() != 1) {
			$this->arrForm['access_allowed']	= $this->get_access_allowed();
			$this->arrForm['verified_dts']	= $this->get_verified_dts();
			$this->set_access_allowed(1);
			$this->set_verified_dts(date("Y-m-d	H:i:s"));
			$this->save();
			$this->insertChangeInTransaction($this->get_uid());
			if($sendEmail) {
				$this->sendEmailWelcome();
			}
		}
	}

	public function	logout($redirect = false) {
		session_destroy();
		if($redirect) {
			output::redirect(config::url());
		}
	}

	public function	passwordStrength() {

	}

	public function	enable($sendEmail =	true) {
		if($this->get_allow_access() !=	1) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['access_allowed']['Value']	= 1;
		$this->save();
		if($sendEmail) {
			$this->sendEmailEnabled();
		}
	}

	public function	disable($sendEmail = true) {
		if($this->get_allow_access() !=	0) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['access_allowed']['Value']	= 0;
		$this->save();
		if($sendEmail) {
			$this->sendEmailDisabled();
		}
	}

	public function	changePassword($password = "",$sendEmail=false)	{
		if($this->get_password() !=	md5($password))	{
			$this->arrForm['password'] = $this->get_password();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['password']['Value'] =	md5($password);
		$this->save();
		if($sendEmail) {
			$this->sendEmailPasswordChanged();
		}
	}

	public function	getUsernamesMatching($pattern =	'',$excludeUsers=array()) {

		$arrResponse = array ();

		$sql = "SELECT ";
		$sql.= "`open_username`	";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE ";
		$sql.= "`open_username`	LIKE '".$pattern."'	";
		if(count($excludeUsers)	> 0) {
			$sql.= "AND	`uid` NOT IN (".implode(',',$excludeUsers).") ";
		}
		$sql.= "ORDER BY ";
		$sql.= "`open_username`	ASC";

		$res = database::query($sql);

		if($res	&& mysql_error()=='' &&	mysql_num_rows($res) > 0) {
			while($row = mysql_fetch_assoc($res)) {
				$arrResponse[] = stripslashes($row['open_username']);
			}
		}

		return $arrResponse;

	}

	public function	updateUsername($arrUsernames=array()){
		$firstname		= $this->get_vfirstname();
		$lastname		= $this->get_vlastname();
		$open_username	= substr($firstname,0,1).substr($lastname,0,1);
		$number			= rand(0,10).rand(0,10).rand(0,10).rand(0,10);

		if(count($arrUsernames)	< 1) {
			$arrUsernames	= $this->getUsernamesMatching($open_username.'%', array($this->get_uid()));
		}

		if(count($arrUsernames)	> 0) {
			foreach($arrUsernames as $username)	{
				if($username ==	$open_username . $number) {
					$this->updateUsername($arrUsernames);
				}
			}
		}
		$open_username.=$number;

		// check if	open_username already exists
		if(!$this->username_exists($open_username))	{
			$this->set_open_username($username);
		} else {
			// shouldn't occur indefinitely	- but perhaps up to	10 times this could	repeat,	depending on the number	of people in the DB?
			$this->updateUsername();
		}
	}

	public function	changeEmail() {

	}

	public function	sendEmailWelcome() {

	}

	public function	sendEmailDisabled()	{

	}

	public function	sendEmailEnabled() {

	}

	public function	sendEmailPasswordChanged() {

	}

	public function	sendEmailChangedEmail()	{

	}

	public function	sendRegistrationEmail()	{
		$md5Id		= $this->getMD5registerLink();
		$insert		= $this->setUserRegistrationKey($md5Id);

		if($insert)	{
			$link	= config::url("login/verify/$md5Id/");
			$mail	= new email_phpmailer();
			//$mail->AddReplyTo("name@yourdomain.com","First Last");
			//$mail->SetFrom('name@yourdomain.com',	'First Last');
			//$mail->AddReplyTo("name@yourdomain.com","First Last");
			$body				= $link;
			$address			= $this->get_email();
			$mail->Subject		= "Create Account Verification Email";
			$mail->AltBody		= "To view the message,	please use an HTML compatible email	viewer."; // optional, comment out and test

			$mail->AddAddress($address,	"");
			$mail->MsgHTML($body);
			$mail->Send();
		}
	}

	public function	getMD5registerLink() {
		$email	= $this->get_email();
		$uid	= $this->get_uid();
		$md5Id	= md5($email."_".$uid);

		return $md5Id;
	}

	public function	setUserRegistrationKey($md5Id =	"")	{
		$insert		= false;
		$insert_id	= null;

		if($md5Id != "") {

			$user_registration = new user_registration();
			$user_registration->set_user_uid($this->get_uid());
			$user_registration->set_key($md5Id);
			$user_registration->set_created_dts(date("Y-m-d	H:i:s"));

			if(($insert_id = $user_registration->insert()) !== false) {
				$insert	= true;
			}
		}
		return $insert;
	}


	public function	getUserListByType($type, $where	= array()) {
		$and = '';
		foreach( $where	as $idx	=> $val	){
			$and .=	" AND `" . $idx	. "` = '" .	mysql_real_escape_string($val) . "'";
		}

		$sql = "SELECT ";
		$sql.= "* ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE FIND_IN_SET('".strtolower( $type )."',`user_type`) ";
		$sql.= $and;
		$sql.= " ORDER BY ";
		$sql.= "`registered_dts` DESC";

		$result	= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{

			$body =	new	xhtml('body.admin.users.type.list');
			$body->load();

			while($data	= mysql_fetch_assoc($result)) {
				$data['edit'] =	'profile/'.$type.'/';
				$panel = new xhtml('body.admin.users.list.row');
				$panel->load();
				$panel->assign($data);
				$page_rows[]	= $panel->get_content();
			}

			$body->assign('users.rows',	implode('',$page_rows));

			return $body->get_content();
		}
		return 'Users not found.';
	}

	public function	getUserListForSchoolByType($type , $school_id, $ref_table, $where =	array()) {
		$and = "AND	`uid` IN ( SELECT `uid`	FROM ".	$ref_table ." WHERE	`school_id`	= '".$school_id."')";
		foreach( $where	as $idx	=> $val	){
			$and .=	" AND `" . $idx	. "` = '" .	$val . "'";
		}

		$sql = "SELECT ";
		$sql.= "* ";
		$sql.= "FROM ";
		$sql.= "`user` ";
		$sql.= "WHERE FIND_IN_SET('".strtolower( $type )."',`user_type`) ";
		$sql.= $and;
		$sql.= " ORDER BY ";
		$sql.= "`registered_dts` DESC";

		$result	= database::query($sql);

		if($result && mysql_error()==''	&& mysql_num_rows($result) > 0)	{
			$body =	new	xhtml('body.admin.users.type.list');
			$body->load();

			while($data	= mysql_fetch_assoc($result)) {
				$data['edit'] =	'profile/'.$type.'/';
				$panel = new xhtml('body.admin.users.list.row');
				$panel->load();
				$panel->assign($data);
				$page_rows[] = $panel->get_content();
			}

			$body->assign('users.rows',	implode('',$page_rows));

			return $body->get_content();
		}
		return 'Users not found.';
	}
}

?>