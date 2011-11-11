<?php

class admin_reseller_controller extends Controller {

	private $parts = array();
	private $token = 'profile';
	private $arrTokens = array(
		'profile',
		'packages',
		'add'
	);
	private $objPackage = null;
	private $objResellerPackage = null;

	public function __construct() {

		parent::__construct();
		$this->parts = config::get('paths');
		if (isset($this->parts[5]) && in_array($this->parts[5], $this->arrTokens)) {
			$this->token = $this->parts[5];
		}

		if (in_array($this->token, $this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		} else {
			$this->doProfile();
		}
	}

	protected function doPackages() {

		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();
		$body = new xhtml('body.admin.reseller_package.add');
		$body->load();

		$this->objPackage = new package();
		$this->objResellerPackage = new reseller_package();

		if (isset($this->parts[6]) && $this->parts[6] == "add") {
			$this->objResellerPackage = new reseller_package();
			if (($response = $this->objResellerPackage->insertOrUpdate($this->parts[4], $_POST)) === true) {
				output::redirect(config::url('admin/users/reseller/'));
			}
		} else if (isset($this->parts[6]) && $this->parts[6] == "delete") {
			$this->objResellerPackage = new reseller_package();
			$response = $this->objResellerPackage->deleteBeforeUpdate($this->parts[4], $this->parts[7]);
			output::redirect(config::url('admin/users/profile/reseller/' . $this->parts[4] . '/packages/'));
		}

		// $packageList=$this->objPackage->getList();
		$newPackageList = $this->objResellerPackage->getNewPackageList($this->parts[4]);
		$availablePackageList = $this->objResellerPackage->getAvailablePackageList($this->parts[4]);
		$updatedAvailablePackageList = $this->objResellerPackage->getUpdatedAvailablePackageList($this->parts[4]);

		$selectedPackages = $this->objResellerPackage->getPackageIds($this->parts[4]);

		$newPackageHtml = "";
		$availablePackageHtml = "";
		$updateAvailablePackageHtml = "";
		foreach ($newPackageList as $package) {
			$newPackageHtml.='<tr>';
			$newPackageHtml.='<td>';
			$newPackageHtml.='<input type="checkbox" name="packages[]" value="' . $package["uid"] . '"  />';
			$newPackageHtml.='</td>';
			$newPackageHtml.='<td>';
			$newPackageHtml.=$package["name"];
			$newPackageHtml.='</td>';
			$newPackageHtml.='</tr>';
		}
//		foreach ($updatedAvailablePackageList as $package) {
//			$updateAvailablePackageHtml.='<tr>';
//			$updateAvailablePackageHtml.='<td>';
//			$updateAvailablePackageHtml.='<input type="checkbox" name="packages[]" value="' . $package["uid"] . '"  />';
//			$updateAvailablePackageHtml.='</td>';
//			$updateAvailablePackageHtml.='<td>';
//			$updateAvailablePackageHtml.= $package["name"];
//			$updateAvailablePackageHtml.='</td>';
//			$updateAvailablePackageHtml.='<td>';
//			$updateAvailablePackageHtml.= 'Edit';
//			$updateAvailablePackageHtml.='</td>';
//			$updateAvailablePackageHtml.='<td>';
//			$updateAvailablePackageHtml.= 'Delete';
//			$updateAvailablePackageHtml.='</td>';
//			$updateAvailablePackageHtml.='</tr>';
//		}
		foreach ($availablePackageList as $package) {
			$availablePackageHtml.='<tr>';
			$availablePackageHtml.='<td>';
			$availablePackageHtml.=$package["name"];
			$availablePackageHtml.='</td>';
			$availablePackageHtml.='<td>';
			$availablePackageHtml.='<a href="' . config::url('admin/reseller_sub_package/list/' . $this->parts[4] . '/' . $package["uid"] . '/') . '">Sub Package</a> | ';
			$availablePackageHtml.='<a href="' . config::url('admin/users/profile/reseller/' . $this->parts[4] . '/packages/delete/' . $package["uid"] . '/') . '">Delete</a>';
			$availablePackageHtml.=' | ';
			$availablePackageHtml.= ( array_search($package["uid"], $updatedAvailablePackageList) === false) ? "-" : '<a href="javascript:;" onclick="make_checked(\'pack_' . $package["uid"] . '\')">Update available</a>';
			$availablePackageHtml.= ( array_search($package["uid"], $updatedAvailablePackageList) === false) ? "" : '<div style="display:none"><input type="checkbox" id="pack_' . $package["uid"] . '" name="packages[]" value="' . $package["uid"] . '"  /></div>';
			$availablePackageHtml.='</td>';
			$availablePackageHtml.='</tr>';
		}

		$newPackageHtml = (!empty($newPackageHtml)) ? '<table class="table_main"  border="0" cellspacing="0" cellpadding="10" ><tr><th></th><th>Package Name</th></tr>' . $newPackageHtml."</table>" : '<p>No New Packages</p>';
		$availablePackageHtml = (!empty($availablePackageHtml)) ? '<table class="table_main"  border="0" cellspacing="0" cellpadding="10" ><tr><th>Package Name</th><th></th></tr>'.$availablePackageHtml."</table>"  : '<p>You have not available any packages</p>';
//		$updateAvailablePackageHtml = (!empty($updateAvailablePackageHtml)) ? $updateAvailablePackageHtml : "No new updates in packages";

		$objReseller = new profile_reseller ();
		$resellerName = $objReseller->getResellerNameByUid($this->parts[4]);

		$body->assign('reseller_name', $resellerName);
		$body->assign('new_packages', $newPackageHtml);
//		$body->assign('updated_available_packages', $updateAvailablePackageHtml);
		$body->assign('available_packages', $availablePackageHtml);
		$body->assign('reseller_uid', $this->parts[4]);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

	protected function doProfile() {
		$skeleton = new xhtml('skeleton.admin');
		$skeleton->load();

		$body = new xhtml('body.admin.users.profile.reseller');
		$body->load();

		$objReseller = new profile_reseller();
		$objLanguage = new language();
		if (count($_POST) > 0) {
			$objReseller = new profile_reseller();
			if ($objReseller->doSave()) {
				$objReseller->redirectTo('admin/users/reseller/'); // redirect to user list if all does well;
			} else {
				$objReseller->arrForm['locale_rights'] = $objLanguage->LocaleSelectBox(
					'locale_rights',
					$objReseller->arrForm['locale_rights']
				);
				$body->assign($objReseller->arrForm);
			}
		} else {
			$objUser = new user($this->parts[4]);
			$objUser->load();

			$arrBody = array();

			if ($this->parts[4] > 0) {

				$arrBody['iuser_uid'] = $this->parts[4];
				$objReseller = new profile_reseller ( );
				$objReseller->load(array(), $arrBody);
				$arrBody['vemail'] = $objUser->get_email();
				if ($objReseller->get_vfirstname() != '') {
					foreach ($objReseller->TableData as $idx => $val) {
						$arrBody[$idx] = $val['Value'];
					}
					$arrBody['locale_rights'] = $objLanguage->LocaleSelectBox(
						'locale_rights',
						$arrBody['locale_rights']
					);
				} else {
					$arrBody['locale_rights'] = $objLanguage->LocaleSelectBox('locale_rights');
				}

				$body->assign($arrBody);
			}
		}

		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

}

?>