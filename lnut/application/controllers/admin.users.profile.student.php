<?php

class schoolteacher extends Controller {

	private $parts = array();

	public function __construct () {
		parent::__construct();
		$this->parts = config::get('paths');
		$this->doProfile();
	}

	protected function doProfile() {
		$objSchool		= new users_schools();
		//$arrSchool		= $objSchool->getSchool();
		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl('body.admin.users.profile.student');
		$objStudent		= new profile_student();
		$arrBody		= array();
		if(count( $_POST ) > 0){

			if($objStudent->doSave()) {
				// redirect to student list if all does well;
				$objStudent->redirectToDynamic('/users/student');
			} else {
				$objStudent->arrForm['school_id'] = $objSchool->SchoolListBox('school_id',$objStudent->arrForm['school_id']);
				$body->assign( $objStudent->arrForm );
			}
		} else {
			if($this->parts[4] > 0){

				$arrBody['iuser_uid'] = $this->parts[4];
				$objStudent->load(array(),$arrBody);
				
				if($objStudent->get_vfirstname() != '' ) {
					foreach( $objStudent->TableData as $idx => $val ){
						$arrBody[$idx] = $val['Value'];
					}
				}
				$arrBody['school_id'] = $objSchool->SchoolListBox('school_id',$objStudent->get_school_id());
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