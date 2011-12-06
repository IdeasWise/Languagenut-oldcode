<?php

/**
 * api.writing.php
 */

class API_Writing extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$method = 'getInvalidLink';
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}

	private function getInvalidLink() {
		die('Invalid Link!!!');
	}
	
	private function submitArticle() {
		/*
		$arrJson = array(
			'task_uid'				=>1,
			'task_difficulty_uid'	=>2,
			'difficulty_level_uid'	=>2,
			'unit_uid'				=>1,
			'article_uid'			=>1,
			'learning_language_uid'	=>14,
			'support_language_uid'	=>14,
			'article_content'		=>array(
				array(
					'article_content_item_uid'	=>35,
					'content'					=>'bla bla bla'
				),
				array(
					'article_content_item_uid'	=>36,
					'content'					=>'bla bla bla'
				),
				array(
					'article_content_item_uid'	=>37,
					'content'					=>'bla bla bla'
				)
			)
		);

		echo json_encode($arrJson);
		*/
		
		$objJson = false;
		if(isset($_REQUEST['data'])) {
			$objJson = json_decode($_REQUEST['data']);
		}

		if(isset($objJson->article_content) && count($objJson->article_content) && isset($objJson->article_uid)) { 
			$objArticleScore = new articlescore();
			$user_uid = (isset($_SESSION['user']['uid']))?$_SESSION['user']['uid']:1;

			$objArticleScore->set_student_user_uid($user_uid);
			if(isset($_SESSION['user']['class_uid'])) {
				$objArticleScore->set_class_uid($_SESSION['user']['class_uid']);
			}
			$objArticleScore->set_task_uid($objJson->task_uid);
			$objArticleScore->set_task_difficulty_uid($objJson->task_difficulty_uid);
			$objArticleScore->set_difficulty_level_uid($objJson->difficulty_level_uid);
			$objArticleScore->set_unit_uid($objJson->unit_uid);
			$objArticleScore->set_article_uid($objJson->article_uid);
			$objArticleScore->set_learning_language_uid($objJson->learning_language_uid);
			$objArticleScore->set_support_language_uid($objJson->support_language_uid);
			$objArticleScore->set_recorded_dts(date('Y-m-d H:i:s'));
			$articlescore_uid = $objArticleScore->insert();
			$objArticlescoreDetail = new articlescore_detail();
			foreach($objJson->article_content as $arrContent) {
				$objArticlescoreDetail->set_articlescore_uid($articlescore_uid);
				$objArticlescoreDetail->set_article_content_item_uid($arrContent->article_content_item_uid);
				$objArticlescoreDetail->set_content($arrContent->content);
				$objArticlescoreDetail->insert();
			}
			if(isset($_SESSION['user']['class_uid'])) {
				$ObjTeacherNotifications = new teacher_notifications();
				$ObjTeacherNotifications->setNotification(
					$_SESSION['user']['class_uid'],
					$user_uid,
					$articlescore_uid
				);
			}
			$this->CreateArticleClone(
				$objJson->article_uid,
				$objJson->learning_language_uid,
				$articlescore_uid
			);
			echo '{"success":"true"}';
		} else {
			echo '{"success":"false"}';
		}
		//$this->CreateArticleClone(1,14);
	}

	private function CreateArticleClone($article_uid=null,$language_uid=null,$articlescore_uid=null) {
		if($article_uid==null || $language_uid==null || $articlescore_uid==null) {
			return false;
		}
		$query ="INSERT ";
		$query.="INTO ";
		$query.="`clone_article` ";
		$query.="( ";
			$query.="`articlescore_uid`,";
			$query.="`article_uid`, ";
			$query.="`title`, ";
			$query.="`article_template_type_uid`, ";
			$query.="`unit_uid`, ";
			$query.="`article_category_uid`, ";
			$query.="`template_uid`, ";
			$query.="`token`, ";
			$query.="`created_date`, ";
			$query.="`locked`, ";
			$query.="`published` ";
		$query.=") SELECT ";
			$query.="'".$articlescore_uid."',";
			$query.="`uid`, ";
			$query.="`title`, ";
			$query.="`article_template_type_uid`, ";
			$query.="`unit_uid`, ";
			$query.="`article_category_uid`, ";
			$query.="`template_uid`, ";
			$query.="`token`, ";
			$query.="`created_date`, ";
			$query.="`locked`, ";
			$query.="`published` ";
		$query.="FROM ";
		$query.="`article`";
		$query.=" WHERE ";
		$query.="`uid`='".$article_uid."'";
		
		$result = database::query($query);
		if(mysql_error()=="") {
			$clone_article_uid = mysql_insert_id();

			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_translations` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`title`, ";
				$query.="`content`, ";
				$query.="`locale`, ";
				$query.="`language_uid`, ";
				$query.="`locked`, ";
				$query.="`published` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`title`, ";
				$query.="`content`, ";
				$query.="`locale`, ";
				$query.="`language_uid`, ";
				$query.="`locked`, ";
				$query.="`published` ";
			$query.="FROM ";
			$query.="`article_translations`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$query.="AND ";
			$query.="`language_uid`='".$language_uid."'";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}

			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_page` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`template_uid`, ";
				$query.="`page_order`, ";
				$query.="`width`, ";
				$query.="`height` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`template_uid`, ";
				$query.="`page_order`, ";
				$query.="`width`, ";
				$query.="`height` ";
			$query.="FROM ";
			$query.="`article_page`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}

			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_page_translation` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`language_uid`, ";
				$query.="`width`, ";
				$query.="`height` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`language_uid`, ";
				$query.="`width`, ";
				$query.="`height` ";
			$query.="FROM ";
			$query.="`article_page_translation`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$query.="AND ";
			$query.="`language_uid`='".$language_uid."'";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}


			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_content` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`template_content_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`item_type_uid`, ";
				$query.="`content`, ";
				$query.="`rotation`, ";
				$query.="`width`, ";
				$query.="`height`, ";
				$query.="`fontfamily`, ";
				$query.="`fontsize`, ";
				$query.="`textalignment`, ";
				$query.="`textcolour`, ";
				$query.="`positionx`, ";
				$query.="`positiony`, ";
				$query.="`stackingposition`, ";
				$query.="`accept_content` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`template_content_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`item_type_uid`, ";
				$query.="`content`, ";
				$query.="`rotation`, ";
				$query.="`width`, ";
				$query.="`height`, ";
				$query.="`fontfamily`, ";
				$query.="`fontsize`, ";
				$query.="`textalignment`, ";
				$query.="`textcolour`, ";
				$query.="`positionx`, ";
				$query.="`positiony`, ";
				$query.="`stackingposition`, ";
				$query.="`accept_content` ";
			$query.="FROM ";
			$query.="`article_content`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}


			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_content_translations` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`template_content_translation_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`article_page_translation_uid`, ";
				$query.="`article_content_uid`, ";
				$query.="`article_translation_uid`, ";
				$query.="`item_type_uid`, ";
				$query.="`content`, ";
				$query.="`rotation`, ";
				$query.="`width`, ";
				$query.="`height`, ";
				$query.="`fontfamily`, ";
				$query.="`fontsize`, ";
				$query.="`textalignment`, ";
				$query.="`textcolour`, ";
				$query.="`positionx`, ";
				$query.="`positiony`, ";
				$query.="`stackingposition` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`template_content_translation_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`article_page_translation_uid`, ";
				$query.="`article_content_uid`, ";
				$query.="`article_translation_uid`, ";
				$query.="`item_type_uid`, ";
				$query.="`content`, ";
				$query.="`rotation`, ";
				$query.="`width`, ";
				$query.="`height`, ";
				$query.="`fontfamily`, ";
				$query.="`fontsize`, ";
				$query.="`textalignment`, ";
				$query.="`textcolour`, ";
				$query.="`positionx`, ";
				$query.="`positiony`, ";
				$query.="`stackingposition` ";
			$query.="FROM ";
			$query.="`article_content_translations`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$query.="AND ";
			$query.="`article_page_translation_uid`  ";
			$query.="IN ( ";
				$query.="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`article_page_translation`";
				$query.=" WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				$query.="AND ";
				$query.="`language_uid`='".$language_uid."'";
			$query.=" ) ";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}


			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_group` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`name`, ";
				$query.="`created_date` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`name`, ";
				$query.="`created_date` ";
			$query.="FROM ";
			$query.="`article_group`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}


			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_group_content` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`template_uid`, ";
				$query.="`article_uid`, ";
				$query.="`article_group_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`article_content_uid` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`template_uid`, ";
				$query.="`article_uid`, ";
				$query.="`article_group_uid`, ";
				$query.="`article_page_uid`, ";
				$query.="`article_content_uid` ";
			$query.="FROM ";
			$query.="`article_group_content`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}


			$query ="INSERT ";
			$query.="INTO ";
			$query.="`clone_article_group_content_translations` ";
			$query.="( ";
				$query.="`clone_article_uid`, ";
				$query.="`article_uid`, ";
				$query.="`article_translation_uid`, ";
				$query.="`article_translation_group_uid`, ";
				$query.="`article_translation_content_uid`, ";
				$query.="`name` ";
			$query.=") SELECT ";
				$query.="'".$clone_article_uid."', ";
				$query.="`article_uid`, ";
				$query.="`article_translation_uid`, ";
				$query.="`article_translation_group_uid`, ";
				$query.="`article_translation_content_uid`, ";
				$query.="`name` ";
			$query.="FROM ";
			$query.="`article_group_content_translations`";
			$query.=" WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$query.="AND ";
			$query.="`article_translation_uid` ";
			$query.="IN ( ";
				$query.="SELECT ";
				$query.="`uid` ";
				$query.="FROM ";
				$query.="`article_translations`";
				$query.=" WHERE ";
				$query.="`article_uid`='".$article_uid."' ";
				$query.="AND ";
				$query.="`language_uid`='".$language_uid."'";
			$query.=" ) ";

			$result = database::query($query);
			if(mysql_error()!='') {
				die($query.'<BR><BR><BR>'.mysql_error());
			}


		}
	}
}

?>