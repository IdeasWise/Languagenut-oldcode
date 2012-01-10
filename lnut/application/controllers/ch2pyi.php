<?php

/**
 * trynow.php
 */

class Test extends Controller {

	public function __construct () {
		parent::__construct();

		$this->page();
	}

	
	public function page() {

		$arrTables = array(
			array(
				'tableName'			=>'activity_skill_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'activity_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'certificate_messages_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'difficulty_level_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'email_templates_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'exercise_qae_topic_content_question_option_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_content_question_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_content_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_qae_topic_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'exercise_type_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'flash_translations_locales',
				'fieldName'			=>'support_language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'game_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'language_translation',
				'fieldName'			=>'language_translation_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_children_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_contact_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_culture_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_games_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_songs_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_teachers_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_index_tab_welcome_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_messages_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_privacy_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_1_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_2_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_3_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_homeuser_stage_4_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_1_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_2_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_3_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_school_stage_4_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_subscribe_select_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_terms_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'page_widget_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'qae_topic_translation',
				'fieldName'			=>'language_uid_support',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'reference_material_type_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>'locale'
			),
			array(
				'tableName'			=>'school_registration_templates_translations',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'sections_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'sections_vocabulary_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'send_application_translation',
				'fieldName'			=>'locale',
				'fieldType'			=>'str',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'speaking_and_listening_translation',
				'fieldName'			=>'language_uid',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'units_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			),
			array(
				'tableName'			=>'years_translations',
				'fieldName'			=>'language_id',
				'fieldType'			=>'int',
				'optionalFieldName'	=>''
			)
		);

		$from_locale='ch';
		$to_locale	='pyi';
		$from_uid	=7;
		$to_uid		=7;

		$copyFrom	=null;
		$copyTo		=null;

		foreach($arrTables as $arrTable) {
			if($arrTable['fieldType']=='int' && trim($arrTable['optionalFieldName'])=='') {
				continue;
			}
			if($arrTable['fieldType']=='int') {
				$copyFrom	=$from_uid;
				$copyTo		=$to_uid;
			} else {
				$copyFrom	=$from_locale;
				$copyTo		=$to_locale;
			}
			if(trim($arrTable['optionalFieldName'])=='') {
				$this->copySiteContentTranslation(
					$arrTable['tableName'],
					$arrTable['fieldName'],
					$copyFrom,
					$copyTo
				);
			} else {
				$this->copySiteContentTranslation(
					$arrTable['tableName'],
					$arrTable['fieldName'],
					$copyFrom,
					$copyTo,
					trim($arrTable['optionalFieldName']),
					$to_locale
				);
			}
		}
	}

	public function copySiteContentTranslation($txtTable=null, $txtLocaleFieldName=null, $copyFrom=null, $copyTo=null,$optionalFieldName=null, $optionalFieldValue=null) {
		$query ="UPDATE ";
		$query.="`".$txtTable."` ";
		$query.="SET ";
		$query.="`".$txtLocaleFieldName."`='".$copyTo."' ";
		if($optionalFieldName!=null && $optionalFieldValue!=null) {
			$query.=",`".$optionalFieldName."`='".$optionalFieldValue."' ";
		}
		$query.="WHERE ";
		$query.="`".$txtLocaleFieldName."`='".$copyFrom."' ";
		echo '<br><br>'.$query;
		//database::query($query);
		/*
		if(mysql_error()!='') {
			die('error: '.mysql_error().'<br><br>query: '.$query);
		}*/

	}

}

?>