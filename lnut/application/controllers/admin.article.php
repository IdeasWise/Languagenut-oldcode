<?php

class admin_article extends Controller {

	private $token		= 'list';

	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'content',
		'pages',
		'content-translation',
		'group-content-translation',
		'groups'
	);
	private $arrPaths	= array();

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[2]) && in_array($this->arrPaths[2],$this->arrTokens)) {
			$this->token=str_replace(array('-'),array(''),$this->arrPaths[2]);
		}
		if($this->token != '') {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	private function doContent() {
		$this->load_controller('admin.article.content');
	}

	private function doContenttranslation() {
		$this->load_controller('admin.article.content.translation');
	}

	private function doGroupcontenttranslation() {
		$this->load_controller('admin.article.group.content.translation');
	}

	private function doGroups() {
		$this->load_controller('admin.article.groups');
	}

	private function doPages() {
		$this->load_controller('admin.article.pages');
	}

	protected function doContentOld() {
		if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])) {
			$objArticle = new article($this->arrPaths[3]);
			if($objArticle->get_valid()) {
				$objArticle->load();
				$skeleton	= config::getUserSkeleton();
				$objTabs	= new tabs();
				$body		= $objTabs->GetArticleTabs(
					'body',
					'article_translations',
					'content',
					'body.admin.article.content.form',
					$objArticle->get_uid(),
					$objArticle
				);
				$skeleton->assign ($body);
				output::as_html($skeleton,true);
			} else {
				output::redirect(config::url('admin/article/list/'));
			}
		} else {
			output::redirect(config::url('admin/article/list/'));
		}
	}

	protected function doAdd() {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl('body.admin.article.add');

		$objTemplate		= new template();
		$objArticleCategory	= new article_category();
		$objUnit			= new units();
		if(count($_POST) > 0) {
			$objArticle = new article();
			if(($response=$objArticle->isValidCreate())===true) {
				output::redirect(config::url('admin/article/list/'));
			} else {
				$objArticle->arrForm['template_uid'] = $objTemplate->getTemplateSelectBox('template_uid[]','template_uid');
				$objArticle->arrForm['article_category_uid'] = $objArticleCategory->getCategoryListBox('article_category_uid', $objArticle->arrForm['article_category_uid']);
				$objArticle->arrForm['unit_uid'] = $objUnit->getUnitSelectBox('unit_uid', $objArticle->arrForm['unit_uid']);
				$body->assign($objArticle->arrForm);
			}
		} else {
			$body->assign(
				array(
					'template_uid'=>$objTemplate->getTemplateSelectBox('template_uid[]','template_uid'),
					'article_category_uid'=>$objArticleCategory->getCategoryListBox('article_category_uid'),
					'unit_uid'=>$objUnit->getUnitSelectBox()

				)
			);
		}

		$body->assign(
			array(
				'article.translation'=>$this->getArticleTranslation()
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

		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl('body.admin.article.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : '';
		$objTemplate		= new template();
		$objArticleCategory	= new article_category();
		$objUnit			= new units();
		if($uid != '') {
			$objArticle = new article($uid);
			$objArticle->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objArticle->isValidUpdate())===true) {
					output::redirect(config::url('admin/article/list/'));
				} else {
					/*
					$objArticle->arrForm['template_uid'] = $objTemplate->getTemplateSelectBox('template_uid', $objArticle->arrForm['template_uid']);
					*/
					$objArticle->arrForm['article_category_uid'] = $objArticleCategory->getCategoryListBox('article_category_uid', $objArticle->arrForm['article_category_uid']);
					$objArticle->arrForm['unit_uid'] = $objUnit->getUnitSelectBox('unit_uid', $objArticle->arrForm['unit_uid']);
					$body->assign($objArticle->arrForm);
				}
			} else {
				foreach( $objArticle->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				if(isset($arrBody['template_uid'])) {
					/*
					$arrBody['template_uid'] = $objTemplate->getTemplateSelectBox('template_uid', $arrBody['template_uid']);
					*/
					$arrBody['article_category_uid'] = $objArticleCategory->getCategoryListBox('article_category_uid', $arrBody['article_category_uid']);
					$arrBody['unit_uid'] = $objUnit->getUnitSelectBox('unit_uid', $arrBody['unit_uid']);
				}
				$body->assign($arrBody);
			}
		} else {
			output::redirect(config::url('admin/article/list/'));
		}
		$body->assign(
			array(
				'article.translation'=>$this->getArticleTranslation($uid)
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
		if(isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) {
			$objArticle = new article($this->arrPaths[3]);
			$objArticle->delete();
			$objArticle->redirectTo('admin/article/list/');
		} else {
			output::redirect(config::url('admin/article/list/'));
		}
	}

	protected function doList () {
		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl('body.admin.article.list');
		$objArticle		= new article();
		$arrArticle		= $objArticle->getList();

		if($arrArticle && count($arrArticle) > 0) {
			$rows = array ();
			foreach($arrArticle as $uid=>$arrData) {
				$row = make::tpl('body.admin.article.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}
			$body->assign('rows',implode('',$rows));
			$page_display_title		= $objArticle->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objArticle->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objArticle->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objArticle->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
		}
		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}


	private function getArticleTranslation($article_uid=null){
		$query ="SELECT ";
		$query.="`prefix` AS `locale`, ";
		$query.="`uid`, ";
		$query.="( ";
			$query.="SELECT ";
			$query.="`title` ";
			$query.="FROM ";
			$query.="`article_translations` ";
			$query.="WHERE ";
			$query.="`article_translations`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`article_uid` = '".$article_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `name` ";
		$query.=", ( ";
			$query.="SELECT ";
			$query.="`width` ";
			$query.="FROM ";
			$query.="`article_translations` ";
			$query.="WHERE ";
			$query.="`article_translations`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`article_uid` = '".$article_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `width` ";
		$query.=", ( ";
			$query.="SELECT ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`article_translations` ";
			$query.="WHERE ";
			$query.="`article_translations`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`article_uid` = '".$article_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `height` ";
		$query.=", ( ";
			$query.="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`article_translations` ";
			$query.="WHERE ";
			$query.="`article_translations`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`article_uid` = '".$article_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `article_translation_uid` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::query($query);
		
		if(mysql_error() == '' && mysql_num_rows($result)) {
			$arrLi	= array();
			$arrDiv	= array();
			$xhtmlTemplate = 'body.admin.article.translation.add';
			if($article_uid!=null) {
				$xhtmlTemplate = 'body.admin.article.translation.edit';
			}
			while($row = mysql_fetch_array($result)) {

				if( ($article_uid==null || is_null($row['name']) || $row['name'] == '') && isset($_POST['tname'][$row['uid']])) {
					$row['name'] = $_POST['tname'][$row['uid']];
				}

				if( ($article_uid==null || is_null($row['width']) || $row['width'] == 0) && isset($_POST['twidth'][$row['uid']])) {
					$row['width'] = $_POST['twidth'][$row['uid']];
				}

				if( ($article_uid==null || is_null($row['height']) || $row['height'] == 0) && isset($_POST['theight'][$row['uid']])) {
					$row['height'] = $_POST['theight'][$row['uid']];
				}

				$localeLi = new xhtml('admin.locale.li');
				$localeLi->load();

				$localeLi->assign("tab_id", "locale-");
				$localeLi->assign("uid", $row['locale']);
				$localeLi->assign("prefix", $row['locale']);

				$arrLi[]	= $localeLi->get_content();
//				$arrLi[]	= '<li><a href="#locale-'.$row['locale'].'"><span>'.$row['locale'].'</span></a></li>';
				$arrDiv[]	= make::tpl($xhtmlTemplate)->assign($row)->get_content();


			}
			return make::tpl('body.admin.tabs.inner')->assign(
				array(
					'tabs.lis' => implode('',$arrLi),
					'tabs.divs' => implode('',$arrDiv)
					)
				)->get_content();
		}
		return ' ';
	}
}

/*

CREATE TABLE `article_template_type` (
`uid` TINYINT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`token` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`active` TINYINT( 1 ) UNSIGNED NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE `article_page` (
`uid` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`article_uid` INT( 11 ) UNSIGNED NOT NULL ,
`template_uid` INT( 11 ) UNSIGNED NOT NULL ,
`page_order` INT( 11 ) UNSIGNED NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `article` ADD `article_template_type_uid` TINYINT( 3 ) UNSIGNED NOT NULL AFTER `title`

ALTER TABLE `article_content` ADD `article_page_uid` BIGINT( 20 ) UNSIGNED NOT NULL AFTER `article_uid`

ALTER TABLE `article_content_translations` ADD `article_page_uid` BIGINT( 20 ) UNSIGNED NOT NULL AFTER `article_uid` 

ALTER TABLE `article` ADD `unit_uid` INT( 11 ) UNSIGNED NOT NULL AFTER `template_uid` 
*/
?>
