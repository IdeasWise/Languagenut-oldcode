<?php

/**
 * index.php
 */
class Index extends Controller {

	public function __construct() {
		parent::__construct();

		$paths = config::get('paths');

		if(isset($paths[0]) && $paths[0] == 'cancel-subscription') {
			$this->load_controller('cancel_subscription');
			exit;
		}

		if(isset($paths[0]) && $paths[0] == 'send-application') {
			$this->load_controller('send_application');
			exit;
		}
		$this->page();
	}

	protected function page() {
		/**
		 * Fetch the standard public xhtml page template
		 */
		$skeleton = new xhtml('skeleton.public');
		$skeleton->load();

		/**
		 * Fetch the body content
		 */
		$locale = config::get('locale');
		$body = new xhtml('body.index.'.$locale);
		$body->load();

		/**
		 * Set Country Flags
		 */
		if($locale=='en' || $locale=='us'){
			$displayLanguage=array(
				'jp'=>'ja',
				'dk'=>'dk',
				'de'=>'ge',
				'us'=>'us',
				'fr'=>'fr'
			);
			$flags="";
			$languages=language::getPrefixes();
			foreach ($languages as $key => $language) {
				$lang=array_search($language["prefix"], $displayLanguage);
				if($lang!==false){
					$flags.='&nbsp;<a href="'.config::base($language["prefix"]."/").'"><img src="'.config::base("images/flag/".$lang.".png") .'" border="0" /></a>';
				}
			}
			$body->assign('country_flags', $flags);
		}
		/**
		 * Fetch Translations
		 */
		$comTranslations = new component_translations;
		$arrPanels = $comTranslations->homePageTabsByLocale();
		$body->assign($arrPanels);

		$arrTerms = config::translate(array(
			'welcome',
			'games',
			'songs',
			'culture',
			'teachers_say',
			'children_say',
			'contact',
			'play_and_learn',
			'terms',
			'privacy_policy',
			'free_2_week_trial',
			'all_rights_reserved',
			'home_user_link_caption',
			'school_user_link_caption'
		));

		/*
		 *  on following condition we should put a check if array ($arrTerms) counts matche with all index to check translation is available or not if not we should redirect it to /en/ locale
		 */
		if (is_array($arrTerms)) {
			foreach ($arrTerms as $key => $val) {
				$body->assign('translate:' . $key, $val);
				$body->assign($key, $val);
			}
		}

		/**
		 * Fetch the page details
		 */
		$page = new page('index');

		/**
		 * Build the output
		 */
		$skeleton->assign(
			array(
				'title' => $page->title(),
				'keywords' => $page->keywords(),
				'description' => $page->description(),
				'body' => $body
			)
		);

		output::as_html($skeleton, true);
	}

}

?>