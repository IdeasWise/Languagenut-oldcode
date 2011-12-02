<?php

class account_reseller_users extends Controller {

	private $token = 'list';
	private $arrTokens = array(
		'user-update',
		'update',
		'list',
		'edit',
		'add',
		'delete',
		'package'
	);
	private $arrProfiles = array(
		'school',
		'schooladmin',
		'schoolteacher',
		'student',
		'homeuser'
	);
	private $arrPaths = array();

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

	protected function doDelete() {
		if (isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objUser = new user($this->arrPaths[3]);
			$objUser->load();
			$objUser->set_deleted(1);
			$objUser->save();
			$objUser->redirectToDynamic('/users/list/'); // redirect to user list
		}
	}

	protected function doEdit() {

		$skeleton = config::getUserSkeleton();
		$objLanguage = new language();

		$body = make::tpl('body.account.users.edit');
		$action = "Update";
		$email = "";

		$locale = '<input type="hidden" name="locale" id="locale" value="en" />';
		if (isset($_SESSION['user']['prefix']) && trim($_SESSION['user']['prefix']) != '') {
			$locale = '<input type="hidden" name="locale" id="locale" value="' . $_SESSION['user']['prefix'] . '" />';
		}

		$body->assign("action", $action);

		$user_uid = $this->arrPaths[count($this->arrPaths) - 1];
		$objUser = new user($user_uid);

		if ($objUser->get_valid()) {
			$objUser->load();
			$body->assign(
				array(
				"uid" => $user_uid,
				"email" => $objUser->get_email(),
				"is_deleted" => ($objUser->get_deleted() == 0) ? 'checked="checked"' : '',
				"is_admin" => ($objUser->get_is_admin() == 0) ? 'checked="checked"' : '',
				"is_allow_access" => ($objUser->get_access_allowed() == 0) ? 'checked="checked"' : '',
				'referral' => $objUser->get_referral(),
				"optin" => ($objUser->get_optin() == 0) ? 'checked="checked"' : '',
				"locale" => $locale,
				"allow_access_without_sub" => ($objUser->get_allow_access_without_sub() == 0) ? 'checked="checked"' : '',
				'success_message' => (isset($_SESSION['success_message'])) ? $_SESSION['success_message'] : ''
				)
			);
			if (isset($_SESSION['success_message'])) {
				unset($_SESSION['success_message']);
			}
		} else {
			// $objUser->redirectToDynamic('/users/'); // redirect to user list if user does not exist;
		}

		if (count($_POST) > 0) {
			$response = $objUser->isUpdateSuccessFul();
			if ($response[0] == 'success') {
			//$objUser->redirectToDynamic('/users/list/');
			if (!isset($_SESSION['success_message'])) {
				$_SESSION['success_message'] = component_message::success('Record has been updated successfully.');
			}
			$objUser->redirectToDynamic('/users/edit/' . $objUser->get_uid() . '/');
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
						"locale" => $locale,
						"allow_access_without_sub" => $allow_access_without_sub
					)
				);
				$error_msg = NULL;
				$array = array();
				foreach ($response[1] as $idx => $val) {
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

		if (isset($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->arrProfiles)) {
			$body = make::tpl('body.admin.users.add.userType');
		} else {
			$body = make::tpl('body.reseller.account.users.add');
		}
		$action = "Add";

		$body->assign("action", $action);

		if (count($_POST) > 0) {
			$objUser = new user();
			$response = $objUser->isCreateSuccessful();
			if ($response[0] == 'success') {
				$objUser->redirectToDynamic('/users/profile/' . $_POST['user_type'] . '/' . $response[2]);
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
						"locale" => $objLanguage->LocaleSelectBoxBasedOnAccessRight('locale', $_POST['locale']),
						"allow_access_without_sub" => $allow_access_without_sub
					)
				);

				$error_msg = NULL;
				$array = array();

				if ($_POST['user_type'] != '') {
					$array[$_POST['user_type']] = 'selected="selected"';
				}
				foreach ($response[1] as $idx => $val) {
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
				"locale" => $objLanguage->LocaleSelectBoxBasedOnAccessRight('locale')
				)
			);
		}

		if (isset($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->arrProfiles)) {
			$userType = "";
			switch ($this->arrPaths[3]) {
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
				'user_type' => $this->arrPaths[3],
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
		$objResellerUsers = new reseller_users();

		if (!isset($this->arrPaths[2])) {
			$this->arrPaths[2] = 'list';
		}

		$body = make::tpl('body.reseller.account.users.' . $this->arrPaths[2]);

		$arrBody = array();
		$arrSearch = array();
		$arrBody['section'] = $this->arrPaths[2];
		$arrSearch['section'] = $this->arrPaths[2];
		$arrBody['component-search-form'] = component_search::form($arrSearch);
		$arrLocales = $objResellerUsers->getDistinctLocales();
		$locales = array();

		if ($arrLocales && count($arrLocales) > 0) {
			foreach ($arrLocales as $locale) {
				$selected = (isset($this->arrPaths[3]) && $this->arrPaths[3] != "" && $this->arrPaths[3] == $locale) ? ' class="selected"' : '';
				$locales[] = '<a href="' . config::url('account/users/') . ($this->arrPaths[2] != 'list' ? $this->arrPaths[2] : 'list') . '/' . $locale . '/"' . $selected . '>' . $locale . '</a>';
			}
			$body->assign('list.locale', implode(' | ', $locales));
		}

		if ($this->arrPaths[2] == 'school') {
			$objSchoolPackages = new school_packages();
		}

		$arrUsers = $objResellerUsers->get_users();
		$now = time();
		$two_weeks_ago = mktime(date('H'), date('i'), date('s'), date('m'), date('d') - 14, date('Y'));
		$arrRows = array();

		if (!empty($arrUsers)) {
			foreach ($arrUsers as $uid => $data) {
				$data['edit'] = 'edit/';
				if (in_array(strtolower($this->arrPaths[2]), $this->arrProfiles)) {
					$data['edit'] = 'profile/' . $this->arrPaths[2] . '/';
				}
				if (!in_array($this->arrPaths[2], array('affiliate', 'translator', 'reseller'))) {
					if ($data['active'] == 0 && $data['access_allowed'] == 0) {
					$data['subscription_cancelled'] = 'subscription_cancelled';
					} else {
					$data['subscription_cancelled'] = '';
					}

					$thisUser = new user($data['uid']);
					$thisUser->load();
					$hasActiveSubscription = $thisUser->has_active_subscription();
					if ($hasActiveSubscription !== true) {
					$data['subscription_cancelled'] = 'subscription_cancelled';
					$data['extra_style'] = ' style="background:#FCBCAE;"';
					}

					$subUserUid = $thisUser->getSchoolId();

					//$data['invoice_sent'] = 'No';
					$data['paid'] = 'No';

					if ($subUserUid !== 0) {

					$arrSubscription = array();
					$arrSubscription = subscriptions::getUserSubscriptionDetails($subUserUid);

					$expiry = strtotime($arrSubscription['expires_dts']);
					$regd = strtotime($thisUser->TableData['registered_dts']['Value']);
					$verified = ($arrSubscription['verified'] == 1 ? true : false);
					//$paid = ($arrSubscription['paid']==1 ? true : false);
					
					$data['invoice_sent'] = (isset($arrSubscription['sent']) && $arrSubscription['sent']==1)?'Yes':'No';
					$data['paid'] = ($arrSubscription['date_paid'] != '0000-00-00 00:00:00') ? 'Yes' : 'No';
					$remaining_days = floor(($expiry - $now) / 86400);
					if ($hasActiveSubscription) {
						$data['extra_style'] ='';
						if($remaining_days > 0 && $remaining_days <= 30 && $verified) {
							$data['subscription_cancelled'] = 'expires-within-30-days-pink';
						} else if ($verified) {
							$data['subscription_cancelled'] = 'verified-green';
						} else if ($two_weeks_ago < $regd && !$verified) {
							$data['subscription_cancelled'] ='two-week-not-verified-orange';
						} else if ($two_weeks_ago > $regd && !$verified) {
							$data['subscription_cancelled'] = 'two-week-not-verified-pink';
						}
					}
					$data['call_status'] = subscriptions::toCallStatusText($arrSubscription['call_status']);
					}
				}
				if ($this->arrPaths[2] == 'school') {
					$data['number_of_requests'] = $objSchoolPackages->getPendingRequests($data['school_uid']);
				}
				$data['registered_dts'] = date('d/m/Y', strtotime($data['registered_dts']));
				if(isset($data['school'])) {
					$data['school'] = stripslashes($data['school']);
				}
				if(isset($data['username_open'])) {
					$data['username_open'] = stripslashes($data['username_open']);
				}

				$rowTemplate = make::tpl('body.reseller.account.users.' . $this->arrPaths[2] . '.row')->assign($data);
				$arrRows[] = $rowTemplate->get_content();
			}
		}



		$page_display_title = $objResellerUsers->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

		$page_navigation = $objResellerUsers->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
		$page_navigation .= $objResellerUsers->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
		$page_navigation .= $objResellerUsers->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

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

	protected function doPackage() {
	$skeleton = config::getUserSkeleton();
	$body = new xhtml('body.account.school.package.add');
	$body->load();

	$objResellerPackage = new reseller_package();
	$objSchoolPackage = new schooladmin_package();

	$reseller_Uid = $_SESSION['user']['uid'];

	$school_Uid = isset($this->arrPaths[4]) ? $this->arrPaths[4] : 0;
	$body->assign('school_uid', $school_Uid);

	$action = (isset($this->arrPaths[3])) ? $this->arrPaths[3] : '';
	if ($action == "add") {
		$objSchoolPackage = new schooladmin_package();
		if (($response = $objSchoolPackage->insertOrUpdate($school_Uid, $_POST)) === true) {
		output::redirect(config::url('account/users/package/list/' . $school_Uid . '/'));
		}
	} else if ($action == "delete") {
		$objSchoolPackage = new schooladmin_package();
		$response = $objSchoolPackage->deleteBeforeUpdate($school_Uid, $this->arrPaths[5]);
		output::redirect(config::url('account/users/package/list/' . $school_Uid . '/'));
	} else if ($action == "update") {
		$objSchoolPackage = new schooladmin_package();
		$response = $objSchoolPackage->isValidCreate($school_Uid, $this->arrPaths[5]);
		output::redirect(config::url('account/users/package/list/' . $school_Uid . '/'));
	}

	// $packageList=$this->objPackage->getList();
	$newResellerPackageList = $objResellerPackage->getResellerPackage($reseller_Uid);

	$availablePackageList = $objResellerPackage->getAvailablePackageList($this->arrPaths[4]);
	$updatedAvailablePackageList = $objResellerPackage->getUpdatedAvailablePackageList($this->arrPaths[4]);

	$selectedPackages = $objResellerPackage->getPackageIds($this->arrPaths[4]);

	$newPackageHtml = "";
	$availablePackageHtml = "";
	$updateAvailablePackageHtml = "";
	foreach ($newResellerPackageList as $resellerPackage) {
		$newResellerSubPackageList = $objResellerPackage->getResellerSubPackage($reseller_Uid, $resellerPackage["package_uid"]);
		$style = (count($newResellerSubPackageList) > 0) ? ' style="border-bottom:0px"' : '';
		$reseller = $objSchoolPackage->getSchoolPackageByResellerPackage($resellerPackage["uid"], $school_Uid);

		if ((isset($reseller[0]["total"]) && $reseller[0]["total"] == 0)) {
		$newPackageHtml.='<tr>';
		$newPackageHtml.='<td ' . $style . '>';
		$newPackageHtml.='<input type="checkbox" name="packages[]" value="package_' . $resellerPackage["uid"] . '"  />';
		$newPackageHtml.='</td>';
		$newPackageHtml.='<td ' . $style . '>';
		$newPackageHtml.=$resellerPackage["name"];
		$newPackageHtml.='</td>';
		$newPackageHtml.='</tr>';
		}
		if (count($newResellerSubPackageList) > 0) {

		foreach ($newResellerSubPackageList as $newResellerSubPackage) {

			$reseller = $objSchoolPackage->getSchoolPackageByResellerSubPackage($newResellerSubPackage["uid"], $school_Uid);

			if ((isset($reseller[0]["total"]) && $reseller[0]["total"] == 0)) {
			$newPackageHtml.='<tr>';
			$newPackageHtml.='<td style="padding:0 0 0 20px">';
			$newPackageHtml.='<input type="checkbox" name="packages[]" value="subpackage_' . $resellerPackage["uid"] . "_" . $newResellerSubPackage["uid"] . '"  />';
			$newPackageHtml.='</td>';
			$newPackageHtml.='<td style="padding:0 0 0 20px">';
			$newPackageHtml.=$newResellerSubPackage["name"];
			$newPackageHtml.='</td>';
			$newPackageHtml.='</tr>';
			}
		}
		}
	}

	$AvailablePackage = $objSchoolPackage->getAvailablePackage($school_Uid);

	if ($AvailablePackage && count($AvailablePackage) > 0) {
		foreach ($AvailablePackage as $aPack) {
		$updateAvailable = $objSchoolPackage->getUpdateAvailable($school_Uid, $aPack["reseller_package_uid"], $aPack["reseller_sub_package_uid"]);
		$packageValue = ($aPack["reseller_sub_package_uid"] > 0) ? 'subpackage_' : 'package_';
		$packageValue.=$aPack["reseller_package_uid"] . '_' . $aPack["reseller_sub_package_uid"];
		$availablePackageHtml.="<tr>";
		$availablePackageHtml.="<td>";
		$availablePackageHtml.=$aPack["name"];
		$availablePackageHtml.="</td>";
		$availablePackageHtml.="<td>";
		$availablePackageHtml.='<a href="javascript:;" onclick="confirm_all_delete(\'' . config::url("account/users/package/delete/{$school_Uid}/{$aPack["uid"]}/") . '\')">Delete</a>';
		$availablePackageHtml.= ( $updateAvailable[0]["total"] > 0) ? '&nbsp;|&nbsp;<a href="' . config::url("account/users/package/update/{$school_Uid}/{$packageValue}/") . ' " >Update Available</a>' : '';
		$availablePackageHtml.="</td>";
		$availablePackageHtml.="</tr>";
		}
	}

	$newPackageHtml = (!empty($newPackageHtml)) ? '<table class="table_main"  border="0" cellspacing="0" cellpadding="10"  ><tr><th></th><th>Package Name</th></tr>' . $newPackageHtml . "</table>" : '<p>No New Packages</p>';
	$availablePackageHtml = (!empty($availablePackageHtml)) ? '<table class="table_main"  border="0" cellspacing="0" cellpadding="10" ><tr><th>Package Name</th><th></th></tr>' . $availablePackageHtml . "</table>" : '<p>You have not available any packages</p>';
	//		$updateAvailablePackageHtml = (!empty($updateAvailablePackageHtml)) ? $updateAvailablePackageHtml : "No new updates in packages";

	$objReseller = new profile_reseller ();
	$resellerName = $objReseller->getResellerNameByUid($reseller_Uid);

	$body->assign('reseller_name', $resellerName);
	$body->assign('new_packages', $newPackageHtml);
	//		$body->assign('updated_available_packages', $updateAvailablePackageHtml);
	$body->assign('available_packages', $availablePackageHtml);
	$body->assign('reseller_uid', $reseller_Uid);
	$skeleton->assign(
		array(
			'body' => $body
		)
	);
	output::as_html($skeleton, true);
	}

	private function getSortinQueryString() {
	$queryString = '';
	if (isset($this->arrPaths[3]) && language::CheckLocale($this->arrPaths[3], false) != false) {
		$queryString .= $this->arrPaths[3] . '/';
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

}

?>