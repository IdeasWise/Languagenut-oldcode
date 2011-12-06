<?php

/**
 * account.currency.php
 */

class admin_currency extends Controller {
	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index() {
		$skeleton			= config::getUserSkeleton();
		$objTranslatorTabs	= new translator_tabs();
		$skeleton->assign ( $objTranslatorTabs->get_tabs_and_contents_of_pricing() );
		output::as_html($skeleton,true);
	}
}

?>