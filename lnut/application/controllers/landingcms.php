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
		$locale = config::get('locale');
		if($locale!='') {
			if(in_array($locale,array('au','us','nz','ae','lb'))) {
				$tpl.= 'body.landing.cms.'.$locale;
			} else {
				$tpl.= 'body.landing.cms.en';
			}
		} else {
			$tpl.= 'body.landing.cms';
		}

		if(strlen($this->templatepath) > 0) {
			$tpl.= '.'.$this->templatepath;
		}

		$intro = new xhtml('body.landing.cms.introExample');
		$intro->load();
		
		$menu = new xhtml('body.landing.cms.menuExample');
		$menu->load();
		
		$sidebar = new xhtml('body.landing.cms.sidebarExample');

		$body = new xhtml ($tpl);
		$body->load();
		$body = utf8_encode($body->get_content());


		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml ('skeleton.landing.cms');
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
				'title'				=> $page->title(),
				'keywords'			=> $page->keywords(),
				'description'		=> $page->description(),
				'body'				=> $body,
				'intro'				=> $intro->get_content(),
				'menu'				=> $menu->get_content(),
				'sidebar'			=> $sidebar->get_content(),
				'page_title'		=> 'Welcome', // to be replaced by a string in the cms
				'sidebar_sprite'	=> 'http://images.languagenut.com/en/landing/freeSprite.jpg', // also to be in the cms
				'locale'			=> config::get('locale')
			)
		);

		output::as_html($skeleton,true);

	}
}

?>