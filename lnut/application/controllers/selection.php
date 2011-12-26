<?php

/**
 * selection.php
 */

class Selection extends Controller {

	public function __construct () {
		parent::__construct();
		if(count($_SESSION) > 0 && isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in']==true) {
			$this->index();
		} else {
			output::redirect(config::url('logout/'));
		}
	}
	protected function index() {

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
				$tpl.= 'body.selection.'.$locale;
			} else {
				$tpl.= 'body.selection.en';
			}
		} else {
			$tpl.= 'body.selection.en';
		}

		if(strlen($this->templatepath) > 0) {
			$tpl.= '.'.$this->templatepath;
		}


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
		$page = new page('selection');

		/**
		 * Build the output
		 */
		
		$skeleton->assign('pageID','selection');
		
		
		$skeleton->assign (
			array (
				'title'			=> $page->title(),
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body,
				'selectionHeader' => ' | online langages in a nutshell',
				'locale'		=> config::get('locale')
			)
		);

		output::as_html($skeleton,true);

	}
}

?>