<?php

class admin_affiliate_controller extends Controller {

	private $parts = array();

	public function __construct () {
		parent::__construct();
		$this->parts = config::get('paths');
		$this->profile();
	}

	protected function profile() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.users.profile.affiliate');
		$objAffiliate	= new profile_affiliate();

		if(count( $_POST ) > 0){
			if( $objAffiliate->doSave()) {
				// redirect to affiliate list if all does well;
				$objAffiliate->redirectTo('admin/users/affiliate/');
			} else {
				$body->assign( $objAffiliate->arrForm );
			}
		}
		else {
			$objUser = new user($this->parts[4]);
			$objUser->load();
			
			$arrBody = array();
			
			if($this->parts[4] > 0){
				$arrBody['iuser_uid'] = $this->parts[4];
				$objAffiliate->load(array(),$arrBody);
				$arrBody['vemail'] = $objUser->get_email();
				if($objAffiliate->get_vfirstname() != '' ) {
					foreach( $objAffiliate->TableData as $idx => $val ){
						$arrBody[$idx] = $val['Value'];
					}
				}
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