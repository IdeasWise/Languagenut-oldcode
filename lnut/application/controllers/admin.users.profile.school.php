<?php
class profile_school extends Controller {

	private $parts = array();

	public function __construct() {

		parent::__construct();

		$this->parts = config::get('paths');
		$this->doProfile();
	}

	protected function doProfile() {

		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl('body.admin.users.profile.school');
		$uid		= null;
		$arrBody	= array();
		$objSchool	= new users_schools();

		if ($this->parts[4] > 0) {
			$arrBody['user_uid'] = $this->parts[4];
			$objUser = new user($this->parts[4]);
			$objUser->load();
		}

		if (count($_POST) > 0 && isset($_POST['submit-edit-profile'])) {
			if ($objSchool->doSave()) {
				//$objSchool->redirectToDynamic('/users/school/');
				if(!isset($_SESSION['school_success_message'])) {
					$_SESSION['school_success_message'] = component_message::success('Record has been updated successfully.');
				}
				$objSchool->redirectToDynamic('/users/profile/school/'.$objUser->get_uid().'/');
			} else {
				$body->assign($objSchool->arrForm);
			}

		} else {

			if ($this->parts[4] > 0) {
				$arrBody['user_uid'] = $this->parts[4];
				$objSchool->load(array(), $arrBody);
				$arrBody['username_open'] = $objUser->get_username_open();
				$arrBody['password_open'] = $objUser->get_password_open();

				if ($objSchool->get_user_uid() != '') {
					$uid = $objSchool->TableData['uid']['Value'];
					foreach ($objSchool->TableData as $idx => $val) {
						$arrBody[$idx] = $val['Value'];
					}
				}

				$query ="SELECT ";
				$query.="`notes_2wft_call1`, ";
				$query.="`notes_2wft_call2`, ";
				$query.="`notes_2wft_call3`,";
				$query.="`notes_courtesy_call1`, ";
				$query.="`notes_courtesy_call2`, ";
				$query.="`notes_renewal_call1`, ";
				$query.="`notes_renewal_call2`, ";
				$query.="`call_status` ";
				$query.="FROM ";
				$query.="`user` ";
				$query.="WHERE ";
				$query.="`uid`='".$arrBody['user_uid']."' ";
				$query.="LIMIT 0,1";
				$result = database::query($query);
				if(mysql_error()=='' && mysql_num_rows($result)) {
					$arrRow = mysql_fetch_array($result);
					$arrBody['notes_2wft_call1']		= $arrRow['notes_2wft_call1'];
					$arrBody['notes_2wft_call2']		= $arrRow['notes_2wft_call2'];
					$arrBody['notes_2wft_call3']		= $arrRow['notes_2wft_call3'];
					$arrBody['notes_courtesy_call1']	= $arrRow['notes_courtesy_call1'];
					$arrBody['notes_courtesy_call2']	= $arrRow['notes_courtesy_call2'];
					$arrBody['notes_renewal_call1']		= $arrRow['notes_renewal_call1'];
					$arrBody['notes_renewal_call2']		= $arrRow['notes_renewal_call2'];
					$arrBody['call_status'.$arrRow['call_status']]	= 'selected="selected"';
				}
				if(!isset($arrBody['success_message'])) {
					$arrBody['success_message'] = (isset($_SESSION['school_success_message']))?$_SESSION['school_success_message']:'';
				}
				if(isset($_SESSION['school_success_message'])) {
					unset($_SESSION['school_success_message']);
				}
				$body->assign($arrBody);
			}
		}
		$address_id = (isset($arrBody['address_id'])) ? $arrBody['address_id'] : 0;
		$arrAddress = array(
			'user_uid'		=> $arrBody['user_uid'],
			'tbl_name'		=> 'users_schools',
			'profile_uid'	=> $uid,
			'address_id'	=> $address_id
		);
		$objAddress = new plugin_address_details($arrAddress);
		$contentAddress = $objAddress->run();
		$body->assign(
			array(
				'tab.address'		=> $contentAddress->get_content(),
				'tab.schooladmin'	=> $objUser->getUserListForSchoolByType('schooladmin', $uid, 'profile_schooladmin'),
				'tab.schoolteacher'	=> $objUser->getUserListForSchoolByType('schoolteacher', $uid, 'profile_schoolteacher'),
				'tab.subscriptions'	=> $objSchool->getInvoiceList($arrBody['user_uid']),
				'tab.classes'		=> $this->getClasses($uid)
			)
		);
		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	public function getClasses($school_uid=null) {
		if($school_uid!=null && is_numeric($school_uid) && $school_uid>0) {
			$arrClasses = users_schools::getClassesBySchooluId($school_uid);
			if(is_array($arrClasses) && count($arrClasses)) {
				$arrRows = array();
				$body = make::tpl('body.admin.school.class.list');
				foreach($arrClasses as $arrClass) {
					$arrRows[] = make::tpl('body.admin.school.class.list.row')->assign($arrClass)->get_content();
				}
				$body->assign('list.rows', implode('', $arrRows));
				$body->assign('school_uid', $school_uid);
				return $body->get_content();
			} else {
				return make::tpl('body.admin.school.class.notfound')->assign('school_uid',$school_uid)->get_content();
			}
		} else {
			return '<span style="color:#30A4B1;font-weight:bold;padding-left:15px;">Classes are not found!</span>';
		}
	}
}
?>