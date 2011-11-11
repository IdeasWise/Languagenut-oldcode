<?php

/**
 * press.php
 */

class Press extends Controller {

	public function __construct () {
		parent::__construct();

		$parts = config::get('paths');

		$this->story = '';

		if(isset($parts[1])) {
			$this->story = strtolower(preg_replace('/[^a-zA-Z0-9\-]/','',$parts[1]));
		}

		$this->page();

	}

	protected function page () {
		$this->page_default();
	}

	protected function page_default () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml ('skeleton.basic.press');
		$skeleton->load();
		$content="";

		/**
		 * Fetch the body content template
		 */
		$tpl = '';
		if(config::get('locale')!='') {
			$tpl.= 'body.press.'.config::get('locale');
		} else {
			$tpl.= 'body.press';
		}

		if(strlen($this->story) > 0) {
			$tpl.= '.'.$this->story;
		}

		$body = new xhtml ($tpl);
		$body->load();
		$body = utf8_encode($body->get_content());

		/**
		 * Fetch the page details
		 */
		$page = new page('terms');

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> 'Press',
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'locale'		=> config::get('locale')
			)
		);

		output::as_html($skeleton,true);

	}
}

?>