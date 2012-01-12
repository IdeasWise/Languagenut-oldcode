<?php

class logout extends Controller {

	public function __construct () {
		if(user::isLoggedIn() && isset($_SERVER['persistent-id']) && isset($_SERVER['affiliation'])) {
			output::redirect(config::url());
		} else if(user::isLoggedIn()) {
			$user = new user($_SESSION['user']['uid']);
			$user->load();
			$user->logout();
			output::redirect(config::url("login"));
		}
	}
}

?>