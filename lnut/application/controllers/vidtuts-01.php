<?php

/**
 * vidtuts.php
 */

class Vidtuts extends Controller {

	public function __construct () {
		parent::__construct();
                  
		$this->page();
	}

	protected function page () {
		$this->page_default();
	}

	protected function page_default () {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml ('skeleton.basic');
		$skeleton->load();
		$content="";
		/**
		 * Fetch the body content template
		 */
		if(config::get('locale')!='') {
			$body = new xhtml ('body.vidtuts.'.config::get('locale'));
		} else {
			$body = new xhtml ('body.vidtuts');
		}
		$body->load();

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

		$result = database::query($query);

		if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);

			$date = date('d/m/Y');

			$content = str_replace('{{ date }}',$date,stripslashes($row['html']));
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
				'title'			=> /*$page->title()*/'Video Tutorials',
				'keywords'		=> $page->keywords(),
				'description'	=> $page->description(),
				'body'			=> $body
			)
		);
                
		output::as_html($skeleton,true);
                
	}
}

?>