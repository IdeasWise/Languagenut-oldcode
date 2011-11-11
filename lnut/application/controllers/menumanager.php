<?php

/**
* menumanager.php
*/

class menumanager extends Controller {

	public function __construct () {
		parent::__construct();
		$this->show_list();
	}

	protected function show_list () {

		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.menumanager');
		$menu_list	= component_menumanager::generate_menu_list();

		// get the menu snippet
		$objTemplate	= new template();
		$menu_snippets	= array();
		$menu_snippets	= $objTemplate->get_templates(4);
		$arrOption		= array();
		if(count($menu_snippets) > 0) {
			foreach($menu_snippets as $uid => $data) {
				$arrOption[$data['uid']]  =   $data['name'];
			}
		}

		// assign menu list
		$body->assign(
				array(
					"menu.list"		=> $menu_list,
					"menu.snippet"	=> format::to_select(array("name" => "menu-snippet","options_only" => false,"id" => "menu-snippet"), $arrOption)
				)
		);

		//assign the body
		$skeleton->assign (
				array (
				'body' => $body
				)
		);
		output::as_html($skeleton,true);
	}
}

?>