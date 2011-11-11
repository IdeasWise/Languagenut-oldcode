<?php

/**
 * login.php
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
			$plugin_login = new plugin_login();
			$body = $plugin_login->run();

			output::as_html(make::tpl('skeleton.login')->assign (array (
				'body'	=> $body,
				'locale'=> config::get('locale')
			)),true);
		}
	}
}

?>