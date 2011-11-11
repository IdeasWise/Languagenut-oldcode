<?php

/**
 * privacy.php
 */

class Privacy extends Controller {

	public function __construct () {
		parent::__construct();
		$this->page();
	}

	protected function page () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton	= make::tpl ('skeleton.basic');

		/**
		 * Fetch the body content
		 */
		$body		= make::tpl ('body.privacy');


		/**
		 * Fetch the translated privacy policy
		 */
		$query = "SELECT ";
		$query.= "`html` ";
		$query.= "FROM ";
		$query.= "`page_privacy_translations` ";
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
		$page = new page('privacy');

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