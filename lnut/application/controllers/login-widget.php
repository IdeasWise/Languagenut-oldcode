<?php

/**
 * login-widget.php
 */

class loginWidget extends Controller {

	public function __construct () {
		parent::__construct();

		$this->paths = config::get('paths');

		if(isset($this->paths[1])) {
			if($this->paths[1] == 'iframe') {
				$this->page_iframe();
			} else if($this->paths[1] == 'noiframe') {
				$this->page_noiframe();
			} else if($this->paths[1] == 'popup') {
				$this->page_popup();
			} else if($this->paths[1] == 'direct') {
				$this->page_direct();
			} else {
				output::redirect(config::url('login-widget/'));
			}
		} else {
			$this->page_default();
		}
	}

	protected function page_default () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.basic');
		/**
		 * Fetch the body content template
		 */
		$body = make::tpl ('body.login-widget');


		/**
		 * Fetch the translated terms
		 */
		$query = "SELECT ";
		$query.= "`html` ";
		$query.= "FROM ";
		$query.= "`page_widget_translations` ";
		$query.= "WHERE ";
		$query.= "`locale`='".config::get('locale')."' ";
		$query.= "LIMIT 1";

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row		= mysql_fetch_assoc($result);
			$date		= date('d/m/Y');
			$content	= str_replace('{{ date }}',$date,stripslashes($row['html']));
		}

		$body->assign('content',$content);

		/**
		 * Fetch the page details
		 */
		 /*
		$page = new page('terms');
		*/

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> /*$page->title()*/'Login Widget',
				'keywords'		=> /*$page->keywords()*/'',
				'description'	=> /*$page->description()*/'',
				'body'			=> $body
			)
		);

		output::as_html($skeleton,true);

	}

	protected function page_iframe () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.basic');
		/**
		 * Fetch the body content template
		 */
		$body = make::tpl ('body.login-widget.iframe');

		/**
		 * Fetch the page details
		 */
		/*$page = new page('terms');*/

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> /*$page->title()*/'Login Widget',
				'keywords'		=> /*$page->keywords()*/'',
				'description'	=> /*$page->description()*/'',
				'body'			=> $body
			)
		);
		output::as_html($skeleton,true);

	}

	protected function page_noiframe () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.basic');

		/**
		 * Fetch the body content template
		 */
		$body = make::tpl ('body.login-widget.noiframe');

		/**
		 * Fetch the page details
		 */
		/*$page = new page('terms');*/

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> /*$page->title()*/'Login Widget',
				'keywords'		=> /*$page->keywords()*/'',
				'description'	=> /*$page->description()*/'',
				'body'			=> $body
			)
		);

		output::as_html($skeleton,true);

	}

	protected function page_popup () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.basic');

		/**
		 * Fetch the body content template
		 */
		$body = make::tpl ('body.login-widget.popup');

		/**
		 * Fetch the page details
		 */
		/*$page = new page('terms');*/

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> /*$page->title()*/'Login Widget',
				'keywords'		=> /*$page->keywords()*/'',
				'description'	=> /*$page->description()*/'',
				'body'			=> $body
			)
		);

		output::as_html($skeleton,true);

	}

	protected function page_direct () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.basic');

		/**
		 * Fetch the body content template
		 */
		$body = make::tpl ('body.login-widget.direct');

		/**
		 * Fetch the page details
		 */
		/*$page = new page('terms');*/

		/**
		 * Build the output
		 */
		$skeleton->assign (
			array (
				'title'			=> /*$page->title()*/'Login Widget',
				'keywords'		=> /*$page->keywords()*/'',
				'description'	=> /*$page->description()*/'',
				'body'			=> $body
			)
		);

		output::as_html($skeleton,true);

	}
}

?>