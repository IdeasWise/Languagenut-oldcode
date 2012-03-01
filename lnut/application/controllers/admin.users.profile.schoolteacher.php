<?php

class schoolteacher extends Controller {

	private $parts = array();

	public function __construct () {
		parent::__construct();
		$this->parts = config::get('paths');
		$this->doProfile();
	}

	protected function doProfile() {
		$objSchool			= new users_schools();
		//$arrSchool			= $objSchool->getSchool();
		$uid				= 0;
		$skeleton			= config::getUserSkeleton();
		$body				= make::tpl('body.admin.users.profile.schoolteacher');
		$objSchoolTeacher	= new profile_schoolteacher();

		if(count( $_POST ) > 0){
			if($objSchoolTeacher->doSave()) {
				// redirect to school teacher list if all does well;
				$objSchoolTeacher->redirectToDynamic('/users/schoolteacher');
			} else {
				$uid = $objSchoolTeacher->arrForm['school_id'];
				$objSchoolTeacher->arrForm['school_id'] = $objSchool->SchoolListBox('school_id',$objSchoolTeacher->arrForm['school_id']);
				$body->assign($objSchoolTeacher->arrForm );
			}
		} else {
			$arrBody = array();
			if($this->parts[4] > 0){
				$arrBody['iuser_uid'] = $this->parts[4];
				$objSchoolTeacher->load(array(),$arrBody);
				$objUser = new user($this->parts[4]);
				$objUser->load();
				if($objUser->get_email() != '') {
					$arrBody['email'] = $objUser->get_email();
				}

				if($objSchoolTeacher->get_vfirstname() != '' ) {
					foreach( $objSchoolTeacher->TableData as $idx => $val ){
						$arrBody[$idx] = $val['Value'];
					}
				}
				$uid = (isset($arrBody['school_id']))?$arrBody['school_id']:'';
				$arrBody['school_id'] = $objSchool->SchoolListBox('school_id',$objSchoolTeacher->get_school_id());
				$body->assign( $arrBody );
			}
		}
		$objUser = new user();
		
		$body->assign (
			array (
				'tab.schooladmin' => $objUser->getUserListForSchoolByType('schooladmin', $uid, 'profile_schooladmin')
				)
		);
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

}
?>