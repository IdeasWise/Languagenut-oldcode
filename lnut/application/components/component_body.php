<?php

class component_body {

	public static function fetch ($template='', $menu='', $selected='') {
		/**
		 * Fetch the menu and add the selected class
		 */
		$xhtml = new xhtml ('body.'.$template);
		$xhtml->load();

		switch($menu) {
			case 'mortgages':
			case 'insurances':
			case 'loans':
			case 'surveys':
			case 'calculators':
			case 'contact':
				$xhtml->assign('menu', component_menu::fetch($menu, $selected));
			break;
		}

		return $xhtml->get_content();
	}
}

?>