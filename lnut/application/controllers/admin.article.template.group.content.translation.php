<?php

class admin_article_template_group_content_translation extends Controller {

	private $token						= 'list';
	private $arrTokens					= array (
		'list',
		'edit',
		'add',
		'delete'
	);
	private $arrPaths					= array();
	private $template_translation_uid	= null;
	private $template_uid				= null;
	private $template_language			= null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidTemplateTranslationUid();

		if(isset($this->arrPaths[4]) && in_array($this->arrPaths[4], $this->arrTokens)) {
			$this->token =  $this->arrPaths[4];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidTemplateTranslationUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objTemplateTranslation = new template_translation($this->arrPaths[3]);
			if($objTemplateTranslation->get_valid()) {
				$objTemplateTranslation->load();
				$this->template_translation_uid=$this->arrPaths[3];
				$this->template_uid = $objTemplateTranslation->get_template_uid();
				$objLanguage = new language($objTemplateTranslation->get_language_uid());
				$objLanguage->load();
				$this->template_language = $objLanguage->get_name();

			} else {
				// redirect back to article-template list
				output::redirect(config::url('admin/article-template/list/'));
			}
		} else {
			// redirect back to article-template list
			output::redirect(config::url('admin/article-template/list/'));
		}
	}

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.content.translation.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) ? $this->arrPaths[5] : '';

		if($uid != '') {
			$objTemplateContent = new template_content_translations($uid);
			$objTemplateContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objTemplateContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article-template/content-translation/'.$this->template_translation_uid.'/list/'));
				} else {
					if(isset($objTemplateContent->arrForm['item_type_uid']) && $objTemplateContent->arrForm['item_type_uid'] == 2) {
						$objTemplateContent->arrForm['type_text_checked'] = 'checked="checked"';
					}
					$body->assign($objTemplateContent->arrForm);
				}
			} else {
				foreach( $objTemplateContent->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				if(isset($arrBody['item_type_uid']) && $arrBody['item_type_uid'] == 2) {
					$arrBody['type_text_checked'] = 'checked="checked"';
				}
				$body->assign($arrBody);
			}

		} else {
			output::redirect(config::url('admin/article-template/content-translation/'.$this->template_translation_uid.'/list/'));
		}
		
		$body->assign(
			array(
				'template_translation_uid'	=>$this->template_translation_uid,
				'template_uid'				=>$this->template_uid,
				'template_language'			=>$this->template_language
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}


	protected function doList () {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.group.content.translation.list');
		$objTemplateGroupContent	= new template_group_content_translations();
		$arrGroups	= $objTemplateGroupContent->getList($this->template_translation_uid);

		if(count($arrGroups) > 0) {
			$rows = array ();
			foreach($arrGroups as $uid=>$arrData) {
				$rows[] = make::tpl('body.admin.article.template.group.content.list.row')->assign($arrData)->get_content();
			}

			$page_display_title		= $objTemplateGroupContent->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objTemplateGroupContent->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objTemplateGroupContent->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objTemplateGroupContent->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('rows',implode('',$rows));
		}

		$body->assign(
			array(
				'template_translation_uid'	=>$this->template_translation_uid,
				'template_uid'				=>$this->template_uid,
				'template_language'			=>$this->template_language
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);

	}

		protected function doDelete() {
		if(isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) {
			$objTemplateContent = new template_content_translations($this->arrPaths[5]);
			$objTemplateContent->delete();
			//$objTemplateTranslation = new template_translation();
			//$objTemplateTranslation->DeleteTemplateTranslation($this->arrPaths[3]);
			
			$objTemplateContent->redirectTo('admin/article-template/content-translation/'.$this->template_translation_uid.'/list/');
		} else {
			output::redirect(config::url('admin/article-template/content-translation/'.$this->template_translation_uid.'/list/'));
		}
	}
}

?>