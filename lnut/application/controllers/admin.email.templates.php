<?php

	class admin_email_templates extends Controller {

		private $token		= 'list';
		private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'details'

		);
		private $arrPaths		= array();

		public function __construct () {
			parent::__construct();
			$this->arrPaths = config::get('paths');
			if(isset($this->arrPaths[2]) && in_array($this->arrPaths[2], $this->arrTokens)) {
				$this->token =  $this->arrPaths[2];
			}
			if(in_array($this->token,$this->arrTokens)) {
				$method = 'do' . ucfirst($this->token);
				$this->$method();
			}
		}
		/**
		*  doAdd() methods displays  add form on the screen
		*/
		protected function doAdd() {
			if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1){
				$skeleton	= make::tpl ('skeleton.admin');
			} else {
				$skeleton	= make::tpl('skeleton.account.translator');
			}
			$body		= make::tpl ('body.admin.email.template.add-edit');

			/**
			* If submit button is pressed then save form data to database
			*/
			if(isset($_POST['form_submit_button'])){
				$objEmailTemplate = new email_templates();
				if($objEmailTemplate->doSave() ){
					// redirect to list if all does well;
					$objEmailTemplate->redirectToDynamic('/email-templates/list/');
				} else {
					/**
					* If there is any error in saving form data to database or if empty data
					* from form then error message and form data
					* repopulated to the user.
					*/
					$body->assign( $objEmailTemplate->arrForm );
				}
			}
			$body->assign (
			array (
			'title' => 'Add'
			)
			);

			$skeleton->assign (
			array (
			'body' => $body
			)
			);
			output::as_html($skeleton,true);
		}

		/**
		*  doEdit() methods displays  edit form on the screen with populated data.
		*/
		protected function doEdit() {
			if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1){
				$skeleton	= make::tpl ('skeleton.admin');
			} else {
				$skeleton	= make::tpl('skeleton.account.translator');
			}
			$body			= make::tpl ('body.admin.email.template.add-edit');
			$arrBody		= array();
			$arrBody['title'] = 'Edit';

			/**
			* If submit button is pressed then save form data to database
			*/
			if(isset($_POST['form_submit_button'])){
				$objEmailTemplate = new email_templates();
				if($objEmailTemplate->doSave() ){
					// redirect to list if all does well;
					$objEmailTemplate->redirectToDynamic('/email-templates/list/');
				} else {
					/**
					* If there is any error in saving form data to database or if empty data from form then error message and form data
					* repopulated to the user.
					*/
					$body->assign( $objEmailTemplate->arrForm );
				}
			} else {
				if($this->arrPaths[3] > 0){
					$objEmailTemplate = new email_templates($this->arrPaths[3]);
					$objEmailTemplate->load();
					foreach( $objEmailTemplate->TableData as $idx => $val ){
						$arrBody[$idx] = $val['Value'];
					}
					$arrBody['uid'] =  $this->arrPaths[3];

				}
			}
			$body->assign( $arrBody );

			$skeleton->assign (
			array (
			'body' => $body
			)
			);
			output::as_html($skeleton,true);
		}

		/**
		* doDelete() method will delete an entry from langage table.
		*/
		protected function doDelete() {
			/*
			if($this->arrPaths[3] > 0){
				$objEmailTemplate = new email_templates($this->arrPaths[3]);
				$objEmailTemplate->load();
				$objEmailTemplate->delete();
				// redirect to list if all does well;
				$objEmailTemplate->redirectToDynamic('/email-templates/list/');
			}
			*/
			$objEmailTemplate = new email_templates();
			$objEmailTemplate->redirectToDynamic('/email-templates/list/');

		}

		/**
		* doList() method will list all records from database.
		*/
		protected function doList () {
			if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1){
				$skeleton	= make::tpl ('skeleton.admin');
			} else {
				$skeleton	= make::tpl('skeleton.account.translator');
			}
			$body		= make::tpl ('body.admin.email.templates.list');


			$arrRecords			= array();
			$objEmailTemplate	= new email_templates();
			$arrRecords			= $objEmailTemplate->getList();
			$arrRows			= array();
			if(!empty($arrRecords)) {
				foreach($arrRecords as $uid=>$data) {
					$arrRows[] = make::tpl('body.admin.email.templates.list.row')->assign($data)->get_content();
				}
			}

			$page_display_title		= $objEmailTemplate->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation		= $objEmailTemplate->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objEmailTemplate->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objEmailTemplate->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

			$body->assign('page.display.title'	, $page_display_title);
			$body->assign('page.navigation'		, $page_navigation);
			$body->assign('list.rows'			, implode('',$arrRows));

			$skeleton->assign (
			array (
			'body'=> $body
			)
			);
			output::as_html($skeleton,true);
		}


		private function doDetails() {
			if( isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3]) && $this->arrPaths[3] > 0 ) {
				$objEmailTemplate = new school_registration_templates($this->arrPaths[3]);
				$objEmailTemplate->load();
				if($objEmailTemplate->get_uid() == $this->arrPaths[3] ) {
					if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1){
						$skeleton	= make::tpl ('skeleton.admin');
					} else {
						$skeleton	= make::tpl('skeleton.account.translator');
					}
					$body		= make::tpl ('body.admin.email.templates');

					$body->assign(
					$this->get_tabs_and_contents(
					'email-content',
					'email_templates_translations',
					'tabb',
					'body.admin.email.template.form',
					$objEmailTemplate->get_uid()
					)
					);

					$skeleton->assign (
					array (
					'body' => $body
					)
					);
					output::as_html($skeleton,true);
				} else {
					output::redirect(config::admin_uri('email-templates/list/'));
				}
			} else {
				output::redirect(config::admin_uri('email-templates/list/'));
			}
		}

		private function get_tabs_and_contents( $index , $tableName, $tabName, $XHTML, $email_uid  ) {

			$arrResult = array();
			$arrLanguage = array();
			if(isset($_POST['form_submit_button'])){
				if(isset($_POST['table_name']) && $tableName == $_POST['table_name']){
					$className = trim($_POST['table_name']) ;
					if(!empty($className)){
						$objClass = new $className();
						if($objClass->doSave()){
							output::redirect($_SERVER['REQUEST_URI']); // redirect
						}
						else{
							$arrResult = $objClass->arrForm;
							// if error occured.
						}
					}
				}
			}

			if(isset($_POST['form_submit_language']) ){
				if(!empty($_POST['locale'])){
					$query  = "INSERT ";
					$query .= "INTO ";
					$query .= mysql_real_escape_string($_POST['table_name'])." ";
					$query .= "SET ";
					$query .= "locale = '".mysql_real_escape_string($_POST['locale'])."' ";
					$query .= ", email_uid = '".mysql_real_escape_string($email_uid)."' ";
					database::query($query);
					output::redirect($_SERVER['REQUEST_URI']); // redirect
				} else {
					$arrLanguage['message_error'] = '<p>Please correct the errors below:</p><ul><li>Please choose one laguage to create page.</li></ul>';
				}
			}

			$body		= make::tpl ('body.admin.tabs.inner');

			$WHERE =" AND ";
			$WHERE.="`email_uid` = '".mysql_real_escape_string($email_uid)."' ";

			$query ="SELECT ";
			$query.="`TB`.*, ";
			$query.="`LG`.`name` AS `LangName` ";
			$query.="FROM ";
			$query.="`".mysql_real_escape_string($tableName)."` AS TB, ";
			$query.="`language` AS LG ";
			$query.="WHERE ";
			$query.="`LG`.`prefix` = `TB`.`locale` ";
			$query.=$WHERE." ";
			$query.="GROUP BY `TB`.`uid` ";
			$query.="ORDER BY `LG`.`prefix`";
			$result			= database::query($query);
			$arrTabs		= array();
			$arrTabs_li		= array();
			$arrTabs_div	= array();

			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row=mysql_fetch_assoc($result)) {
					$panel = make::tpl($XHTML);
					if(isset($arrResult['uid']) && $arrResult['uid'] == $row['uid']){
						foreach( $arrResult as $idx => $val ) {
							$row[$idx] = $val;
						}
					}

					foreach( $row as $idx => $val ) {
						$row[$idx.'.id'] = $row['locale'].$idx.'.id'.$tabName;
					}

					$row['cms.id']		= $row['locale'].'.cms'.$tabName;
					$row['table_name']	= $tableName;
					$row['tabName']		= $tabName;
					$row['action']		= $_SERVER['REQUEST_URI'];
					$row['body'] = str_replace(array('{{','}}'),array('&#123;&#123;','&#125;&#125;'),$row['body']);

					$panel->assign($row);

					$localeLi = new xhtml('admin.locale.li');
					$localeLi->load();

					$localeLi->assign("tab_id", "subTab-");
					$localeLi->assign("uid", $tabName . '-' . $row['uid']);
					$localeLi->assign("prefix", $row['locale']);
					$arrTabs_li[] = $localeLi->get_content();

					//				$arrTabs_li[]	= '<li><a href="#subTab-'.$tabName.'-'.$row['uid'].'"><span>'.$row['locale'].'</span></a></li>';
					$div_wrapper_xhtml = new xhtml('div_wrapper');
					$div_wrapper_xhtml->load();

					$div_wrapper_xhtml->assign("id", 'subTab-' . $tabName . '-' . $row['uid']);
					$div_wrapper_xhtml->assign("content", $panel->get_content());

					$arrTabs_div[] = $div_wrapper_xhtml->get_content();

				}
			}

			$query ="SELECT ";
			$query.="`prefix`, ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`language` ";
			$query.="WHERE ";
			$query.="`prefix` NOT IN ( ";
			$query.="SELECT ";
			$query.="`locale` ";
			$query.="FROM ";
			$query.=" ".$tableName." ";
			$query.="WHERE ";
			$query.="1=1 ".$WHERE."";
			$query.=") ";
			$query.="GROUP BY prefix ";
			$query.="ORDER BY name";
			$result = database::query($query);
			$data = array();
			$data[''] = 'Language';
			if($result && mysql_error()=='' && mysql_num_rows($result) > 0) {
				while($row=mysql_fetch_assoc($result)) {
					$data[$row['prefix']] = $row['name'];
				}
			}
			if(count($data) > 1){
				$row = array();
				$panel = make::tpl ('body.admin.page.tab.add.language');

				$row['table_name'] = $tableName;
				$row['action'] = $_SERVER['REQUEST_URI'];
				$row['locale'] = format::to_select(array("name" => 'locale',"id" => 'locale',"options_only" => false), $data , NULL);
				$panel->assign($row);
				$panel->assign($arrLanguage);

				$localeLi = new xhtml('admin.locale.li');
				$localeLi->load();

				$localeLi->assign("tab_id", "subTab-");
				$localeLi->assign("uid", $tabName. '-language');
				$localeLi->assign("prefix", 'Add New');
				$arrTabs_li[] = $localeLi->get_content();

				//			$arrTabs_li[] = '<li><a href="#subTab-' . $tabName . '-language"><span>Add New</span></a></li>';

				$div_wrapper_xhtml = new xhtml('div_wrapper');
				$div_wrapper_xhtml->load();

				$div_wrapper_xhtml->assign("id", 'subTab-' . $tabName . '-language');
				$div_wrapper_xhtml->assign("content", $panel->get_content());

				$arrTabs_div[] = $div_wrapper_xhtml->get_content();
			}

			$body->assign(
			array(
			'tabs.lis' => implode('',$arrTabs_li),
			'tabs.divs' => implode('',$arrTabs_div)
			)
			);
			return array( $index => $body->get_content());
		}
	}

?>