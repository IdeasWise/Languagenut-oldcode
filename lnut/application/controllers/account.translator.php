<?php

class account_translator extends Controller {

	private $objUser = null;

	public function __construct() {
		parent::__construct();
		$this->page();
	}

	protected function page() {
		if (user::isLoggedIn()) {
			$this->objUser = new user($_SESSION['user']['uid']);
			$this->objUser->load();

			if ($this->objUser->isLoggedIn()) {
				$this->pageAccount();
			} else {
				$this->objUser->logout();
				output::redirectTo('login/');
			}
		} else {
			output::redirectTo('login/');
		}
	}

	protected function pageAccount() {
		$arrPaths	= config::get('paths');
		if (count($arrPaths) > 1) {
			if (isset($arrPaths[1])) {
				$token = $arrPaths[1];
				switch ($token) {
					case "translations":
						$this->load_controller('account.translations');
					break;
					case "wordbank":
						$this->load_controller('account.wordbank');
					break;
					case 'certificate':
						$this->load_controller('account.certificate');
					break;
					case 'game_translations':
						$this->load_controller('account.game_translations');
					break;
					case 'pages':
						$this->load_controller('account.pages');
					break;
					case 'flash_translations':
						$this->load_controller('account.flash_translations');
					break;
					case "message_translations":
						$this->load_controller('account.message_translations');
					break;
					case 'email-templates':
						$this->load_controller('admin.email.templates');
					break;
					default:
						output::redirectTo('account/translations/');
					break;
				}

			} else {
				output::redirectTo('account/translations/');
			}
		} else {
			output::redirectTo('account/translations/');
		}
	}

}

?>