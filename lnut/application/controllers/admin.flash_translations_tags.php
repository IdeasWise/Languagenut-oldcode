<?php

class admin_flash_translations_tags extends Controller {

	private $parts = array();
	
	public function __construct () {
		parent::__construct();
		$this->index();
	}

	protected function index() {
		if(count($_POST) > 0) {
			flash_translations_tags::updateFlashTranslationsTags();
			output::redirect(config::url('admin/flash_translations_tags/'));
		}

		$skeleton	= make::tpl('skeleton.admin');
		$body		= make::tpl('body.admin.flash_translations_tags.list');
		/**
		 * Fetch Tags
		 */
		$arrTags = flash_translations_tags::getAllByTagName();

		$arrRows = array();
		foreach($arrTags as $tag_uid=>$arrTag) {
			$arrRows[] = make::tpl('body.admin.flash_translations_tags.row')->assign(
				array(
					'tag_uid'		=>$tag_uid,
					'tag_name'		=>$arrTag['tag_name'],
					'description'	=>$arrTag['description']
				)
			)->get_content();
		}
		$tagHtml = make::tpl('body.admin.flash_translations_tags.table')->assign(
			array(
				'table_content'=>implode("",$arrRows)
			)
		);

		$body->assign(
			array(
				'tagHtml'		=> $tagHtml,
				'form.action'	=> config::url('admin/flash_translations_tags/')
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