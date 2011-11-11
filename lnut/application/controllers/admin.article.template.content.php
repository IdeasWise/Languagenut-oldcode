<?php

class admin_article_template_content extends Controller {

	private $token			= 'list';
	private $arrTokens		= array (
		'list',
		'edit',
		'add',
		'delete',
		'translation',
		'groups'
	);
	private $arrPaths		= array();
	private $template_uid	=null;

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');

		$this->isValidTemplateUid();

		if(isset($this->arrPaths[4]) && in_array($this->arrPaths[4], $this->arrTokens)) {
			$this->token =  $this->arrPaths[4];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token); 
			$this->$method();
		}
	}

	private function isValidTemplateUid() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0) {
			$objTemplate = new template($this->arrPaths[3]);
			if($objTemplate->get_valid()) {
				$this->template_uid=$this->arrPaths[3];
			} else {
				// redirect back to article-template list
				output::redirect(config::url('admin/article-template/list/'));
			}
		} else {
			// redirect back to article-template list
			output::redirect(config::url('admin/article-template/list/'));
		}
	}

	private function doGroups() {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.content.group-content.list');
		$objTemplateGroupContent	= new template_group_content();
		$template_content_uid = (isset($this->arrPaths[5]) && is_numeric($this->arrPaths[5]))?$this->arrPaths[5]:null; 
		$arrGroups	= $objTemplateGroupContent->getContentGroupList($this->template_uid,$template_content_uid);

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
				'template_uid'=>$this->template_uid
			)
		);
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doAdd() {
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.article.template.content.add');
		$support_language_uid	= 0;
		$arrLearnable			= array();
		if(count($_POST) > 0) {
			$objTemplateContent = new template_content();
			if(($response=$objTemplateContent->isValidCreate())===true) {
				output::redirect(config::url('admin/article-template/content/'.$this->template_uid.'/list/'));
			} else {
				$body->assign($objTemplateContent->arrForm);
			}
		}
		$body->assign(
			array(
				'template_uid'=>$this->template_uid
			)
		);
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doEdit() {

		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.content.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[5]) && (int)$this->arrPaths[5] > 0) ? $this->arrPaths[5] : '';

		if($uid != '') {
			$objTemplateContent = new template_content($uid);
			$objTemplateContent->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objTemplateContent->isValidUpdate())===true) {
					output::redirect(config::url('admin/article-template/content/'.$this->template_uid.'/list/'));
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
			output::redirect(config::url('admin/article-template/content/'.$this->template_uid.'/list/'));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}


	protected function doList () {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.content.list');
		$objTemplateContent	= new template_content();
		$arrTemplateContent	= $objTemplateContent->getList($this->template_uid);

		if($arrTemplateContent && count($arrTemplateContent) > 0) {
			$rows = array ();
			foreach($arrTemplateContent as $uid=>$arrData) {
				$rows[] = make::tpl('body.admin.article.template.content.list.row')->assign($arrData)->get_content();
			}

			$page_display_title		= $objTemplateContent->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objTemplateContent->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objTemplateContent->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objTemplateContent->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('rows',implode('',$rows));
		}
		$body->assign(
			array(
				'template_uid'=>$this->template_uid
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
			$objTemplateContent = new template_content($this->arrPaths[5]);
			$objTemplateContent->delete();
			//$objTemplateTranslation = new template_translation();
			//$objTemplateTranslation->DeleteTemplateTranslation($this->arrPaths[3]);
			
			$objTemplateContent->redirectTo('admin/article-template/content/'.$this->template_uid.'/list/');
		} else {
			output::redirect(config::url('admin/article-template/content/'.$this->template_uid.'/list/'));
		}
	}
}

?>