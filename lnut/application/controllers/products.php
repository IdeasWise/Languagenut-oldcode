<?php

/**
 * products.php
 */

class Products extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl('skeleton.basic');

		/**
		 * Fetch the body content template
		 */
		$body = make::tpl('body.products.'.config::get('locale'));

		/**
		 * Fetch the page details
		 */
		$page = new page('products');

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body
			)
		);
		output::as_html($skeleton,true);
	}
}

?>