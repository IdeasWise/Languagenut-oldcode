<?php

/**
 * terms.php
 */

class Terms extends Controller {

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
		$body = make::tpl('body.terms');

		/**
		 * Fetch the translated terms
		 */
		$query = "SELECT ";
		$query.= "`html` ";
		$query.= "FROM ";
		$query.= "`page_terms_translations` ";
		$query.= "WHERE ";
		$query.= "`locale`='".config::get('locale')."' ";
		$query.= "LIMIT 1";

		$result = database::arrQuery($query, 1);
//		$result = database::query($query);
		$content="";
		if(count($result)>0) {
			$row		= $result[0];
			$date		= date('d/m/Y');
			$content	= str_replace('{{ date }}',$date,stripslashes($row['html']));
		}

		$body->assign('content',$content);

		/**
		 * Fetch the page details
		 */
		$page = new page('terms');

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