<?php

class component_sidebar {

	public static function fetch ($template='') {

		if($template && strlen($template) > 0) {
			/**
			 * If a sidebar is requested, load it
			 */
			$sidebar = 'sidebar.'.$template;

			$xhtml = new xhtml ('sidebar.'.$template);
			$xhtml->load();

			return $xhtml->get_content();
		} else {
			/**
			 * If one is not loaded, get it from the URL
			 * {{ uri }}{{ page }}_{{ pageextras }}/{{ sidebar }}_{{ sidebarextras }}
			 */
			$paths = config::get('paths');
			if(isset($paths[1])) {
				$sidebar_parts = explode('_', $paths[1]);
				if(count($sidebar_parts) > 0) {
					$sidebar = strtolower(preg_replace('/[^a-zA-Z\d-]/','',$sidebar_parts[0]));

					if(method_exists('component_sidebar', $sidebar)) {
						return self::$sidebar((array_key_exists(1, $sidebar_parts) ? $sidebar_parts[1] : '')) ;
					} else {
						return self::sidebar_menu();
					}
				} else {
					return self::sidebar_menu();
				}
			} else {
				return self::sidebar_menu();
			}
		}
	}

	public static function sidebar_menu ($extras = '') {
		/**
		 * Plugin: Ask a question
		 */
		$plugin_ask = component_ask::process();

		$xhtml = new xhtml ('sidebar.menu');
		$xhtml->load();
		$xhtml->assign(
			array(
				'plugin.ask'	=> $plugin_ask
			)
		);

		return $xhtml->get_content();
	}
}

?>