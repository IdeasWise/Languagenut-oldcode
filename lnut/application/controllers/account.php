<?php

class account extends Controller {

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
		if(isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights'] == 'translator') {
			$this->load_controller('account.translator');
			exit;
		} else if(isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights'] == 'reseller') {
			$this->load_controller('account.reseller');
			exit;
		} else if(isset($_SESSION['user']['userRights']) && $_SESSION['user']['userRights'] == 'student') {
			output::redirectTo("");
			exit;
		}

		$arrPaths	= config::get('paths');

		if (count($arrPaths) > 1) {
			if (isset($arrPaths[1])) {

				$token = $arrPaths[1];

				switch ($token) {
					case "users":
						$this->load_controller('account.users');
					break;
					case "classes":
						$this->load_controller('account.classes');
					break;
					case "schooladmin_sub_package":
						$this->load_controller('account.schooladmin_package');
					break;
					case 'view_logs':
						$this->load_controller('view_logs');
					break;
					case 'login-history':
						$this->load_controller('login_history');
					break;
					case 'order-packages':
						$this->load_controller('account.schooladmin.order.package');
					break;
					case 'packages':
						$this->load_controller('account.schoolteacher.package');
					break;
					case 'class-package-management':
						$this->load_controller('account.schoolteacher.class.package.management');
					break;
					case 'student-package-management':
						$this->load_controller('account.schoolteacher.student.package.management');
					break;
					default:
						output::redirectTo($_SESSION['user']['defaultPage']);
					break;
				}

			} else {
				output::redirectTo($_SESSION['user']['defaultPage']);
			}
		} else {
			output::redirectTo($_SESSION['user']['defaultPage']);
		}
	}

}

?>