<?php

class component_menu {

	public static function fetch ($template='', $selected='') {
		/**
		 * Which menu item do we select?
		 */
		if($selected == '') {
			$selected = config::get('controller');
		}

		/**
		 * Fetch the menu and add the selected class
		 */
		$xhtml = new xhtml ('menu.'.$template);
		$xhtml->load();
		$xhtml->assign('selected_'.$selected, ' class="selected"');

		return $xhtml->get_content();
	}
}

?>