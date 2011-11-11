<?php

/**
* pages.php
*/

class Pages extends Controller {

	private $selected	= 'tabs';

	private $pages		= array (
		'tabs',
		'school',
		'homeuser',
		'send-application'
	);

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		if(isset($arrPaths[2]) && in_array($arrPaths[2], $this->pages)) {
			$this->selected	 = $arrPaths[2];
		}
		switch($this->selected) {
			case "tabs":
				$this->doTabs();
			break;
			case "school":
				$this->doSchoolStage();
			break;
			case "homeuser":
				$this->doHomeuserStage();
			break;
			case "send-application":
				$this->doSendApplication();
			break;	
			case "list" :
			default:
				$this->doTabs();
			break;
		}

	}

	protected function doSendApplication() {

		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.send-application');
		$objTabs	= new tabs();

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'send-application-content',
				'send_application_translation',
				'send-application',
				'body.admin.send-application-form'
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doTabs() {
		$skeleton		= make::tpl('skeleton.admin');
		$body			= make::tpl('body.admin.page.tabs');
		$objTabs		= new tabs();

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.welcome.page',
				'page_index_tab_welcome_translations',
				'welcome',
				'body.admin.page.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.game.page',
				'page_index_tab_games_translations',
				'game',
				'body.admin.page.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.songs.page',
				'page_index_tab_songs_translations',
				'songs',
				'body.admin.page.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.newstories.page',
				'page_index_tab_culture_translations',
				'newstories',
				'body.admin.page.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.teachers.page',
				'page_index_tab_teachers_translations',
				'teachers',
				'body.admin.page.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.children.page',
				'page_index_tab_children_translations',
				'children',
				'body.admin.page.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.contact.page',
				'page_index_tab_contact_translations',
				'contact',
				'body.admin.page.tabs.details.form'
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doSchoolStage() {
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.page.school.stage.tabs');
		$objTabs	= new tabs();
		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage1',
				'page_subscribe_school_stage_1_translations',
				'stage1',
				'body.admin.page.school.stage.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage2',
				'page_subscribe_school_stage_2_translations',
				'stage2',
				'body.admin.page.school.stageall.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage3',
				'page_subscribe_school_stage_3_translations',
				'stage3',
				'body.admin.page.school.stageall.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage4',
				'page_subscribe_school_stage_4_translations',
				'stage4',
				'body.admin.page.school.stageall.tabs.details.form'
			)
		);

		$body->assign(array('section_name'=>'School Registration'));

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

	protected function doHomeuserStage() {
		$skeleton	= make::tpl ('skeleton.admin');
		$body		= make::tpl ('body.admin.page.school.stage.tabs');

		$objTabs = new tabs();
		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage1',
				'page_subscribe_homeuser_stage_1_translations',
				'stage1',
				'body.admin.page.school.stage.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage2',
				'page_subscribe_homeuser_stage_2_translations',
				'stage2',
				'body.admin.page.homeuser.stage2and3.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage3',
				'page_subscribe_homeuser_stage_3_translations',
				'stage3',
				'body.admin.page.homeuser.stage2and3.tabs.details.form'
			)
		);

		$body->assign(
			$objTabs->get_tabs_and_contents(
				'tab.stage4',
				'page_subscribe_homeuser_stage_4_translations',
				'stage4',
				'body.admin.page.homeuser.stage4.tabs.details.form'
			)
		);

		$body->assign(array('section_name'=>'Home User Registration'));

		$skeleton->assign (
			array (
				'body' => $body
			)
		);
		output::as_html($skeleton,true);
	}

}

?>