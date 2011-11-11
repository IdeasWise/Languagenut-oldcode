<?php

/**
 * login_widget.php
 */
class Login_widget extends Controller {

	public  $paths = array();

	public function __construct () {
		parent::__construct();
		$this->process_login();
	}

	private function process_login() {
		$this->paths = config::get('paths');
		if(isset($this->paths[1]) && $this->paths[1] === 'token' && isset($this->paths[2]) && !empty($this->paths[2]) ) {
			$this->doLogin();
			exit;
		}

		if( isset($_REQUEST['email']) && isset($_REQUEST['password']) && !empty($_REQUEST['email']) && !empty($_REQUEST['password']) ) {
			$json				= array();
			$_POST['email']		= $_REQUEST['email'];
			$_POST['password']	= $_REQUEST['password'];
			$user				= new user();
			if(($response = $user->isValidLogin()) === true) {
				if($user->get_uid() > 0) {
					$login_widget_token = md5($user->get_registration_key().time());
					$user->set_login_widget_token($login_widget_token);
					$user->save();
				}
				$json['status']	= 'success';
				$json['data']	= config::base($user->get_locale().'/login_widget/token/'.$login_widget_token);
				if(isset($_REQUEST['jsoncallback'])) {
					echo $_REQUEST['jsoncallback'].'('.json_encode($json).')';
				} else {
					echo json_encode($json);
				}
			} else {
				if(!empty($response['message'])) {
					$json['status']	= 'error';
					$json['data']	= $response['message'];
					if(isset($_REQUEST['jsoncallback'])) {
						echo $_REQUEST['jsoncallback'].'('.json_encode($json).')';
					} else {
						echo json_encode($json);
					}
				}
			}
		}
	}

	private function doLogin() {
		$query ="SELECT ";
		$query.="`uid` ";
		$query.="FROM ";
		$query.="`user` ";
		$query.="WHERE ";
		$query.="`login_widget_token` = '".mysql_real_escape_string($this->paths[2])."' ";
		$query.="LIMIT 1";
		$result		= database::query($query);
		$user_uid	= 0;
		if($result && mysql_error()=='' && mysql_num_rows($result) == 1) {
			$row = mysql_fetch_array( $result );
			$user_uid = $row['uid'];
		}

		if(is_numeric($user_uid) &&	$user_uid >	0) {
			$objUser	= new user($user_uid);
			$objUser->load();
			$response	= array();

			if($objUser->get_access_allowed() == 0) {
				$response['message'] = 'The details you have entered do not match our records.';
			}
			if($objUser->get_deleted()	== 1) {
				$response['message']	=	"The details you have entered do not match our records.";
			}
			if($objUser->get_is_admin() ==	0){
				if($objUser->has_active_subscription()	== false) {
					$response['message']	=	"Your subscription period is expire please renew now.";
				}
			}
			if(count($response) == 0) {
					$objUser->login(true);
					$objUser->redirectTo('flash/');
			} else {
				echo $response['message'];
			}
		} else {
			echo 'Bad Request! This is link is not live anymore...';
		}
	}
}

?>