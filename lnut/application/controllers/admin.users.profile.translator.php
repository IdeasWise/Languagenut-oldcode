<?php

class admin_translator_controller extends Controller {
	
	private $parts = array();

	public function __construct () {
		parent::__construct();
		$this->parts = config::get('paths');
		$this->doProfile();
	}

	protected function doProfile() {
		/**
		 * Fetch the standard admin xhtml page template
		 */
		$skeleton	= make::tpl ('skeleton.admin');

		/**
		 * Fetch the translator profile template
		 */
		$body		= make::tpl ('body.admin.users.profile.translator');


		// Initialise translator profile object.
		$objTranslatorProfile	= new profile_translator();
		$objLanguage			= new language();

		// If form is submitted then perform/call save method to save changes.
		if(count( $_POST ) > 0 && isset($_POST['submit-edit-profile'])) {
			if($objTranslatorProfile->doSave() ) {
				// if everything is fine then redirect user to translator list page.
				$objTranslatorProfile->redirectTo('admin/users/translator/');
			} else {
				// if there is problem in saving user changes or any error then repopulate data with proper error message.
				$objTranslatorProfile->arrForm['locale_rights'] = $objLanguage->getLanguageComboBox('locale_rights[]' , explode(',',$objTranslatorProfile->arrForm['locale_rights']));
				$body->assign( $objTranslatorProfile->arrForm );
			}
		} else {
			if(isset($this->parts[4]) && is_numeric($this->parts[4]) && $this->parts[4] > 0) {
				// if $this->parts[4] is provided then initialize user object as well as load that user data.
				$objUser = new user($this->parts[4]);
				$objUser->load();

				$arrBody = array();
				$arrBody['iuser_uid'] = $this->parts[4];
				$objTranslatorProfile->load(array(),$arrBody);
				$arrBody['vemail'] = $objUser->get_email();
				if( $objTranslatorProfile->get_vfirstname() != '' ) {
					foreach( $objTranslatorProfile->TableData as $idx => $val ){
						$arrBody[$idx] = $val['Value'];
					}
					$arrBody['locale_rights'] = $objLanguage->getLanguageComboBox('locale_rights[]' , explode(',',$arrBody['locale_rights']));
				} else {
					$arrBody['locale_rights'] = $objLanguage->getLanguageComboBox('locale_rights[]' , array());
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