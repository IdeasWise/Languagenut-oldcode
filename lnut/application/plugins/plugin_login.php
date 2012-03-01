<?php

class plugin_login extends plugin {

	public function __construct($data = array()) {
		$this->data = $data;
		if(config::get('locale')=='nl') {
			$this->body = make::tpl('body.login.nl');
		} else {
			$this->body = make::tpl('body.login');
		}
	}

	public function get_class_name() {
		return __CLASS__;
	}

	public function run() {
		$this->processLogin();

		return $this->body->get_content();
	}

	public function processLogin() {
		if(isset($_POST['form']) && $_POST['form'] = "login") {
			$user       =   new user();
			$response   =   array();
			if($user->isLoggedIn()) {
				$user->logout();
			}
			if(($response = $user->isValidLogin()) === true) {
				$user->login();
			} else {
				$this->parseResponse($response);
			}
		}
	}
}
?>