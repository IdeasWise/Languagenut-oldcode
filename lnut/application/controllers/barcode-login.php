<?php

/**
 * barcpde-login.php
 */
class Login extends Controller {

	public function __construct () {
		parent::__construct();

		if(count($this->paths) > 1 && isset($this->paths[1]) && $this->paths[1]=="verify") {
			$plugin_register = new plugin_register();
			$plugin_register->processVerification();
		}

		if(user::isLoggedIn()) {
			$objUser = new user($_SESSION['user']['uid']);
			$objUser->load();
			$objUser->redirectTo($objUser->userRedirectUrl());
		} else {
			//$plugin_login = new plugin_login();
			//$body = $plugin_login->run();
			$body = make::tpl('body.barcode.login');
			if(isset($_POST['form']) && $_POST['form'] = "login") {
				$ObjUser	= new user();
				$response	= array();
				if(($response = $ObjUser->isValidBaarcodeLogin()) === true) {
					$ObjUser->login();
				} else {
					if(isset($response['message'])) {
						$body->assign('message',$response['message']);
					}
				}
			}

			output::as_html(make::tpl('skeleton.login')->assign (array (
				'body'	=> $body,
				'locale'=> config::get('locale')
			)),true);
		}
	}
}

?>