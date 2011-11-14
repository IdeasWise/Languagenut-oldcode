<?php

class users extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'user-update',
		'update',
		'list',
		'edit',
		'add',
		'delete'
	);
	private $arrProfiles = array(
		'school',
		'schooladmin',
		'schoolteacher',
		'student',
		'homeuser',
		'affiliate',
		'reseller',
		'translator'
	);
	private $parts = array();

	public function __construct() {
		parent::__construct();
		$this->parts = config::get('paths');
		if (isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
			$this->token = str_replace(array('user', '-'), array('', ''), $this->parts[2]);
		}
		if (isset($this->parts[3]) && isset($this->parts[4]) && $this->parts[2] == 'profile') {
			if (in_array(strtolower($this->parts[3]), $this->arrProfiles)) {
				$objUser = new user($this->parts[4]);
				if ($objUser->get_valid()) {
					// validate user
					// if yes then load controller
					// other wise redirect him to list page
					$controller = 'admin.users.profile.' . strtolower($this->parts[3]);
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

	protected function doDelete() {
		if (isset($this->parts[3]) && is_numeric($this->parts[3])) {
			$objUser = new user($this->parts[3]);
			$objUser->load();
			$objUser->set_deleted(1);
			$objUser->save();
			$objUser->redirectToDynamic('/users/list/'); // redirect to user list
		}
	}

	protected function doEdit() {
		$skeleton = config::getUserSkeleton();
		$objLanguage = new language();
		$body = make::tpl('body.admin.users.edit');
		$action = "Update";
		$email = "";

		$body->assign("action", $action);
		$user_uid = $this->parts[count($this->parts) - 1];
		$objUser = new user($user_uid);
		if ($objUser->get_valid()) {
			$objUser->load();
			$body->assign(
				array(
					"uid"						=> $user_uid,
					"email"						=> $objUser->get_email(),
					"is_deleted"				=> ($objUser->get_deleted() == 0) ? 'checked="checked"' : '',
					"is_admin"					=> ($objUser->get_is_admin() == 0) ? 'checked="checked"' : '',
					"is_allow_access"			=> ($objUser->get_access_allowed() == 0) ? 'checked="checked"' : '',
					'referral'					=> $objUser->get_referral(),
					"optin"						=> ($objUser->get_optin() == 0) ? 'checked="checked"' : '',
					"locale"					=> $objLanguage->LocaleSelectBox('locale', $objUser->get_locale()),
					"allow_access_without_sub"	=> ($objUser->get_allow_access_without_sub() == 0) ? 'checked="checked"' : '',
					"has_active_subscription"	=> (($objUser->has_active_subscription() === true) ? ' <span style="color:#30A4B1;font-weight:bold;padding-left:15px;">Currently Active</div>' : '<span style="color:#f7941d;font-weight:bold;padding:5px;border:1px solid #f7941d;">Expired!</span>'),
					'success_message'	=> (isset($_SESSION['success_message']))?$_SESSION['success_message']:''
				)
			);
				if(isset($_SESSION['success_message'])) {
					unset($_SESSION['success_message']);
				}
		} else {
			// $objUser->redirectToDynamic('/users/'); // redirect to user list if user does not exist;
		}

		if (count($_POST) > 0) {
			$arrResponse = $objUser->isUpdateSuccessFul();
			if ($arrResponse[0] == 'success') {
				//$objUser->redirectToDynamic('/users/list/');
				if(!isset($_SESSION['success_message'])) {
					$_SESSION['success_message'] = component_message::success('Record has been updated successfully.');
				}
				$objUser->redirectToDynamic('/users/edit/'.$objUser->get_uid().'/');
			} else {
				$deleted = ($_POST['deleted'] == 0) ? 'checked="checked"' : '';
				$access = ($_POST['allow_access'] == 0) ? 'checked="checked"' : '';
				$is_admin = ($_POST['is_admin'] == 0) ? 'checked="checked"' : '';
				$optin = ($_POST['optin'] == 0) ? 'checked="checked"' : '';
				$allow_access_without_sub = ($_POST['allow_access_without_sub'] == 0) ? 'checked="checked"' : '';

				$body->assign(
						array(
							"email" => $_POST['email'],
							"is_deleted" => $deleted,
							"is_admin" => $is_admin,
							"is_allow_access" => $access,
							'referral' => $_POST['referral'],
							"optin" => $optin,
							"locale" => $objLanguage->LocaleSelectBox('locale', $_POST['locale']),
							"allow_access_without_sub" => $allow_access_without_sub
						)
				);

				$error_msg = NULL;
				$array = array();
				foreach ($arrResponse[1] as $idx => $val) {
					$array[$idx] = 'label_error';
					$error_msg .= '<li>' . $val . '</li>';
				}
				if ($error_msg != NULL) {
					$error_msg = '<p>Please correct the errors below:</p><ul>' . $error_msg . '</ul>';
					$array['message_error'] = $error_msg;
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

	protected function doAdd() {
		$skeleton = config::getUserSkeleton();
		$objLanguage = new language();
		if (isset($this->parts[3]) && in_array($this->parts[3], $this->arrProfiles)) {
			$body = make::tpl('body.admin.users.add.userType');
		} else {
			$body = make::tpl('body.admin.users.add');
		}
		$action = "Add";

		$body->assign("action", $action);

		if (count($_POST) > 0) {
			$objUser = new user();
			$arrResponse = $objUser->isCreateSuccessful();
			if ($arrResponse[0] == 'success') {
				$objUser->redirectToDynamic('/users/profile/' . $_POST['user_type'] . '/' . $arrResponse[2]);
			} else {
				$deleted = ($_POST['deleted'] == 0) ? 'checked="checked"' : '';
				$access = ($_POST['allow_access'] == 0) ? 'checked="checked"' : '';
				$is_admin = ($_POST['is_admin'] == 0) ? 'checked="checked"' : '';
				$optin = ($_POST['optin'] == 0) ? 'checked="checked"' : '';
				$allow_access_without_sub = ($_POST['allow_access_without_sub'] == 0) ? 'checked="checked"' : '';

				$body->assign(
						array(
							"email" => $_POST['email'],
							"is_deleted" => $deleted,
							"is_admin" => $is_admin,
							"is_allow_access" => $access,
							'referral' => $_POST['referral'],
							"optin" => $optin,
							"locale" => $objLanguage->LocaleSelectBox('locale', $_POST['locale']),
							"allow_access_without_sub" => $allow_access_without_sub
						)
				);

				$error_msg = NULL;
				$array = array();

				if ($_POST['user_type'] != '') {
					$array[$_POST['user_type']] = 'selected="selected"';
				}
				foreach ($arrResponse[1] as $idx => $val) {
					$array[$idx] = 'label_error';
					$error_msg .= '<li>' . $val . '</li>';
				}
				if ($error_msg != NULL) {
					$error_msg = '<p>Please correct the errors below:</p><ul>' . $error_msg . '</ul>';
					$array['message_error'] = $error_msg;
					$body->assign($array);
				}
			}
		} else {
			$body->assign(
					array(
						"locale" => $objLanguage->LocaleSelectBox('locale')
					)
			);
		}

		if (isset($this->parts[3]) && in_array($this->parts[3], $this->arrProfiles)) {
			$userType = "";
			switch ($this->parts[3]) {
				case "school": $userType = "School";
					break;
				case "schooladmin": $userType = "School Admin";
					break;
				case "schoolteacher": $userType = "School Teacher";
					break;
				case "student": $userType = "Student";
					break;
				case "homeuser": $userType = "Home User";
					break;
				case "affiliate": $userType = "Affiliate";
					break;
				case "reseller": $userType = "Reseller";
					break;
				case "translator": $userType = "Translator";
					break;
			}

			$body->assign(
					array(
						'user_type' => $this->parts[3],
						'userType' => $userType
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
		$skeleton = config::getUserSkeleton();
		if (!isset($this->parts[2])) {
			$this->parts[2] = 'list';
		}

		$body = make::tpl('body.admin.users.' . $this->parts[2]);

		$arrBody = array();
		$arrSearch = array();
		$arrBody['section'] = $this->parts[2];
		$arrSearch['section'] = $this->parts[2];
		$arrBody['component-search-form'] = component_search::form($arrSearch);
		$arrLocales = user::getDistinctLocales();
		$locales = array();

		if ($arrLocales && count($arrLocales) > 0) {
			foreach ($arrLocales as $locale) {
				$selected = (isset($this->parts[3]) && $this->parts[3] != "" && $this->parts[3] == $locale) ? ' class="selected"' : '';
				$locales[] = '<a href="' . config::url('admin/users/') . ($this->parts[2] != 'list' ? $this->parts[2] : 'list') . '/' . $locale . '/"' . $selected . '>' . $locale . '</a>';
			}
			$body->assign('list.locale', implode(' | ', $locales));
		}

		$objUser = new user();
		$arrUsers = $objUser->get_users();
		$arrRows = array();
		$page_display_title="";
		$page_navigation="";
		if (!empty($arrUsers)) {
			$now = time();
			$two_weeks_ago = mktime(date('H'),date('i'),date('s'),date('m'),date('d')-14,date('Y'));
			foreach ($arrUsers as $uid => $data) {
				$data['edit'] = 'edit/';
				if (in_array(strtolower($this->parts[2]), $this->arrProfiles)) {
					$data['edit'] = 'profile/' . $this->parts[2] . '/';
				}

				$panel = make::tpl('body.admin.users.' . $this->parts[2] . '.row');
				if (!in_array($this->parts[2],array('affiliate','translator','reseller'))) {
					if ($data['active'] == 0 && $data['access_allowed'] == 0) {
						$data['subscription_cancelled'] = 'subscription_cancelled';
					} else {
						$data['subscription_cancelled'] = '';
					}

					$thisUser = new user($data['uid']);
					$thisUser->load();
					$hasActiveSubscription = $thisUser->has_active_subscription();
					if($hasActiveSubscription !== true){
						$data['subscription_cancelled'] = 'subscription_cancelled';
						$data['extra_style'] = ' style="background:#FCBCAE;"';
					}

					$subUserUid = $thisUser->getSchoolId();

					if($subUserUid !== 0) {
						$arrSubscription =array();
						$arrSubscription = subscriptions::getUserSubscriptionDetails($subUserUid);


						$expiry = strtotime($arrSubscription['expires_dts']);
						$regd = strtotime($thisUser->TableData['registered_dts']['Value']);
						$verified = ($arrSubscription['verified']==1 ? true : false);
						$paid = ($arrSubscription['paid']==1 ? true : false);

						if($hasActiveSubscription) {
							if($regd < $two_weeks_ago && !$paid) {
								$data['extra_style'] = ' style="background:#FCBCAE;"';
							} else if($regd < $two_weeks_ago && $paid) {
								$data['extra_style'] = ' style="background:#B8ED9C;"';
							} else if($regd > $two_weeks_ago && !$paid) {
								$data['extra_style'] = ' style="background:#FCC52F;"';
							} else if($paid) {
								$data['extra_style'] = ' style="background:#bbdfB1;"';
							}
						}
					}
				}
				if(isset($data['school'])) {
					$data['school'] = stripslashes($data['school']);
				}
				$data['registered_dts'] = date('d/m/Y',strtotime($data['registered_dts']));
				$panel->assign($data);
				$arrRows[] = $panel->get_content();
			}

			$page_display_title = $objUser->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objUser->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objUser->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objUser->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		}



		$body->assign('page.display.title', $page_display_title);
		$body->assign('page.navigation', $page_navigation);
		$body->assign('users.rows', implode('', $arrRows));
		$body->assign($this->getSortinQueryString());
		$body->assign($arrBody);

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	private function getSortinQueryString() {
		$queryString = '';
		if (isset($this->parts[3]) && language::CheckLocale($this->parts[3], false) != false) {
			$queryString .= $this->parts[3] . '/';
		}
		$queryString .='?';
		if (isset($_GET['find'])) {
			$queryString .= "find=" . $_GET['find'] . "&";
		}
		$arrSort = array(
			'sort_email' => 'email',
			'sort_registered_dts' => 'registered_dts',
			'sort_school' => 'school',
			'sort_username_open' => 'username_open'
		);
		foreach ($arrSort as $index => $value) {
			$order = 'asc';
			if (isset($_GET['column']) && $_GET['column'] == $value && isset($_GET['order']) && $_GET['order'] == 'asc') {
				$order = 'desc';
			}
			$arrSort[$index] = $queryString . "column=" . $value . "&order=" . $order;
		}
		return $arrSort;
	}

	public function logout() {
		$_SESSION = array();
		session_regenerate_id();
		session_destroy();
		output::redirect(config::url(config::get('locale') . '/login/'));
	}

}

?>