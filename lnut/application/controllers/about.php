<?php

/**
 * about.php
 */

class About extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('en/skeleton.public');
		//$skeleton->load();

		/**
		 * Fetch the public menu
		 */
		$menu				= component_menu::fetch('top');

		/**
		 * Fetch the body content
		 */
		$body				= component_body::fetch('index'); // fetch body dynamically from the url?

		/**
		 * Fetch the Callback Plugin content
		 */
		$plugin_callback	= component_callback::process();

		/**
		 * Fetch the sidebar
		 */
		$sidebar			= component_sidebar::fetch(); // fetch sidebar dynamically from the url?

		/**
		 * Fetch the page details
		 */
		$page		= new page('index');

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'				=> $page->title(),
				'keywords'			=> $page->keywords(),
				'description'		=> $page->description(),
				'menu.top'			=> $menu,
				'body'				=> $body,
				'sidebar'			=> $sidebar,
				'plugin.callback'	=> $plugin_callback
			)
		);

		output::as_html($skeleton,true);
	}
}

?>