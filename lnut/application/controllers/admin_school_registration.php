<?php

class admin_school_registration extends Controller {

	private $token		= 'register';
	private $arrTokens	= array (
		'register',
		'success'
	);
	private $parts		= array();

	public function __construct () {
		parent::__construct();
		$this->parts = config::get('paths');
		if(isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
			$this->token =  $this->parts[2];
		}
		
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	protected function doSuccess() {
		$skeleton		= make::tpl ('skeleton.admin');
		$body			= make::tpl ('body.admin.school-registration-success');
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
	/**
	 * doRegister() function will display school registration form on the screen
	 */
	protected function doRegister() {
		$skeleton			= make::tpl ('skeleton.admin');
		$body				= make::tpl ('body.admin.school-registration');
		
		$ObjLanguage 		= new language();
		$ObjectEmailTemplate= new school_registration_templates();
		$arrBody			= array();

		/**
		* If submit button is pressed then save form data to database
		*/
		if( isset($_POST['form_submit_button'])){
			$objSchool = new users_schools();
			if( $objSchool->SaveAdminSchoolRegistration() ){
				$objSchool->redirectTo('admin/school-registration/success/'); // redirect to list if all does well;
			} else {
				/**
				* If there is any error in saving form data to database or if empty data from form then error message and form data
				* repopulated to the user. 
				*/
				$objSchool->arrForm['locale']			= $ObjLanguage->LocaleSelectBox('locale', $objSchool->arrForm['locale']);
				$objSchool->arrForm['email_template']	= $ObjectEmailTemplate->getListBox('email_template',$objSchool->arrForm['email_template']);
				$body->assign( $objSchool->arrForm);
			}
		} else {
			$arrBody['locale']			= $ObjLanguage->LocaleSelectBox('locale');
			$arrBody['email_template']	= $ObjectEmailTemplate->getListBox('email_template');
		}

		$body->assign($arrBody);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}
}
?>