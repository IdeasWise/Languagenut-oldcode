<?php

class controller_translations extends Controller {

	private $token		= 'list';
	private $arrTokens	= array (
		'list',
		'year',
		'unit',
		'section',
		'vocabulary',
		'upload'
	);
	private $arrPaths	= array();

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

	private function doUpload() {
		$this->load_controller('admin.translations-upload');
	}

	protected function getYearLink( $uid ) {
		$link = '';
		$objYear = new years($uid);
		if($objYear->get_valid() == true){
			$objYear->load();
			$link = ' &rsaquo;&rsaquo; <a href="'.config::admin_uri('translations/year/'.$objYear->get_uid()).'">'.$objYear->get_name().'</a>';
		}
		return $link;
	}

	protected function getUnitLink( $uid ) {
		$link = '';
		$objUnit = new units($uid);
		if($objUnit->get_valid() == true){
			$objUnit->load();
			$link .= $this->getYearLink($objUnit->get_year_uid());
			$link.= ' &rsaquo;&rsaquo; <a href="'.config::admin_uri('translations/unit/'.$objUnit->get_uid()).'">'.$objUnit->get_name().'</a>';
		}
		return $link;
	}

	protected function getSectionLink( $uid ) {
		$link = '';
		$objSection = new sections($uid);
		if($objSection->get_valid() == true){
			$objSection->load();
			$link .= $this->getUnitLink($objSection->get_unit_uid());
			$link.= ' &rsaquo;&rsaquo; <a href="'.config::admin_uri('translations/section/'.$objSection->get_uid()).'">'.$objSection->get_name().'</a>';
		}
		return $link;
	}

	protected function getVocabularyLink( $uid ) {
		$link = '';
		$objSectionsVocabulary = new sections_vocabulary($uid);
		if($objSectionsVocabulary->get_valid() == true){
			$objSectionsVocabulary->load();
			$link .= $this->getSectionLink($objSection->get_section_uid());
			$link .= ' &rsaquo;&rsaquo; Vocabulary :'.$objSectionsVocabulary->get_name();
		}
		return $link;
	}

	protected function getBreadcrumb() {
		$link = '<a href="'.config::admin_uri('translations/').'">Translations</a>';
		if($this->token != '' && $this->token != 'list'){
			$method = 'get' . ucfirst($this->token).'Link';
			$link .= $this->$method(@$this->arrPaths[3]);
		}
		return $link;
	}
	protected function doSave($TableName, $section,  $Where = '', $uid = 0, $TAB1 ='', $TAB2 = '', $TranslationTable='', $parentId = '', $PreTable = '', $Presection = '' ) {
		if($_POST['name'] != '' ){
			$query ="UPDATE ";
			$query.="`".mysql_real_escape_string($PreTable)."` ";
			$query.="SET ";
			$query.="name = '".addslashes(mysql_real_escape_string($_POST['name']))."' ";
			$query.="WHERE ";
			$query.="`uid` = '".mysql_real_escape_string($uid)."' ";
			$query.="LIMIT 1 ";
			database::query($query);
		}

		foreach($_POST['lang'] as $idx => $val){
			$val = addslashes($val);
			$WHERE =" WHERE ";
			$WHERE.="`".mysql_real_escape_string($parentId)."` = '".mysql_real_escape_string($uid)."' ";
			$WHERE.="AND ";
			$WHERE.="`language_id` = '".mysql_real_escape_string($idx)."'";

			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`".mysql_real_escape_string($TranslationTable)."` ";
			$query.=$WHERE;

			$result = database::query($query);
			if(mysql_num_rows($result)){
				$query = " UPDATE ";
				$query.="`".mysql_real_escape_string($TranslationTable)."` ";
				$query.="SET ";
				$query.="`name` = '".mysql_real_escape_string($val)."' ";
				$query.=$WHERE;
				database::query($query);
			} else if( !empty ($val) ) {
				$query ="INSERT INTO ";
				$query.="`".mysql_real_escape_string($TranslationTable)."` (";
				$query.="`name`, ";
				$query.="`".mysql_real_escape_string($parentId)."`, ";
				$query.="`language_id`, ";
				$query.="`active`";
				$query.=") VALUES ( ";
				$query.="'".mysql_real_escape_string($val)."', ";
				$query.="'".mysql_real_escape_string($uid)."', ";
				$query.="'".mysql_real_escape_string($idx)."', ";
				$query.="'1' ";
				$query.=")";
				database::query($query);
			}
			
		}
		return "Your changes have been saved successfully...";
	}

	protected function getLanguageForm( $TableName, $uid, $parentId, $reference = array() ) {

		$query ="SELECT *, ( ";
			$query.="SELECT `name` ";
			$query.="FROM ";
			$query.="`".mysql_real_escape_string($TableName)."` ";
			$query.="WHERE ";
			$query.="`language_id` = `language`.uid ";
			$query.="AND ";
			$query.="`".mysql_real_escape_string($parentId)."` = '".mysql_real_escape_string($uid)."'";
			$query.="LIMIT 1 ";
		$query.=") AS `Lvalue` ";
		$query.="FROM ";
		$query.="`language` ";
		if(isset($_SESSION['user']['localeRights'])) {
			$query.="WHERE ";
			$query.="`prefix` IN (".$_SESSION['user']['localeRights'].")";
		}
		$query.="ORDER BY `name`";
		$result = database::query($query);

		$arrForms = array();
		if(count($reference)){
			$arrForms[] = make::tpl ('body.admin.translation.element')->assign($reference)->get_content();
		}

		$arrForms[] = '<tr><th>Language</th><th>Translation</th></tr>';
		while($row = mysql_fetch_array( $result ) ) {
			$panel = make::tpl ('body.admin.translation.element');
			
			$data['lable'] = $row['name'];
			$data['input_name'] = 'lang['.$row['uid'].']';
			if($row['Lvalue'] != NULL) {
				$data['input_value'] = stripslashes(str_replace('\\','',$row['Lvalue']));
			} else {
				$data['input_value'] = '';
			}
			$panel->assign($data);
			$arrForms[] = $panel->get_content();
		}
		return implode('',$arrForms);
	}

	protected function doGenerate($TableName, $section,  $Where = '', $uid = 0, $TAB1 ='', $TAB2 = '', $TranslationTable='', $parentId = '', $PreTable = '', $Presection = '' ) {
		
		if(isset($_SESSION['user']['admin']) && $_SESSION['user']['admin'] == 1) {
			$skeleton	= make::tpl ('skeleton.admin');
		} else {
			$skeleton	= make::tpl ('skeleton.account.translator');
			$skeleton->assign (
				array (
					'logged_user_email'=> (isset($_SESSION['user']['email']) && $_SESSION['user']['email'] != "")?$_SESSION['user']['email']:''
				)
			);
		}

		$array		= array();
		$arrRows	= array();
		$message	= '';

		if(isset($_POST['save_changes'])) {
			if(isset($_POST['lang'])) {
				$message = $this->doSave($TableName, $section, $Where, $uid, $TAB1, $TAB2, $TranslationTable, $parentId, $PreTable);
			}
		}

		if($TAB1 != '') {
			$array['tab1'] = $TAB1;
		} else {
			$array['tab1_style'] = 'style="display:none;"';
		}

		if($TAB2 != '') {
			$array['tab2'] = $TAB2;
		} else {
			$array['tab2_style'] = 'style="display:none;"';
		}

		$body = make::tpl ('body.admin.year.test');

		if($TableName != '' && $section != 'vocabulary') {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`name` ";
			$query.="FROM ";
			$query.="`".mysql_real_escape_string($TableName)."` ";
			$query.=$Where;

			$yearResult = database::query($query);
			while($Y = mysql_fetch_array($yearResult) ){
				$margine	= 'style="margin:2px; font-size:11px;"';
				$arrRows[]	= '<div '.$margine.'><a style="text-decoration:none;" href="'.config::admin_uri('translations/'.$section.'/'.$Y['uid']).'">'.$Y['name'].'<a></div>';
			}
		}
		if($section == 'vocabulary'){
			$arrRows[] = $this->getVocabularyForm();
		}

		$body->assign( $array );
		if($uid > 0) {
			if( $TableName != '' )
			$body->assign('tab2.contents',implode('',$arrRows));

			$formBody = make::tpl ('body.admin.translation.form');
			$reference = array();

			if($PreTable != ''){
				$query ="SELECT ";
				$query.="`uid`, ";
				$query.="`name` ";
				$query.="FROM ";
				$query.="`".mysql_real_escape_string($PreTable)."` ";
				$query.="WHERE ";
				$query.="`uid` = '".mysql_real_escape_string($uid)."' ";
				$query.="LIMIT 1";

				$result = database::query($query);
				if(mysql_num_rows($result)){
					$row = mysql_fetch_array($result);
					$reference['RefTableName'] = $PreTable;
					$reference['lable'] = ucfirst($this->arrPaths[2])." Name";
					$reference['input_name'] = 'name';
					$reference['input_value'] = stripslashes(str_replace('\\','',$row['name']));
				}

			}


			$formBody->assign(
				'form.elements',$this->getLanguageForm(
					$TranslationTable,
					$uid,
					$parentId,
					$reference
				)
			);

			$formBody->assign( 'form.action',$_SERVER['REQUEST_URI'] );
			$formBody->assign( 'message'	, $message );

			$body->assign('tab1.contents', $formBody->get_content() );
		} else {
			$body->assign('tab1.contents'	, implode('',$arrRows));
		}
		$body->assign('breadcrumb'			, $this->getBreadcrumb());
		$body->assign('page_title'			, "Vocabulary Translations");


		$skeleton->assign (
			array (
				'body'=> $body
			)
		);
		output::as_html($skeleton,true);
	}


	protected function doSaveVocabulary() {
		foreach($_POST['vocabs'] as $idx => $val){
			$val = mysql_real_escape_string(addslashes($val));
			$WHERE =" WHERE ";
			$WHERE.="`term_uid` = '".$idx."' ";
			$WHERE.="AND ";
			$WHERE.="`language_id` = '".mysql_real_escape_string($_POST['vocab_language_id'])."'";

			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`sections_vocabulary_translations` ";
			$query.=$WHERE;
			$query.=" LIMIT 1";

			$result = database::query($query);
			if(mysql_num_rows($result)){
				$query ="UPDATE ";
				$query.="`sections_vocabulary_translations` ";
				$query.="SET ";
				$query.="`name` = '".$val."' ";
				$query.=$WHERE;
				$query.=" LIMIT 1";
				database::query($query);
			}
			elseif( !empty ($val) ) {
				$query ="INSERT INTO ";
				$query.="`sections_vocabulary_translations` (";
				$query.="`name`,";
				$query.="`term_uid`,";
				$query.="`language_id`,";
				$query.="`active`";
				$query.=") VALUES ( ";
				$query.="'".$val."',";
				$query.="'".mysql_real_escape_string($idx)."',";
				$query.="'".mysql_real_escape_string($_POST['vocab_language_id'])."',";
				$query.="'1'";
				$query.=")";
				database::query($query);
			}
		}
		return "Your changes have been saved successfully...";
	}

	protected function getVocabularyForm() {
		$message = '';
		if(isset($_POST['save_changes']) && isset($_POST['vocabs'])){
			$message = $this->doSaveVocabulary();
		}

		if(isset($_POST['vocab_language_id'])) {
			$lang_id=$_POST['vocab_language_id'];
		} else {
			$query ="SELECT ";
			$query.="`uid` ";
			$query.="FROM ";
			$query.="`language` ";
			if(isset($_SESSION['user']['localeRights'])) {
				$query.="WHERE ";
				$query.="`prefix` IN (".$_SESSION['user']['localeRights'].")";
			}
			$query.="ORDER BY ";
			$query.="`name` ";
			$query.="LIMIT 0,1";
			$result = database::query($query);
			if(mysql_num_rows($result)){
				$row = mysql_fetch_array($result);
				$lang_id = $row['uid'];
			}
		}
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="( ";
		$query.="SELECT ";
		$query.="CONCAT(`uid`, '!@!', `name`) ";
		$query.="FROM ";
		$query.="`sections_vocabulary_translations` ";
		$query.="WHERE ";
		$query.="`language_id` = '".mysql_real_escape_string($lang_id)."' ";
		$query.="AND ";
		$query.="`term_uid` = `sections_vocabulary`.`uid` ";
		$query.="LIMIT 1";
		$query.=") AS `Lvalue` ";
		$query.="FROM ";
		$query.="`sections_vocabulary` ";
		$query.="WHERE ";
		$query.="section_uid = '".mysql_real_escape_string($this->arrPaths[3])."' ";
		$result = database::query($query);
		$arrForms = array();

		$formBody = make::tpl ('body.admin.translation.form');
		$formBody->assign('message',$message);
		$objLanguage = new language();


		$arrForms[] = '<tr><th>Language</th><th>'.$objLanguage->LanguageSelectBox('vocab_language_id',$lang_id).'</th></tr>';
		while($row = mysql_fetch_array( $result ) ) {
			$panel = make::tpl ('body.admin.translation.element');
			$idx = '';
			$val = '';
			if($row['Lvalue'] != NULL) {
				list($idx, $val ) = explode( '!@!', @$row['Lvalue']);
			}
			$data['lable'] = $row['name'];
			$data['input_name'] = 'vocabs['.$row['uid'].']';
			if($val != NULL) {
				$data['input_value'] = stripslashes(str_replace('\\','',$val));
			} else {
				$data['input_value'] = '';
			}
			$panel->assign($data);
			$arrForms[] = $panel->get_content();
		}

		$formBody->assign( 'form.elements'	, implode('',$arrForms) );
		$formBody->assign( 'form.action'	, $_SERVER['REQUEST_URI'].'#tab-2');
		return $formBody->get_content();
	}

	protected function doYear() {
		$this->doGenerate(
			'units',
			'unit',
			"WHERE year_uid = '".mysql_real_escape_string($this->arrPaths[3])."'",
			$this->arrPaths[3],
			'Edit Year Translations',
			'Unit List',
			'years_translations',
			'year_id',
			'years'
		);
	}

	protected function doUnit() {
		$this->doGenerate(
			'sections',
			'section',
			"WHERE unit_uid = '".mysql_real_escape_string($this->arrPaths[3])."'",
			$this->arrPaths[3],
			'Edit Unit Translations',
			'Section List',
			'units_translations',
			'unit_id',
			'units'
		);
	}

	protected function doSection() {
		$this->doGenerate(
			'sections_vocabulary',
			'vocabulary',
			"WHERE section_uid = '".mysql_real_escape_string($this->arrPaths[3])."'",
			$this->arrPaths[3],
			'Edit Section Translations',
			'Vocabulary List',
			'sections_translations',
			'section_uid',
			'sections'
		);
	}

	protected function doVocabulary() {
		$this->doGenerate(
			'',
			'',
			"",
			$this->arrPaths[3],
			'Edit Vocabulary Translations',
			'',
			'sections_vocabulary_translations',
			'term_uid',
			'sections_vocabulary'
		);
	}

	protected function doList() {

		$this->doGenerate('years', 'year', '', 0, 'Years');
	}

}

?>