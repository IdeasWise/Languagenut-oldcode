<?php

/**
 * login.php
 */
class Login extends Controller {

	public function __construct () {
		parent::__construct();

		$paths = config::get('paths');
		if(count($paths) > 1) {
			if(isset($paths[1])) {
				switch($paths[1]) {
					case "verify":
						$plugin_login = new plugin_register();
						$plugin_login->processVerification();
					break;
				}
			}
		}

		if(user::isLoggedIn()) {
			$user = new user($_SESSION['user']['uid']);
			$user->load();
			$user->redirectTo($user->userRedirectUrl());
		} else {
			$this->page_login();
		}
	}

	protected function page_login () {
		$plugin_login = new plugin_login();
		$body = $plugin_login->run();

		$skeleton = new xhtml ('skeleton.login');
		$skeleton->load();
		$skeleton->assign (
			array (
				'body'	=> $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>