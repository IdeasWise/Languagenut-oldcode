<?php

class admin_article_template extends Controller {

	private $token		= 'list';

	private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'content',
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

	private function doGroups() {
		$this->load_controller('admin.article.template.groups');
	}

	private function doContent() {
		$this->load_controller('admin.article.template.content');
	}

	private function doContenttranslation() {
		$this->load_controller('admin.article.template.content.translation');
	}

	private function doGroupcontenttranslation() {
		$this->load_controller('admin.article.template.group.content.translation');
	}

	protected function doAdd() {
		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.article.template.add');
		$support_language_uid	= 0;
		$arrLearnable			= array();
		if(count($_POST) > 0) {
			$objTemplate = new template();
			if(($response=$objTemplate->isValidCreate())===true) {
				output::redirect(config::url('admin/article-template/list/'));
			} else {
				$body->assign($objTemplate->arrForm);
			}
		}

		$body->assign(
			array(
				'template.translation'=>$this->getArticleTranslation()
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
		$body			= make::tpl('body.admin.article.template.edit');
		$arrBody		= array();
		$uid			= (isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) ? $this->arrPaths[3] : '';

		if($uid != '') {
			$objTemplate = new template($uid);
			$objTemplate->load();
			$arrBody['uid'] = $uid;
			if(count($_POST) > 0) {
				if(($response = $objTemplate->isValidUpdate())===true) {
					output::redirect(config::url('admin/article-template/list/'));
				} else {
					$body->assign($objTemplate->arrForm);
				}
			} else {
				foreach( $objTemplate->TableData as $idx => $val ){
					$arrBody[$idx] = $val['Value'];
				}
				$body->assign($arrBody);
			}

			$body->assign(
				array(
					'template.translation'=>$this->getArticleTranslation($uid)
				)
			);


		} else {
			output::redirect(config::url('admin/article-template/list/'));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	private function getArticleTranslation($template_uid=null){
		$query ="SELECT ";
		$query.="`prefix` AS `locale`, ";
		$query.="`uid`, ";
		$query.="( ";
			$query.="SELECT ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_translation`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`template_uid` = '".$template_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `name` ";
		$query.=", ( ";
			$query.="SELECT ";
			$query.="`width` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_translation`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`template_uid` = '".$template_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `width` ";
		$query.=", ( ";
			$query.="SELECT ";
			$query.="`height` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_translation`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`template_uid` = '".$template_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `height` ";
		$query.=", ( ";
			$query.="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`template_translation` ";
			$query.="WHERE ";
			$query.="`template_translation`.`language_uid` = `language`.`uid` ";
			$query.="AND ";
			$query.="`template_uid` = '".$template_uid."' ";
			$query.="LIMIT 1";
		$query.=") AS `template_translation_uid` ";
		$query.="FROM ";
		$query.="`language` ";
		$query.= "ORDER BY ";
		$query.= "`prefix` ASC";
		$result = database::query($query);
		if(mysql_error() == '' && mysql_num_rows($result)) {
			$arrLi	= array();
			$arrDiv	= array();
			$xhtmlTemplate = 'body.admin.article.template.translation.add';
			if($template_uid!=null) {
				$xhtmlTemplate = 'body.admin.article.template.translation.edit';
			}
			while($row = mysql_fetch_array($result)) {

				if( ($template_uid==null || is_null($row['name']) || $row['name'] == '') && isset($_POST['tname'][$row['uid']])) {
					$row['name'] = $_POST['tname'][$row['uid']];
				}

				if( ($template_uid==null || is_null($row['width']) || $row['width'] == 0) && isset($_POST['twidth'][$row['uid']])) {
					$row['width'] = $_POST['twidth'][$row['uid']];
				}

				if( ($template_uid==null || is_null($row['height']) || $row['height'] == 0) && isset($_POST['theight'][$row['uid']])) {
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

	protected function doList () {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.article.template.list');
		$objTemplate	= new template();
		$arrTemplates	= $objTemplate->getList();

		if($arrTemplates && count($arrTemplates) > 0) {
			$rows = array ();
			foreach($arrTemplates as $uid=>$arrData) {
				$arrData['created_date'] = date('d/m/Y H:i:s', strtotime($arrData['created_date']));
				$row = make::tpl('body.admin.article.template.list.row')->assign($arrData);
				$rows[] = $row->get_content();
			}

			$page_display_title		= $objTemplate->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');

			$page_navigation = $objTemplate->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>');
			$page_navigation .= $objTemplate->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>', ' &raquo ');
			$page_navigation .= $objTemplate->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('rows',implode('',$rows));
		}

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

		protected function doDelete() {
		if(isset($this->arrPaths[3]) && (int)$this->arrPaths[3] > 0) {
			$objTemplate = new template($this->arrPaths[3]);
			$objTemplate->delete();
			//$objTemplateTranslation = new template_translation();
			//$objTemplateTranslation->DeleteTemplateTranslation($this->arrPaths[3]);
			
			$objTemplate->redirectTo('admin/article-template/list/');
		} else {
			output::redirect(config::url('admin/article-template/list/'));
		}
	}
}

?>