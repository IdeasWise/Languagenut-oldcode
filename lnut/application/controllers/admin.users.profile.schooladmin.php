<?php

class schooladmin extends Controller {

	private $parts = array();

	public function __construct () {
		parent::__construct();
	$this->parts = config::get('paths');
		$this->profile();
	}

	protected function profile() {
		$objSchool		= new users_schools();
		//$arrSchool		= $objSchool->getSchool();
		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl('body.admin.users.profile.schooladmin');
		$objSchoolAdmin	= new profile_schooladmin();

		if(count( $_POST ) > 0){
			if($objSchoolAdmin->doSave() ) {
				// redirect to school admin list if all does well;
				$objSchoolAdmin->redirectToDynamic('/users/schooladmin');
			} else {
				$objSchoolAdmin->arrForm['school_id'] = $objSchool->SchoolListBox('school_id',$objSchoolAdmin->arrForm['school_id']);
				$body->assign( $objSchoolAdmin->arrForm );
			}
		} else {
			$objUser = new user($this->parts[4]);
			$objUser->load();
			$arrBody = array();
			if($this->parts[4] > 0){
				$arrBody['iuser_uid'] = $this->parts[4];
				
				$objSchoolAdmin->load(array(),$arrBody);
				$arrBody['vemail'] = $objUser->get_email();
				if($objSchoolAdmin->get_vfirstname() != '' ) {
					foreach( $objSchoolAdmin->TableData as $idx => $val ){
						$arrBody[$idx] = $val['Value'];
					}
				}
				$arrBody['school_id'] =$objSchool->SchoolListBox('school_id',$objSchoolAdmin->get_school_id());
				$body->assign( $arrBody );
			}
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
}
?>