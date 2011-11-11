<?php

class remote_controller extends Controller {
	
	public $token		= 'access';

	public $arrTokens	= array (
		'access',
		'token',
		'register'
	);
	public $arrPaths	= array();
	
	public function __construct () {
		parent::__construct();
		if(isset($_POST['reseller'])) {
			$_POST['reseller'] 	= mysql_real_escape_string($_POST['reseller']);
		}
		if(isset($_POST['user'])) {
			$_POST['user'] 	= mysql_real_escape_string($_POST['user']);
		}
		$this->arrPaths = config::get('paths');

		if(isset($this->arrPaths[1])) {
			$this->arrPaths[1] = str_replace('-', '', $this->arrPaths[1]);
			$this->token = $this->arrPaths[1];
		} else if(isset($this->arrPaths[0])) {
			$this->arrPaths[0] = str_replace('-', '', $this->arrPaths[0]);
			$this->token = $this->arrPaths[0];
		}

		if(in_array($this->token,$this->arrTokens)) {
			$method = 'get' . ucfirst($this->token); 
			$this->$method();
		} else {
			$this->getAccess();
		}
	}

	// FOLLOWING FUNCTION GET REQUEST VIA CURL AND VERIFY REQUESTS FROM RESELLER AND GENERATES TOKEN.
	public function getToken(){
		$verifyReseller		= false;
		$verifyUser			= false;
		$remote_token		= null;

		// VERIFY RESELLER IF MD5 IS OKAY IT RETURNS TRUE
		$verifyReseller = $this->verifyReseller($_POST['reseller']);
		if( $verifyReseller['return'] === true ){

			// VERIFY USER IF MD5 IS OKAY IT RETURNS TRUE
			$verifyUser = $this->verifyUser($_POST['user'], $verifyReseller['data']);
			if( $verifyUser['return'] === true ){
				echo config::base($verifyUser['data']['locale'].'/remote/'.($remote_token = $this->getPublicToken($_POST['user'])));
			}
		} else {
		 	echo $verifyReseller['error'];
		}
	}
	
	public function verifyReseller( $registration_key ) {
		$return		= array(
			'return'	=> false,
			'data'		=> array(),
			'error'		=> ''
		);
		$query = "SELECT ";
		$query.= "`uid`, ";
		$query.= "`locale` ";
		$query.= "FROM ";
		$query.= "`user` ";
		$query.= "WHERE ";
		$query.= "`registration_key` = '".mysql_real_escape_string($registration_key)."' ";
		$query.= "AND FIND_IN_SET('reseller',`user_type`) ";
		$query.= "LIMIT 1";
		$result = database::query( $query );
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			$return['data'] = mysql_fetch_assoc( $result );
			if($this->verifyResellerServerIPAddress($return['data']['uid']) == true || isset($_POST['do_not_check_ip'])) {
				$return['return'] = true;
			} else {
				$return['error'] = 'Reseller IP address is not valid!';
			}
		} else {
			$return['error'] = 'Reseller does not exist!';
		}
		return $return;
	}

	public function verifyResellerServerIPAddress( $uid ) {
		if($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
			return true;
		}
		$query = "SELECT ";
		$query .= "`uid` ";
		$query .= "FROM ";
		$query .= "`profile_reseller` ";
		$query .= "WHERE ";
		$query .= "`iuser_uid` = '".mysql_real_escape_string($uid)."' ";
		$query .= "AND ";
		$query .= "`server_ip` = '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."' ";
		$query .= "LIMIT 1";
		$result = database::query( $query );
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			return true;
		}
		return false;
	}

	public function verifyUser( $registration_key, $data = array() ) {
		$return = array(
			'return'	=> false,
			'data'		=> array()
		);
		$query = "SELECT ";
		$query.= "`locale`, ";
		$query.= "`uid`, ";
		$query.= "`user_type` ";
		$query.= "FROM ";
		$query.= "`user` ";
		$query.= "WHERE ";
		$query.= "`registration_key` = '".mysql_real_escape_string($registration_key)."' ";
		$query.= "LIMIT 1";
		$result = database::query( $query );
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			$return['data'] = mysql_fetch_assoc( $result );
			if( $this->verifyUserWithReseller( $data, $return['data']  ) === true ) {
				$return['return'] = true;
			}
		}
		return $return;
	}

	public function verifyUserWithReseller( $reseller , $user) {

		$objUser = new user($user['uid']);
		$objUser->load();
		$school_uid = $objUser->getSchoolIdForAccount();
		
		$query = "SELECT ";
		$query.= "`sold_user_uid` ";
		$query.= "FROM ";
		$query.= "`reseller_sale` AS `RS`,";
		$query.= "`profile_reseller` AS `PR` ";
		$query.= "WHERE ";
		$query.= "`iuser_uid` = '".mysql_real_escape_string($reseller['uid'])."' ";
		$query.= "AND ";
		$query.= "`sold_user_uid` = '".mysql_real_escape_string($school_uid)."' ";
		$query.= "LIMIT 1";
		$result = database::query( $query );
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			return true;
		}
		return false;
	}

	public function getPublicToken( $registration_key ) {
		$remote_token = null;
		$remote_token = md5($registration_key.time());

		$query = "UPDATE ";
		$query.= "`user` ";
		$query.= "SET ";
		$query.= "`remote_token` = '".mysql_real_escape_string($remote_token)."' ";
		$query.= "WHERE ";
		$query.= "`registration_key` = '".mysql_real_escape_string($registration_key)."' ";
		$query.= "LIMIT 1";
		$result = database::query( $query );
		return $remote_token; //return false;
	}

	public function getAccess() {
		$arrResponse	= array();
		$user_uid		= 0;
		
		$query ="SELECT ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`user` ";
		$query.="WHERE ";
		$query.="`remote_token` = '".mysql_real_escape_string($this->arrPaths[1])."' ";
		$query.= "LIMIT 1";
		$result = database::query($query);
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			$row = mysql_fetch_array( $result );
			$user_uid = $row['uid'];
		}

		if(is_numeric($user_uid) && $user_uid > 0) {
			$objUser = new user($user_uid);
			$objUser->load();
		
			if($objUser->get_access_allowed() == 0) {
				$arrResponse['message'] = 'The details you have entered do not match our records.';
			}
			if($objUser->get_deleted()	== 1) {
				$arrResponse['message']	=	"The details you have entered do not match our records.";
			}
			if($objUser->get_is_admin() ==	0){
				if($objUser->has_active_subscription()	== false) {
				$arrResponse['message']	=	"Your subscription period is expire please renew now.";
				}
			}
			if(count($arrResponse) == 0) {
				$objUser->login();
			} else {
				echo $arrResponse['message'];
			}
		} else {
			echo 'Bad Request! This is link is not live anymore...';
		}

	}
	
	public function getRegister() {
		$remote_register = new remote_register();
		$remote_register->ProcessRequest( $this );
	}
}

?>