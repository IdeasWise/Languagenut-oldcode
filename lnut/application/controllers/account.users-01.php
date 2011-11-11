<?php

class account_users extends Controller {

	private $token			= 'list';
	private $arrTokens		= array(
		'user-update',
		'update',
		'list',
		'edit',
		'add',
		'delete'
	);

	private $arrProfiles	= array(
		'school',
		'schooladmin',
		'schoolteacher',
		'student',
		'homeuser'
	);

	private $arrPaths			= array();

	public function __construct() {
		parent::__construct();

		$this->arrPaths = config::get('paths');

		if (isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
			$this->token = str_replace(array('user', '-'), array('', ''), $this->arrPaths[2]);
		}

		if (isset($this->arrPaths[3]) && isset($this->arrPaths[4]) && $this->arrPaths[2] == 'profile') {
			if (in_array(strtolower($this->arrPaths[3]), $this->arrProfiles)) {
				$objUser = new user($this->arrPaths[4]);
				if ($objUser->get_valid()) {
					// validate user
					// if yes then load controller
					// other wise redirect him to list page
					$controller = 'admin.users.profile.' . strtolower($this->arrPaths[3]);
					$this->load_controller($controller);
				} else {
					$objUser->redirectToDynamic('/users/'); // redirect to user list if user does not exist;
				}
			}
		} else {
			if (in_array($this->token, $this->arrTokens)) {
				$method = 'do' . ucfirst($this->token);
				$this->$method();
			}
		}
	}

	protected function doEdit() {
		$skeleton			= config::getUserSkeleton();
		$objLanguage		= new language();
		$body				= make::tpl('body.account.users.edit');
		$action				= "Update";
		$email				= "";

		$body->assign("action", $action);

		$user_uid	= $this->arrPaths[count($this->arrPaths) - 1];

		$objUser	= new user($user_uid);

		if($objUser->get_valid()) {
			$objUser->load();

			$body->assign(
				array(
					"uid" => $user_uid,
					"email" => $objUser->get_email(),
					"is_deleted" => ($objUser->get_deleted() == 0) ? 'checked="checked"' : '',
					"is_allow_access" => ($objUser->get_access_allowed() == 0) ? 'checked="checked"' : '',
					'referral' => $objUser->get_referral(),
					"optin" => ($objUser->get_optin() == 0) ? 'checked="checked"' : '',
					"locale" => $objLanguage->LocaleSelectBox('locale', $objUser->get_locale()),
					"allow_access_without_sub" => ($objUser->get_allow_access_without_sub() == 0) ? 'checked="checked"' : ''
				)
			);
		} else {
			// $objUser->redirectToDynamic('/users/'); // redirect to user list if user does not exist;
		}

		if(count($_POST) > 0) {

			$response = $objUser->isUpdateSuccessFul();
			if ($response[0] == 'success') {
				$objUser->redirectToDynamic('/users/');
			} else {
				$deleted = ($_POST['deleted'] == 0) ? 'checked="checked"' : '';
				$access = ($_POST['allow_access'] == 0) ? 'checked="checked"' : '';
				$optin = ($_POST['optin'] == 0) ? 'checked="checked"' : '';
				$allow_access_without_sub = ($_POST['allow_access_without_sub'] == 0) ? 'checked="checked"' : '';

				$body->assign(
							array(
								"email" => $_POST['email'],
								"is_deleted" => $deleted,
								"is_allow_access" => $access,
								'referral' => $_POST['referral'],
								"optin" => $optin,
								"locale" => $objLanguage->LocaleSelectBox('locale', $_POST['locale']),
								"allow_access_without_sub" => $allow_access_without_sub
							)
				);
				$error_msg	= NULL;
				$array		= array();
				foreach ($response[1] as $idx => $val) {
					$array[$idx] = 'label_error';
					$error_msg .= '<li>' . $val . '</li>';
				}
				if ($error_msg != NULL) {
					$error_msg				= '<p>Please correct the errors below:</p><ul>' . $error_msg . '</ul>';
					$array['message_error']	= $error_msg;
					$body->assign($array);
				}
			}
		}

		$skeleton->assign(
						array(
							'body' => $body
						)
					);
		output::as_html($skeleton, true);
	}
	private function AvailableUserTypes( $selected = null ) {
		$arrUserType = array();
		if($_SESSION['user']['userRights'] == 'school'){
			$arrUserType['schooladmin']		= 'School Admin';
			$arrUserType['schoolteacher']	= 'School Teacher';
			$arrUserType['student']			= 'Student';
		}

		if($_SESSION['user']['userRights'] == 'schooladmin'){
			$arrUserType['schoolteacher']	= 'School Teacher';
			$arrUserType['student']			= 'Student';
		}

		if($_SESSION['user']['userRights'] == 'schoolteacher'){
			$arrUserType['student']			= 'Student';
		}

		return format::to_select(
							array(
								"name" => "user_type",
								"id" => "user_type",
								"options_only" => false),
								$arrUserType,
								$selected
							);
	}

	protected function doAdd() {

		$skeleton		= config::getUserSkeleton();
		$objLanguage	= new language();
		$body			= make::xml('body.account.schooladmin.users.add');
		$action			= "Add";
		$body->assign("action", $action);

		if (count($_POST) > 0) {
			$objUser = new user();
			$response = $objUser->isCreateSuccessful();
			if ($response[0] == 'success') {
				$objUser->redirectToDynamic('/users/profile/' . $_POST['user_type'] . '/' . $response[2]);
			} else {

				$deleted	= ($_POST['deleted'] == 0) ? 'checked="checked"' : '';
				$access		= ($_POST['allow_access'] == 0) ? 'checked="checked"' : '';
				$optin		= ($_POST['optin'] == 0) ? 'checked="checked"' : '';
				$allow_access_without_sub = ($_POST['allow_access_without_sub'] == 0) ? 'checked="checked"' : '';

				$body->assign(
							array(
								"email"						=> $_POST['email'],
								"is_deleted"				=> $deleted,
								"is_allow_access"			=> $access,
								'referral'					=> $_POST['referral'],
								"optin"						=> $optin,
								"locale"					=> $objLanguage->LocaleSelectBox('locale', $_POST['locale']),
								"allow_access_without_sub"	=> $allow_access_without_sub,
								"mylocale"					=> config::get('locale')
							)
				);

				$error_msg	= null;
				$array		= array();

				if (isset($array['user_type']) && $_POST['user_type'] != '') {
					$array['user_type'] = $this->AvailableUserTypes($_POST['user_type']);
				} else {
					$array['user_type'] = $this->AvailableUserTypes();
				}

				foreach ($response[1] as $idx => $val) {
					$array[$idx]	= 'label_error';
					$error_msg .= '<li>' . $val . '</li>';
				}
				if ($error_msg != null) {
					$error_msg				= '<p>Please correct the errors below:</p><ul>' . $error_msg . '</ul>';
					$array['message_error']	= $error_msg;
					$body->assign($array);
				}
			}
		} else {
			$body->assign(
				array(
					"locale"	=> $objLanguage->LocaleSelectBox('locale'),
					'user_type'	=> $this->AvailableUserTypes(),
					"mylocale"	=> config::get('locale')
				)
			);
		}

		$skeleton->assign(
			array(
				'body' => $body
			)
		);

		output::as_html($skeleton, true);
	}

	protected function doList() {

		$skeleton	= config::getUserSkeleton();
		if (!isset($this->arrPaths[2])) {
			$this->arrPaths[2] = 'list';
		}
		$body = new xhtml('body.schooladmin.users.' . $this->arrPaths[2]);


		$arrBody							= array();
		$arrSearch							= array();
		$arrBody['section']					= $this->arrPaths[2];
		$arrSearch['section']				= $this->arrPaths[2];
		$arrBody['component-search-form']	= component_search::form($arrSearch);
		$arrLocales							= user::getDistinctLocales();
		$locales							= array();
/*
		if(count($arrLocales) > 0) {
			foreach ($arrLocales as $locale) {
				$selected = (isset($this->arrPaths[3]) && $this->arrPaths[3] != "" && $this->arrPaths[3] == $locale)?' class="selected"':'';
				$locales[] = '<a href="' . config::admin_uri('users/') . ($this->arrPaths[2] != 'list' ? $this->arrPaths[2] : 'list') . '/' . $locale . '/"'.$selected.'>' . $locale . '111</a>';
			}
			$body->assign('list.locale', implode(' | ', $locales));
		}
*/
		$objUser = new user();
		$arrUsers = $objUser->get_users(); // create a function for each type as a wrapper to this method so that we don't use paths within this method
		$arrRows = array();

		if (!empty($arrUsers)) {
			foreach ($arrUsers as $uid => $data) {
				$data['edit'] = 'edit/';
				if (in_array(strtolower($this->arrPaths[2]), $this->arrProfiles)) {
					$data['edit'] = 'profile/' . $this->arrPaths[2] . '/';
				}
				$arrRows[] = make::tpl('body.admin.users.' . $this->arrPaths[2] . '.row')->assign($data)->get_content();

			}
		}

		$page_display_title = $objUser->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
		$page_navigation = $objUser->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>') . $objUser->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo; ') . $objUser->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

		$body->assign('page.display.title'	, $page_display_title);
		$body->assign('page.navigation'		, $page_navigation);
		$body->assign('users.rows'			, implode('', $arrRows));
		$body->assign($arrBody);
		$body->assign($this->getSortinQueryString());
		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	public function logout() {
		$_SESSION = array();
		session_regenerate_id();
		session_destroy();
		output::redirect(config::url(config::get('locale') . '/login/'));
	}
	private function getSortinQueryString( ) { 
		$queryString = '';
		if(isset($this->arrPaths[3]) && language::CheckLocale($this->arrPaths[3], false) != false) {
			$queryString .= $this->arrPaths[3].'/';
		}
		$queryString .='?';
		if(isset($_GET['find'])) {
			$queryString .= "find=".$_GET['find']."&";
		}
		$arrSort = array(
						'sort_email'			=>	'email',
						'sort_registered_dts'	=>	'registered_dts',
						'sort_school'			=>	'school',
						'sort_username_open'	=>	'username_open'
					);
		foreach( $arrSort as $index => $value ) {
			$order = 'asc';
			if(isset($_GET['column']) && $_GET['column'] == $value && isset($_GET['order']) && $_GET['order'] == 'asc' ) {
				$order = 'desc';
			}
			$arrSort[$index] = $queryString . "column=" . $value . "&order=" . $order ;
		}
		return $arrSort;
	}
	protected function doDelete() {
		if( isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) ) {
			$useObj = new user($this->arrPaths[3]);
			$useObj->load();
			$useObj->set_deleted(1);
			$useObj->save();
			$useObj->redirectToDynamic('/users/list/'); // redirect to user list
		}
	}

}

?>