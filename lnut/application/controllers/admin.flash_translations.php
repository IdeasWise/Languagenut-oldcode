<?php

class admin_flash_translations extends Controller {

	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index() {

		if(count($_POST) > 0) {
			$objFlashTranslationTags = new flash_translations_locales();
			$objFlashTranslationTags->updateFlashTranslations();
			output::redirect(config::url('admin/flash_translations/'));
		}

		$skeleton				= make::tpl('skeleton.admin');
		$support_language_uid	= 14;
		$body					= make::tpl('body.admin.flash_translations.list');


		/**
		 * Fetch Tags
		 */
		$tags = flash_translations_tags::getAllByTagName();

		/**
		 * Fetch Locales
		 */
		$prefixes = language::getPrefixes();

		$arrTabs_li = array();
		$arrTabs_div= array();

		if(count($prefixes) > 0) {
			foreach($prefixes as $uidPrefix=>$arrPrefix) {
				$arrTabs_li[] = make::tpl('body.admin.tabs.li')->assign(
					array(
						'tab_id'	=>$uidPrefix,
						'lable'		=>$arrPrefix['prefix']
					)
				)->get_content();
				/**
				 * Fetch Translations by Locale
				 */
				$locales = flash_translations_locales::getTagsByLanguageUid($uidPrefix);

				$arrTags = array();
				foreach($tags as $tag_uid=>$arrTag) {

					$tagArray = flash_translations_tags::getById($tag_uid);
					$arrTag['tag_name'] = stripslashes($arrTag['tag_name']);
					$found = false;

					foreach($locales as $localeUid=>$localeArr) {
						if($localeArr['tag_uid']==$tag_uid) {
							$tag = make::tpl('body.flash_translations.form.row')->assign(
								array(
									'uidPrefix'		=>$uidPrefix,
									'tag_uid'		=>$tag_uid
								)
							);
							$tag->assign($localeArr);
							$found = true;
						}
					}
					if(!$found) {
						$tag = make::tpl('body.flash_translations.form.row')->assign(
							array(
								'uidPrefix'		=>$uidPrefix,
								'tag_uid'		=>$tag_uid
							)
						);
					}
					$tag->assign($tagArray);
					$arrTags[] = $tag->get_content();
				}
				//$arrTags[] = '</table>';
				$flashTable=make::tpl('body.flash_translations.table')->assign(
					array(
						'language_name'	=>$arrPrefix['name'],
						'table_content'	=>implode("",$arrTags)
					)
				);
				//$arrTabs_div[] = '<div id="tab-'.$uidPrefix.'">'.implode('',$arrTags).'</div>';
				$arrTabs_div[] = make::tpl('body.admin.tabs.div')->assign(
					array(
						'tab_id'		=>$uidPrefix,
						'tab_content'	=>$flashTable->get_content()
					)
				)->get_content();
			}
		}


		$body->assign(
			array(
				'tabs'			=> implode('',$arrTabs_div),
				'locales'		=>implode('',$arrTabs_li),
				'form.action'	=> config::url('account/flash_translations/')
			)
		);

		$skeleton->assign (
			array (
				'body' => $body
			)
		);

		output::as_html($skeleton,true);
	}
}
?>