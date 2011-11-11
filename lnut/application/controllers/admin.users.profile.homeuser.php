<?php

class homeuser extends Controller {

	private $parts = array();

	public function __construct() {
		parent::__construct();
		$this->parts = config::get('paths');
		$this->profile();
	}

	protected function profile() {
		$skeleton = make::tpl('skeleton.admin');
		$arrBody = array();
		$body = make::tpl('body.admin.users.profile.homeuser');
		$uid = null;
		$objHomeuser = new profile_homeuser();
		if (count($_POST) > 0 && isset($_POST['submit-edit-profile'])) {
			if ($objHomeuser->doSave()) {
				// redirect to homeuser list if all does well;
				$objHomeuser->redirectTo('admin/users/homeuser/');
			} else {
				$arrBody = $objHomeuser->arrForm;
				$body->assign($objHomeuser->arrForm);
			}
		} else {
			$objUser = new user($this->parts[4]);
			$objUser->load();
			if ($this->parts[4] > 0) {
				$arrBody['iuser_uid'] = $this->parts[4];
				$objHomeuser->load(array(), $arrBody);
				$arrBody['vemail'] = $objUser->get_email();
				if ($objHomeuser->get_vfirstname() != '') {
					$uid = $objHomeuser->TableData['uid']['Value'];
					foreach ($objHomeuser->TableData as $idx => $val) {
						$arrBody[$idx] = $val['Value'];
					}
				}
				$body->assign($arrBody);
			}
		}
		$adddress_id = (isset($arrBody['address_id'])) ? $arrBody['address_id'] : '';
		$arrAddress = array(
			"user_uid" => $arrBody['iuser_uid'],
			'tbl_name' => 'profile_homeuser',
			'profile_uid' => $uid,
			"address_id" => $adddress_id,
		);
		$objAddress = new plugin_address_details($arrAddress);
		$contentAddress = $objAddress->run();
		$body->assign(
				array(
					'tab.address' => $contentAddress->get_content()
				)
		);
		$skeleton->assign(
				array(
					'body' => $body
				)
		);
		output::as_html($skeleton, true);
	}

}

?>