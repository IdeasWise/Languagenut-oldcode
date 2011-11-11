<?php

class user extends generic_object {

	private $arrForm = array();

	public function __construct($uid = 0) {
		parent::__construct($uid, __CLASS__);
	}

	public function get_next_auto_increment_value() {
		$uid = 0;
		$sql = "SHOW TABLE STATUS LIKE 'user'";
		$result = database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$uid = $row['Auto_increment'];
		}
		return $uid;
	}

	public static function getDistinctLocales() {

		$response = false;

		$sql		= "SELECT DISTINCT `locale` FROM `user` WHERE `locale` != ''";
		$result		= database::query( $sql );

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {

			$response = array ();

			while($row=mysql_fetch_assoc($result)) {
				$response[] = $row['locale'];
			}
		}

		return $response;
	}

	// listing of the users
	public function get_users($pageId = '',$all = false) { 
		$parts = config::get('paths');

		$where = '';
		$userTypes = array (
			'schooladmin',
			'schoolteacher',
			'student',
			'homeuser',
			'school',
			'affiliate',
			'reseller'
		);
		$pag = '';
		if($pageId=='') {
			$pag = $parts[count($parts)-1];
			// if(is_numeric($pag))
			if(isset($pag) && is_numeric($pag) && $pag > 0) {
				$pageId = $pag;
			} else {
				$pageId = 1;
			}
		}

		if( in_array( strtolower( @$parts[2] ), $userTypes ) ) {
			$where = "where FIND_IN_SET('".strtolower( $parts[2] )."',user_type)";
		}
		if( isset($parts[3]) && !is_numeric($parts[3]) ) {
			$where .= " and locale = '".$parts[3]."'";
		}

		if($all == false) { 
			$result = database::query('SELECT COUNT(`uid`) FROM `user` '.$where);
			$max = 0;
			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				$row = mysql_fetch_array($result);
				$max = $row[0];
			}
			

			$this->pager(
					$max,						//see above
					config::get("pagesize"),	//how many records to display at one time
					$pageId,
					array("php_self" => "")
			);
			$this->set_range(10);

			if(strtolower( @$parts[2] ) == 'school'){
				$where .=" AND `U`.`uid` = `SC`.`user_uid`";
				$sql = "SELECT `U`.*, `SC`.school FROM `user` as U, `users_schools` as SC ".$where." ORDER BY `registered_dts` DESC LIMIT ".$this->get_limit();
			} else {
				 $sql = "SELECT * FROM user ".$where." ORDER BY `registered_dts` DESC LIMIT ".$this->get_limit(); 
			}

			$result = database::query( $sql );
		} else {
			$result = database::query("SELECT * FROM `user` ".$where." ORDER BY `registered_dts` DESC");
		}

		$this->data		= array();

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			while($row=mysql_fetch_assoc($result)) {
				$this->data[] = $row;
			}
		}
		return $this->data;
	}

	public function isCreateSuccessful() {
		$response = array();
		$insert_id = 0;
		$response = $this->isValidData();

		if(!empty ($response)) {
			if($response[0] == false) {
				$insert_id		= $this->insertFromAdmin();
				$response[1][]	= "User Added Successfully";
			}
		}
		$message_type	= ($response[0] == true)?"error":"success";
		return array($message_type,$response[1],$insert_id);
		// component_response::htmlSuccessFailureResponse($insert_id,$message_type,$response[1]);
	}

	public function insertFromAdmin() {
		$insert_id	= NULL;
		$email		= mysql_real_escape_string($this->arrFields['email']['Value']);
		$password	= mysql_real_escape_string(md5($this->arrFields['password']['Value']));
		$referral	= mysql_real_escape_string($_POST['referral']);

		$sql		= "INSERT INTO `user` ( `registered_dts`, `email`, `password`, `access_allowed`, `deleted`, registration_ip, registration_key, allow_access_without_sub, optin, referral, verified_dts, locale) VALUES (NOW(), '{$email}', '{$password}', '{$this->arrFields['access_allowed']['Value']}', '{$this->arrFields['deleted']['Value']}', '".$_SERVER['REMOTE_ADDR']."', '".session_id()."', '".$_POST['allow_access_without_sub']."', '".$_POST['optin']."', '".addslashes($referral)."', NOW() , '".$_POST['locale']."')";

		$result = database::query($sql);
		if($result && mysql_error()=='') {
			$insert_id  =   mysql_insert_id();
		}
		return $insert_id;
	}

	public function isUpdateSuccessFul() {
		$response	= array();
		$response	= $this->isValidData(true);
		$insert_id	= 0;

		if(!empty ($response)) {
			if($response[0] == false) {
				$this->save();
				$insert_id		= $this->get_uid();
				$this->insertChangeInTransaction($insert_id);
				$response[1][]	= "User Updated Successfully";
			}
		}
		$message_type			= ($response[0] == true)?"error":"success";
		return array($message_type,$response[1],$insert_id);
		//component_response::htmlSuccessFailureResponse($insert_id,$message_type,$response[1]);
	}

	public function insertChangeInTransaction($row_uid = 0) {
		if(!empty($this->arrForm) && is_numeric($row_uid) && $row_uid > 0) {
			$user_transaction = new user_transaction();
			foreach($this->arrForm as $key => $val) {
				$user_transaction->set_row_uid($row_uid);
				$user_transaction->set_field_name($key);
				$user_transaction->set_field_value_was($val);
				$user_transaction->set_field_updated_dts(date("Y-m-d H:i:s"));
				$user_transaction->set_changed_by_user_uid($_SESSION['user']['uid']);
				$user_transaction->set_changed_by_user_type(1);
				$user_transaction->set_session_uid(session_id());
				$user_transaction->set_changed_by_ip_address($_SERVER['REMOTE_ADDR']);
				$user_transaction->insert_transaction();
			}
			$user_transaction->commit_transaction();
			unset($this->arrForm);
		}
	}

	protected function isValidData($update = false) {
		$user_uid			= (isset($_POST['user_uid'])      ? format::to_integer($_POST['user_uid']) : '0');
		$email				= (isset($_POST['email'])         ? format::to_string($_POST['email']) : '');
		$password			= (isset($_POST['password'])      ? format::to_string($_POST['password']) : '');
		$conf_password		= (isset($_POST['conf_password']) ? format::to_string($_POST['conf_password']) : '');
		$allow_access		= (isset($_POST['allow_access'])  ? format::to_integer($_POST['allow_access']) : '');
		$is_admin			= (isset($_POST['is_admin'])      ? format::to_integer($_POST['is_admin']) : '');
		$is_deleted			= (isset($_POST['deleted'])       ? format::to_integer($_POST['deleted']) : '');

		$error				= false;
		$message			= array();

		if(is_numeric($user_uid) && $user_uid > 0) {
			parent::__construct($user_uid,__CLASS__);
			$this->load();
		}

		if(strlen($email) <= 0 || strlen($email) > 255) {
			$error          =   true;
			$message['email_error']      =   "Please Provide Valid Email";
		} else if($this->email_exist($email)) {
			$error          =   true;
			$message['email_error']      =   "Email Already Exist";
		}
		if($update == false) {
			if(strlen($password) <= 0 || strlen($password) > 10) {
				$error          =   true;
				$message['pass_error']      =   "Please Provide Password";
			} else if($password != $conf_password) {
				$error          =   true;
				$message['cpass_error']      =   "Password and confirm Password do not match";
			}
		} else if($password != $conf_password) {
			$error          =   true;
			$message['cpass_error']      =   "Password and confirm Password do not match";
		}
		if(@isset($_POST['user_type']) && @$_POST['user_type'] == '') {
			$error          =   true;
			$message['user_type_error']      =   "Please seelect user type.";
		}

                if(@isset($_POST['locale']) && @$_POST['locale'] == '') {
			$error          =   true;
			$message['locale_error']      =   "Please seelect locale.";
		}

		if(!$error) {

			// get the old values to store in the transaction
			if($update) {
				if($this->arrFields['email']['Value'] != $email) {
					$this->arrForm['email'] = $this->arrFields['email']['Value'];
				}
				if($this->arrFields['access_allowed']['Value'] != (int)$allow_access) {
					$this->arrForm['access_allowed'] = $this->arrFields['access_allowed']['Value'];
				}
				if($this->arrFields['is_admin']['Value'] != (int)$is_admin) {
					$this->arrForm['is_admin'] = $this->arrFields['is_admin']['Value'];
				}
				if($this->arrFields['deleted']['Value'] != (int)$is_deleted) {
					$this->arrForm['deleted'] = $this->arrFields['deleted']['Value'];
				}
			}
			$this->arrFields['email']['Value']          =   $email;
			if($password != "") {
				$this->arrFields['password']['Value']   =   md5($password);
				$this->arrFields['password_open']       =   $password;
			}
			$this->arrFields['access_allowed']['Value'] =   $allow_access;
			$this->arrFields['is_admin']['Value']       =   $is_admin;
			$this->arrFields['deleted']['Value']        =   $is_deleted;

			$this->arrFields['allow_access_without_sub']['Value']        =   $_POST['allow_access_without_sub'];
			$this->arrFields['optin']['Value']        =   $_POST['optin'];
			$this->arrFields['referral']['Value']        =   $_POST['referral'];
                        $this->arrFields['locale']['Value']        =   $_POST['locale'];
			if(isset($_POST['user_type'])) {
				$this->arrFields['user_type']['Value']        =   $_POST['user_type'];
			}
		}
		return array($error,$message);
	}

	public function isAdmin() {
		return ($this->arrFields['is_admin']['Value'] == 1)?true:false;
	}

	public function userRedirectUrl() {
		return $this->isAdmin() ? "admin/" : "flash/";
	}

	public function isUser() {
		return $this->arrFields['active']['Value'];
	}

	public static function isLoggedIn() {
		$response = false;
		if(isset($_SESSION['user']) && isset($_SESSION['user']['uid']) && $_SESSION['user']['uid'] > 0) {
			$response = true;
		}
		return $response;
	}

	public function email_exist($email = "") {
		$found  =   false;
		$email  =   mysql_real_escape_string($email);
		$sql    =   "SELECT * FROM `user` WHERE `email` = '$email' ";
		if($this->get_uid() != null) {
			$sql .= " AND `uid` != '{$this->get_uid()}'";
		}
		$sql .=" LIMIT 1";

		$result =   database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$found  =   true;
		}
		return $found;
	}

	public function username_exist($username_open = "") {
		$found  =   false;
		$username_open  =   mysql_real_escape_string($username_open);
		$sql    =   "SELECT * FROM `user` WHERE `username_open` = '$username_open' ";
		if($this->get_uid() != null) {
			$sql .= " AND `uid` != '{$this->get_uid()}'";
		}
		$sql .=" LIMIT 1";

		$result =   database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$found  =   true;
		}
		return $found;
	}
	public function SubscribeSave() {
		$message = '';
		$errors = array ();

		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1'] : array ();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2'] : array ();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3'] : array ();

		if(count($form1) > 0 && count($form2) > 0 && count($form3) > 0) {
			/**
			 * Add user to database and add to subscriptions if necessary too
			 */
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
			$registration_key	= md5($form2['school_name']['value'].$ip_address);

                        /* Set values to user table fields */
                        $this->arrFields['registered_dts']['Value'] = date('Y-m-d H:i:s',$time);
                        $this->arrFields['registration_ip']['Value'] = $ip_address;
                        $this->arrFields['registration_key']['Value'] = $registration_key;
                        $this->arrFields['affiliate']['Value'] = mysql_real_escape_string(@$_SESSION['aff']);
                        $this->arrFields['email']['Value'] = mysql_real_escape_string($form1['email']['value']);

                        if(isset($form3['username_open']['value']) && $form3['username_open']['value'] != '')
                            $this->arrFields['username_open']['Value'] = mysql_real_escape_string($form3['username_open']['value']);

                        $this->arrFields['password']['Value'] = md5($form1['password']['value']);
                        $this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);

                        /*
                        if(isset($form3['password_open']['value']) && $form3['password_open']['value'] != '')
                        $this->arrFields['password']['Value'] = md5($form1['password']['value']);
                        $this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);
                         * 
                         */

                        $this->arrFields['access_allowed']['Value'] = 1;
                        $this->arrFields['allow_access_without_sub']['Value'] = 1;
                        $this->arrFields['active']['Value'] = 1;
                        $this->arrFields['locale']['Value'] = mysql_real_escape_string(config::get('locale'));
                        $paths = config::get('paths');
                        $this->arrFields['user_type']['Value'] = mysql_real_escape_string(@$paths[1]);      
                        
                        /* Set values to user table fields END */
                        // insert record to table.
                       return $insert = $this->insert();
                       
                }

            
            
        }


         public function SubscribeSaveHomeUser()
        {
                $message = '';
		$errors = array ();

		$form1 = isset($_SESSION['form1']) ? $_SESSION['form1'] : array ();
		$form2 = isset($_SESSION['form2']) ? $_SESSION['form2'] : array ();
		$form3 = isset($_SESSION['form3']) ? $_SESSION['form3'] : array ();
                
		if(count($form1) > 0) { 
			/**
			 * Add user to database and add to subscriptions if necessary too
			 */
			$time				= time();
			$ip_address			= addslashes(substr($_SERVER['REMOTE_ADDR'],0,32));
			$registration_key	= md5($form1['email']['value'].$ip_address);

                        /* Set values to user table fields */
                        $this->arrFields['registered_dts']['Value'] = date('Y-m-d H:i:s',$time);
                        $this->arrFields['registration_ip']['Value'] = $ip_address;
                        $this->arrFields['registration_key']['Value'] = $registration_key;
                        $this->arrFields['affiliate']['Value'] = mysql_real_escape_string(@$_SESSION['aff']);
                        $this->arrFields['email']['Value'] = mysql_real_escape_string($form1['email']['value']);

                        if(isset($form3['username_open']['value']) && $form3['username_open']['value'] != '')
                            $this->arrFields['username_open']['Value'] = mysql_real_escape_string($form3['username_open']['value']);

                        $this->arrFields['password']['Value'] = md5($form1['password']['value']);
                        $this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);

                        /*
                        if(isset($form3['password_open']['value']) && $form3['password_open']['value'] != '')
                        $this->arrFields['password']['Value'] = md5($form1['password']['value']);
                        $this->arrFields['password_open']['Value'] = mysql_real_escape_string($form1['password']['value']);
                         *
                         */

                        $this->arrFields['allow_access_without_sub']['Value'] = 1;
                        $this->arrFields['active']['Value'] = 1;
                        $this->arrFields['locale']['Value'] = mysql_real_escape_string(config::get('locale'));
                        $paths = config::get('paths');
                        $this->arrFields['user_type']['Value'] = mysql_real_escape_string(@$paths[1]);

                        /* Set values to user table fields END */
                        // insert record to table.
                       return $insert = $this->insert();

                }



        }


	public static function getUserByEmailAddress($email = "") {
		$user_uid   =   false;
		$email      =   mysql_real_escape_string($email);
		$sql        =   "SELECT `uid` FROM `user` WHERE `email` = '$email' LIMIT 1";
		$result     =   database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$rec        =   mysql_fetch_assoc($result);
			$user_uid   =   $rec['uid'];
		}
		return $user_uid;
	}

	public static function getUserByOpenUserName($email = "") {
		$user_uid   =   false;
		$email      =   mysql_real_escape_string($email);
		$sql        =   "SELECT `uid` FROM `user` WHERE `username_open` = '$email' LIMIT 1";
		$result     =   database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$rec        =   mysql_fetch_assoc($result);
			$user_uid   =   $rec['uid'];
		}
		return $user_uid;
	}

	// operations to perform
	public function isValidLogin() {
		$response = array (
			'fields'=>array (
				'email'     => array (
					'default'   => 'Email Address',
					'message'   => '',
					'highlight' => false,
					'error'     => false,
					'value'     => ''
				),
				'password'      => array (
					'default'   => '',
					'message'   => '',
					'error'     => false,
					'highlight' => false
				)
			),
			'message' => ''
		);

		$user_uid           =   0;
		$error              =   false;
		// validation start here
		if(validation::isPresent('email',$_POST)) {
			if(validation::isValid('text',$_POST['email'])) { 
				if(  (($user_uid  = self::getUserByEmailAddress($_POST['email'])) === false) && (($user_uid  = self::getUserByOpenUserName($_POST['email'])) === false)  ) {  
					$response['fields']['email']['message']     = "The details you have entered do not match our records.";
					$response['fields']['email']['error']       = true;
					$response['fields']['email']['highlight']   = true;
				} else {
					$response['fields']['email']['value']       = $_POST['email'];
				}
			} else {
				$response['fields']['email']['message']         = 'Please Enter a valid email address';
				$response['fields']['email']['error']           = true;
				$response['fields']['email']['highlight']       = true;
			}
		} else {
			$response['fields']['email']['message']             = 'Email address is requried';
			$response['fields']['email']['error']               = true;
			$response['fields']['email']['highlight']           = true;
		}

		if(validation::isPresent('password',$_POST)) {
			if(!validation::isValid('text',$_POST['password'])) {
				$response['fields']['password']['message']  = 'Please Enter a valid password';
				$response['fields']['password']['error']    = true;
				$response['fields']['password']['highlight']= true;
			}
		} else {
			$response['fields']['password']['message']      = 'Password is requried';
			$response['fields']['password']['error']        = true;
			$response['fields']['password']['highlight']    = true;
		}
		if(is_numeric($user_uid) && $user_uid > 0) {
			parent::__construct($user_uid);
			$this->load();
			$oldPassword        =   $this->get_password();
			if(md5($_POST['password']) != $oldPassword) {
				$response['message']    =   "The details you have entered do not match our records.";
			} else if($this->get_access_allowed() == 0) {
				$response['message']    =   "The details you have entered do not match our records.";
			}
			if($this->get_deleted() == 1) {
				$response['message']    =   "The details you have entered do not match our records.";
			}
		}

		if(count($response['fields']) > 0) {
			foreach($response['fields'] as $key => $data) {
				if($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}
		if($response['message'] != "") {
			$error = true;
		}

		if(!$error) {
			return true;
		} else {
			return $response;
		}
	}

	public function isValidRegister() {
		$response = array (
			'fields'=>array (
				'email'             => array (
					'default'   => 'Email Address',
					'message'   => '',
					'highlight' => false,
					'error'     => false,
					'value'     => ''
				),
				'password'          => array (
					'default'   => '',
					'message'   => '',
					'error'     => false,
					'highlight' => false
				),
				'confirm_password'  => array (
					'default'   => '',
					'message'   => '',
					'error'     => false,
					'highlight' => false
				)
			),
			'message' => ''
		);

		$user_uid           =   0;
		$error              =   false;
		// validation start here
		if(validation::isPresent('email',$_POST)) {
			if(validation::isValid('email',$_POST['email'])) {
				if(($user_uid  = self::getUserByEmailAddress($_POST['email'])) !== false) {
					$response['fields']['email']['message']     = "Email Address is not available";
					$response['fields']['email']['error']       = true;
					$response['fields']['email']['highlight']   = true;
				} else {
					$response['fields']['email']['value']       = $_POST['email'];
					$this->set_email($_POST['email']);
				}
			} else {
				$response['fields']['email']['message']         = 'Please Enter a valid email address';
				$response['fields']['email']['error']           = true;
				$response['fields']['email']['highlight']       = true;
			}
		} else {
			$response['fields']['email']['message']             = 'Email address is requried';
			$response['fields']['email']['error']               = true;
			$response['fields']['email']['highlight']           = true;
		}

		if(validation::isPresent('password',$_POST)) {
			if(!validation::isValid('text',$_POST['password'])) {
				$response['fields']['password']['message']  = 'Please Enter a valid password';
				$response['fields']['password']['error']    = true;
				$response['fields']['password']['highlight']= true;
			} else {
				$this->set_password($_POST['password']);
			}
		} else {
			$response['fields']['password']['message']      = 'Password is requried';
			$response['fields']['password']['error']        = true;
			$response['fields']['password']['highlight']    = true;
		}

		if(validation::isPresent('confirm_password',$_POST)) {
			if(!validation::isValid('text',$_POST['confirm_password'])) {
				$response['fields']['confirm_password']['message']  = 'Please Enter a valid Confirm password';
				$response['fields']['confirm_password']['error']    = true;
				$response['fields']['confirm_password']['highlight']= true;
			} else if($_POST['confirm_password'] != $_POST['password']) {
				$response['fields']['confirm_password']['message']  = 'Password and Confirm password do not match';
				$response['fields']['confirm_password']['error']    = true;
				$response['fields']['confirm_password']['highlight']= true;
			}
		} else {
			$response['fields']['confirm_password']['message']      = 'Confirm Password is requried';
			$response['fields']['confirm_password']['error']        = true;
			$response['fields']['confirm_password']['highlight']    = true;
		}


		if(count($response['fields']) > 0) {
			foreach($response['fields'] as $key => $data) {
				if($data['error'] == true) {
					$error = true;
					break;
				}
			}
		}

		if(!$error) {
			return true;
		} else {
			return $response;
		}
	}

	public function isValidLink($link = "") {
		$valid                  =   false;
		$user_registration_uid  =   0;
		if($link != "") {
			if(($user_registration_uid = user_registration::ket_exists($link)) !== false) {
				$user_registration  = new user_registration($user_registration_uid);
				if($user_registration->get_valid()) {
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

	public function login($returnUrl = false) {

		$_SESSION['user']['uid']            = $this->get_uid();
		$_SESSION['user']['email']          = $this->get_email();
		$_SESSION['user']['type']           = $this->get_is_admin();
                $_SESSION['user']['user_type']      = explode(',',@$this->get_user_type());
                $_SESSION['user']['prefix']         = $this->get_locale();
		$_SESSION['user']['logged_in']      = true;

		if($returnUrl == true) {
			return $this->userRedirectUrl();
		} else {
			$this->redirectTo($this->userRedirectUrl());
		}
	}

	public function register($sendEmail = true) {
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
			parent::__construct($insert_id, __CLASS__);
			$this->load();
			$success = true;
			if($sendEmail) {
				$this->sendRegistrationEmail();
			}
			$this->login();
		}
		return $success;
	}

	public function verify($sendEmail = true) {
		if($this->get_access_allowed() != 1) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->arrForm['verified_dts'] = $this->get_verified_dts();
			$this->set_access_allowed(1);
			$this->set_verified_dts(date("Y-m-d H:i:s"));
			$this->save();
			$this->insertChangeInTransaction($this->get_uid());
			if($sendEmail) {
				$this->sendEmailWelcome();
			}
		}
	}

	public function logout($redirect = false) {
		session_destroy();
		if($redirect) {
			output::redirect(config::url());
		}
	}

	public function passwordStrength() {

	}

	public function enable($sendEmail = true) {
		if($this->get_allow_access() != 1) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['access_allowed']['Value'] = 1;
		$this->save();
		if($sendEmail) {
			$this->sendEmailEnabled();
		}
	}

	public function disable($sendEmail = true) {
		if($this->get_allow_access() != 0) {
			$this->arrForm['access_allowed'] = $this->get_access_allowed();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['access_allowed']['Value'] = 0;
		$this->save();
		if($sendEmail) {
			$this->sendEmailDisabled();
		}
	}

	public function changePassword($password = "") {
		if($this->get_password() != md5($password)) {
			$this->arrForm['password'] = $this->get_password();
			$this->insertChangeInTransaction($this->get_uid());
		}
		$this->arrFields['password']['Value'] = md5($password);
		$this->save();
		if($sendEmail) {
			$this->sendEmailPasswordChanged();
		}
	}

	public function changeEmail() {

	}

	public function sendEmailWelcome() {

	}

	public function sendEmailDisabled() {

	}

	public function sendEmailEnabled() {

	}

	public function sendEmailPasswordChanged() {

	}

	public function sendEmailChangedEmail() {

	}

	public function sendRegistrationEmail() {
		$md5Id  =   $this->getMD5registerLink();
		$insert =   $this->setUserRegistrationKey($md5Id);
		if($insert) {
			$link   =   config::url("login/verify/$md5Id/");
			$mail   =   new email_phpmailer();
			//$mail->AddReplyTo("name@yourdomain.com","First Last");
			//$mail->SetFrom('name@yourdomain.com', 'First Last');
			//$mail->AddReplyTo("name@yourdomain.com","First Last");
			$body               =   $link;
			$address            =   $this->get_email();
			$mail->Subject      =   "Create Account Verification Email";
			$mail->AltBody      =   "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->AddAddress($address, "");
			$mail->MsgHTML($body);
			$mail->Send();
		}
	}

	public function getMD5registerLink() {
		$email  = $this->get_email();
		$uid    = $this->get_uid();
		$md5Id  =   md5($email."_".$uid);
		return $md5Id;
	}

	public function setUserRegistrationKey($md5Id = "") {
		$insert     = false;
		$insert_id  = null;
		if($md5Id != "") {
			$user_registration = new user_registration();
			$user_registration->set_user_uid($this->get_uid());
			$user_registration->set_key($md5Id);
			$user_registration->set_created_dts(date("Y-m-d H:i:s"));
			if(($insert_id = $user_registration->insert()) !== false) {
				$insert = true;
			}
		}
		return $insert;
	}


	public function getUserListByType($type, $where = array()) {
		$and = '';
		foreach( $where as $idx => $val ){
			$and .= " AND " . $idx . " = '" . $val . "'";
		}

		$sql = "SELECT *  FROM user where FIND_IN_SET('".strtolower( $type )."',user_type) ". $and ." ORDER BY `registered_dts` DESC";
		$result = database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {

			$body       = new xhtml('body.admin.users.type.list');
			$body->load();

			while($data = mysql_fetch_assoc($result)) {
				$data['edit'] = 'profile/'.$type.'/';
				$panel = new xhtml('body.admin.users.list.row');
				$panel->load();
				$panel->assign($data);
				$page_rows[]    = $panel->get_content();
			}
			$body->assign('users.rows'          ,   implode('',$page_rows));
			return $body->get_content();
		}
		return 'Users not found.';
	}

	public function getUserListForSchoolByType($type , $school_id, $ref_table, $where = array()) {
		$and = "AND uid IN ( SELECT uid FROM ". $ref_table ." WHERE school_id = '".$school_id."')";
		foreach( $where as $idx => $val ){
			$and .= " AND " . $idx . " = '" . $val . "'";
		}

		$sql = "SELECT *  FROM user where FIND_IN_SET('".strtolower( $type )."',user_type) ". $and ." ORDER BY `registered_dts` DESC";

		$result = database::query($sql);
		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {

			$body       = new xhtml('body.admin.users.type.list');
			$body->load();

			while($data = mysql_fetch_assoc($result)) {
				$data['edit'] = 'profile/'.$type.'/';
				$panel = new xhtml('body.admin.users.list.row');
				$panel->load();
				$panel->assign($data);
				$page_rows[]    = $panel->get_content();
			}
			$body->assign('users.rows'          ,   implode('',$page_rows));
			return $body->get_content();
		}
		return 'Users not found.';
	}
}

?>