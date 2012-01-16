<?php

/**
 * content.php
 */

class content extends Controller {

	public function __construct () {
		parent::__construct();

		$arrPath = config::get('paths');
		
		if(isset($arrPath[1])) {
			$arrContent = landing_cms::get_langing_page_by_slug($arrPath[1]);
			if($arrContent===false) {
				$arrContent = landing_cms::get_langing_page_by_slug();
			}
		} else {
			$arrContent = landing_cms::get_langing_page_by_slug();
		}

		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = make::tpl ('skeleton.landing.cms');
		
		$skeleton->assign('pageID',(isset($parts[1]) ? 'landing-'.$parts[1] : 'landing'));
		
		
		$skeleton->assign (
			array (
				'title'				=> $arrContent['meta_title'],
				'keywords'			=> $arrContent['meta_keywords'],
				'description'		=> $arrContent['meta_description'],
				'body'				=> $arrContent['body_content'],
				'intro'				=> str_replace(array('&#123;&#123;', '&#125;&#125;'), array('{{', '}}'),$arrContent['intro_content']),
				'menu'				=> str_replace(array('&#123;&#123;', '&#125;&#125;'), array('{{', '}}'),$arrContent['menu_content']),
				'sidebar'			=> str_replace(array('&#123;&#123;', '&#125;&#125;'), array('{{', '}}'),$arrContent['sidebar_content']),
				'page_title'		=> $arrContent['page_title'], //replaced by a string in the cms
				'sidebar_sprite'	=> $arrContent['sidebar_sprite_image'], // also to be in the cms
				'locale'			=> config::get('locale')
			)
		);
		output::as_html($skeleton,true);
	}
}

?>