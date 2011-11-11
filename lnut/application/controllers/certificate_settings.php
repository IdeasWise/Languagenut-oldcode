<?php

class certificate_settings extends Controller {	

	public function __construct () {
		parent::__construct();
		$this->ShowPage();
	}
	
	private function ShowPage() {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl ('body.admin.certificate.settings');
		$objTabs	= new tabs();

		$body->assign(
			array(
				'certificate.images' => $objTabs->get_tabs_and_contents_for_certificate()
			)
		);
		$body->assign(
			array(
				'certificate.messages' => $objTabs->get_tabs_and_contents_for_certificate_messages()
			)
		);
		$body->assign(
			array(
				'certificate.fontsize' => $objTabs->get_tabs_and_contents_for_certificate_fontsize()
			)
		);
		$skeleton->assign(
			array(
				'body'=>$body
			)
		);
		output::as_html($skeleton, true);
	}
}

?>