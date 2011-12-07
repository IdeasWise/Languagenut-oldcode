<?php

/**
 * landing.php
 */

class Landing extends Controller {

	public function __construct () {
		parent::__construct();

		$parts = config::get('paths');

		$this->templatepath = '';

		if(isset($parts[1])) {
			$this->templatepath = strtolower(preg_replace('/[^a-zA-Z0-9\-]/','',$parts[1]));
		}

		/* $this->page(); */


		/**
		 * Fetch the body content template
		 */
		$tpl = '';
		if(config::get('locale')!='') {
			$tpl.= 'body.landing.'.config::get('locale');
		} else {
			$tpl.= 'body.landing';
		}

		if(strlen($this->templatepath) > 0) {
			$tpl.= '.'.$this->templatepath;
		}

		$intro = new xhtml('body.landing.en.intro');
		$intro->load();

		$body = new xhtml ($tpl);
		$body->load();
		$body = utf8_encode($body->get_content());


		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml ('skeleton.landing');
		$skeleton->load();

		/**
		 * Fetch the page details
		 */
		$page = new page('landing');

		/**
		 * Build the output
		 */
		
		$skeleton->assign('pageID',(isset($parts[1]) ? 'landing-'.$parts[1] : 'landing'));
		
		
		$skeleton->assign (
			array (
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'intro'			=> $intro->get_content(),
				'locale'		=> config::get('locale')
			)
		);

		output::as_html($skeleton,true);

	}
}

?>