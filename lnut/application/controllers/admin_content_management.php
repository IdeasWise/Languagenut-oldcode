<?php

	class admin_content_management extends Controller {

		private $token		= 'list';
		private $arrTokens	= array (
		'list',
		'edit',
		'add',
		'delete',
		'menu'
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
			$skeleton	= make::tpl ('skeleton.admin');
			$body		= make::tpl ('body.cms.content.add.edit');
			$objLanguage = new language();

			/**
			* If submit button is pressed then save form data to database
			*/
			if(isset($_POST['form_submit_button'])){
				$objLandingCms = new landing_cms();
				if($uid=$objLandingCms->doSave() ){
					// redirect to list if all does well;
				//output::redirect(config::admin_uri('content/list/'));
					output::redirect(config::admin_uri('content/menu/'.$uid.'/'));
				} else {
					/**
					* If there is any error in saving form data to database or if empty data
					* from form then error message and form data
					* repopulated to the user.
					*/
					$objLandingCms->arrForm['locale'] = $objLanguage->LocaleSelectBox(
						'locale',$objLandingCms->arrForm['locale']
					);
					$body->assign($objLandingCms->arrForm);
				}
			} else {
				$body->assign ('locale',$objLanguage->LocaleSelectBox(
					'locale',
					''
					)
				);
			}
			$body->assign (
				array (
					'title'		=> 'Add',
					'action'	=> 'add'
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
			if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])){
				$objLandingCms	= new landing_cms($this->arrPaths[3]);
				if($objLandingCms->get_valid()) {
					$objLanguage = new language();
					$objLandingCms->load();
					$skeleton		= make::tpl ('skeleton.admin');
					$body			= make::tpl ('body.cms.content.add.edit');
					$arrBody		= array();
					$arrBody['title'] = 'Edit';
					$arrBody['action'] = 'edit/'.$objLandingCms->get_uid();

					/**
					* If submit button is pressed then save form data to database
					*/
					if(isset($_POST['form_submit_button'])){
						$objLandingCms = new landing_cms();
						if($objLandingCms->doSave() ){
							// redirect to list if all does well;
							if(!isset($_SESSION['cms_success_message'])) {
								$_SESSION['cms_success_message'] = component_message::success('Record has been updated successfully.');
							}
							output::redirect(config::admin_uri('content/edit/'.$this->arrPaths[3].'/'));
							//$objLandingCms->redirectTo('admin/registration-email/list/');
						} else {
							/**
							* If there is any error in saving form data to database or if empty data from form then error message and form data
							* repopulated to the user.
							*/
							$objLandingCms->arrForm['locale'] = $objLanguage->LocaleSelectBox(
								'locale',$arrBody['locale']
							);
							$body->assign( $objLandingCms->arrForm );
						}
					} else {
						foreach( $objLandingCms->TableData as $idx => $val ){
							$arrBody[$idx] = $val['Value'];
						}
						$arrBody['locale'] = $objLanguage->LocaleSelectBox(
							'locale',$arrBody['locale']
						);
						$arrBody['uid'] = $this->arrPaths[3];
					}
					$arrBody['success_message'] = (isset($_SESSION['cms_success_message']))?$_SESSION['cms_success_message']:'';
					if(isset($_SESSION['cms_success_message'])) {
						unset($_SESSION['cms_success_message']);
					}
					$body->assign( $arrBody );

					$skeleton->assign (
						array (
						'body' => $body
						)
					);
					output::as_html($skeleton,true);
				} else {
					output::redirect(config::admin_uri('content/list/'));
				}
			} else {
				output::redirect(config::admin_uri('content/list/'));
			}
		}

		/**
		*  doMenu() methods displays menu form
		*/
		protected function doMenu() {
			if(isset($this->arrPaths[3]) && is_numeric($this->arrPaths[3])){
				$objLandingCms	= new landing_cms($this->arrPaths[3]);
				if($objLandingCms->get_valid()) {
					$objLandingCms->load();
					$skeleton		= make::tpl ('skeleton.admin');
					$body			= make::tpl ('body.cms.content.manage.menu');
					$arrBody		= array();
					$arrBody['action'] = 'menu/'.$objLandingCms->get_uid();

					/**
					* If submit button is pressed then save form data to database
					*/
					if(isset($_POST['form_submit_button'])){
						$objLandingCmsMenuHeader = new landing_cms_menu_header();
						if($objLandingCmsMenuHeader->save_menu_header($objLandingCms->get_uid()) ){
							$objLandingCms	= new landing_cms();
							$objLandingCms->generate_menu_content($this->arrPaths[3]);
							// redirect to list if all does well;
							if(!isset($_SESSION['cms_success_message'])) {
								$_SESSION['cms_success_message'] = component_message::success('Menu settings has been updated successfully.');
							}
							output::redirect(config::admin_uri('content/menu/'.$this->arrPaths[3].'/'));
							//$objLandingCms->redirectTo('admin/registration-email/list/');
						}
					}
					$arrBody['page_title'] = $objLandingCms->TableData['page_title']['Value'];
					$arrBody['uid'] = $objLandingCms->get_uid();
					$arrBody['success_message'] = (isset($_SESSION['cms_success_message']))?$_SESSION['cms_success_message']:'';
					if(isset($_SESSION['cms_success_message'])) {
						unset($_SESSION['cms_success_message']);
					}
					$arrMenuHeaders = landing_cms_menu_header::get_menu_header_by_cms_uid($objLandingCms->get_uid());
					if(is_array($arrMenuHeaders) && count($arrMenuHeaders)) {
						$arrMenuHeadersHtml = array();
						foreach($arrMenuHeaders as $index => $arrHeader) {
							$arrMenuItems = landing_cms_menu_item::get_menu_item_by_header_uid_and_cms_uid(
								$arrHeader['uid'],
								$objLandingCms->get_uid()
							);
							if(is_array($arrMenuItems) && count($arrMenuItems)) {
								$arrMenuHtml = array();
								foreach($arrMenuItems as $menu_index => $arrMenuItem) {
									$menu_item_tpl = make::tpl('body.cms.content.manage.menu.item');
									$menu_item_tpl->assign(
										array(
											'index'		=>$index,
											'menu_name'	=>$arrMenuItem['menu_name'],
											'menu_url'	=>$arrMenuItem['menu_url']
										)
									);
									$arrMenuHtml[] = $menu_item_tpl->get_content();
								}
								$MenuItemHtml = implode("",$arrMenuHtml);
							} else {
								$MenuItemHtml = make::tpl('body.cms.content.manage.menu.item')->assign(array('index'=>$index))->get_content();
							}
							$meanu_header_tpl = make::tpl('body.cms.content.manage.menu.existing');
							$meanu_header_tpl->assign(
								array(
									'index'			=>$index,
									'header_text'	=>$arrHeader['header_text'],
									'menu_items'				=>$MenuItemHtml
								)
							);
							$arrMenuHeadersHtml[] = $meanu_header_tpl->get_content();
						}
						$arrBody['menu_content'] = implode("",$arrMenuHeadersHtml);
					} else {
						// if no headers exist then show default form area
						$arrBody['menu_content'] = make::tpl('body.cms.content.manage.menu.new');
					}

					$body->assign( $arrBody );
					$skeleton->assign (
						array (
							'body' => $body
						)
					);
					output::as_html($skeleton,true);
				} else {
					output::redirect(config::admin_uri('content/list/'));
				}
			} else {
				output::redirect(config::admin_uri('content/list/'));
			}
		}

		/**
		* doDelete() method will delete an entry from langage table.
		*/
		protected function doDelete() {
			/*
			if($this->arrPaths[3] > 0){
				$objEmailTemplate = new school_registration_templates($this->arrPaths[3]);
				$objEmailTemplate->load();
				if($objEmailTemplate->get_slug() != 'school.registration.welcome.template') {
					$objEmailTemplate->delete();
				}
				// redirect to list if all does well;
				$objEmailTemplate->redirectTo('admin/registration-email/list/');
			} */
			output::redirect(config::admin_uri('content/list/'));
		}

		/**
		* doList() method will list all records from database.
		*/
		protected function doList () {
			$skeleton	= make::tpl ('skeleton.admin');
			$body		= make::tpl ('body.cms.content.list');

			$arrRecords			= array();
			$objLandingCms	= new landing_cms();
			$arrRecords			= $objLandingCms->getList();
			$arrRows			= array();
			if(!empty($arrRecords)) {
				foreach($arrRecords as $uid=>$data) {
					$arrRows[] = make::tpl('body.cms.content.list.row')->assign($data)->get_content();
				}
			}

			$page_display_title		= $objLandingCms->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
			$page_navigation		= $objLandingCms->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$objLandingCms->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$objLandingCms->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

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

		public function getMenuHeaders() {
		}
	}

?>