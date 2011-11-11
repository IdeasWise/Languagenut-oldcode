<?php

/**
 * account.certificate.php
 */

class account_certificate extends Controller {
	
	public function __construct () {
		parent::__construct();
		$this->index();
	}

	private function index() {
		
		$skeleton			= make::tpl('skeleton.account.translator');
		$body				= make::tpl('body.admin.certificate.settings');
		$objTranslatorTabs	= new translator_tabs();
		
		$body->assign(
			array(
				'certificate.images' => $objTranslatorTabs->get_tabs_and_contents_for_certificate()
			)
		);
		$body->assign(
			array(
				'certificate.messages' => $objTranslatorTabs->get_tabs_and_contents_for_certificate_messages()
			)
		);
		$body->assign(
			array(
				'certificate.fontsize' => $objTranslatorTabs->get_tabs_and_contents_for_certificate_fontsize()
			)
		);
		$skeleton->assign(array('body'=>$body));
		output::as_html($skeleton, true);
	}
}

?>