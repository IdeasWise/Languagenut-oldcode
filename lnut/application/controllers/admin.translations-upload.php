<?php

class admin_translations_upload extends Controller {

	private $token		= 'form';

	private $arrTokens	= array (
		'verify',
		'success',
		'cancel',
		'form'
	);
	private $arrPaths		= array();

	public function __construct () {
		parent::__construct();
		$this->arrPaths = config::get('paths');
		if(isset($this->arrPaths[3]) && in_array($this->arrPaths[3], $this->arrTokens)) {
			$this->token =  $this->arrPaths[3];
		}
		if(in_array($this->token,$this->arrTokens)) {
			$method = 'do' . ucfirst($this->token);
			$this->$method();
		}
	}

	private function doForm() {
		$objImportCsv 	= new temp_csv_import();
		$response		= array();
		$language_uid	= 0;
		if(isset($_POST['submit'])) {
			if(isset($_POST['language_uid'])){
				$language_uid = $_POST['language_uid'];
			}
			$response = $objImportCsv->isValidTranslationSubmission();
			if($response === true) {
				$objImportCsv->Save();
				// redirect to verify file data;
				$objImportCsv->redirectTo('admin/translations/upload/verify/');
			}
		}

		$skeleton		= config::getUserSkeleton();
		$body			= make::tpl ('body.admin.upload-translation');

		$arrBody		= array();
		$objLanguage	= new language();
		$arrBody['language_uid']	= $objLanguage->GetUnsuedSectionVocabLanguagesListBox('language_uid', $language_uid);

		$body->assign($arrBody);
		if(is_array($response)) {
			$body->assign($response);
		}

		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	private function doVerify() {

		if(isset($_POST['import'])) {
			$objImportCsv 	= new temp_csv_import();
			$objImportCsv->ImportTranslations();
			// redirect to verify file data;
			$objImportCsv->redirectTo('admin/translations/upload/success/');
		}

		if(isset($_POST['cancel'])) {
			$objImportCsv 	= new temp_csv_import();
			$objImportCsv->CancelImportTranslation();
			// redirect to verify file data;
			$objImportCsv->redirectTo('admin/translations/upload/cancel/');
		}
		
		if(isset($_SESSION['csv_data']) && isset($_SESSION['session_time'])) {
			$skeleton		= config::getUserSkeleton();
			$body			= make::tpl ('body.admin.upload-translation.list');
			$arrImportTable	= array();

			foreach($_SESSION['csv_data'] as $data ) {
				$arrImportTable[] = make::tpl ('body.admin.upload-translation.list.rows')->assign($data)->get_content();
			}
			$body->assign('import_table', implode("",$arrImportTable));
			$skeleton->assign(
				array(
					'body' => $body
				)
			);
			output::as_html($skeleton, true);
		} else {
			output::redirect(config::url('admin/translations/upload/'));
		}
	}

	private function doCancel() {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl('body.admin.upload-translation.import-cancel');

		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}

	private function doSuccess() {
		$skeleton	= config::getUserSkeleton();
		$body		= make::tpl ('body.admin.upload-translation.import-success');

		$skeleton->assign(
			array(
				'body' => $body
			)
		);
		output::as_html($skeleton, true);
	}
}

?>