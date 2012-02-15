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
		set_time_limit(0);
		$objUnit = new units();
		$objUnit->unit_song_and_story_cron();
		echo 'sucess!('.date('d/m/y H:i:s').')';
	}

	public function page_delete_2() {
		
	}
	public function page_delete() {
		ini_set('auto_detect_line_endings', true);
		$file = config::get('root').'ShibScopes.csv';
		$row = 0;
		$arrData = array();
		if (($handle = fopen($file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle)) !== FALSE) {
			/*
				echo '<pre>';
				print_r($data);
				echo '</pre>';
				exit;
				$row++;
				if($row==1) {
					continue;
				}
			*/
				$row++;
				if($row==1) {
					continue;
				}
				//$arrData[] = addslashes(strtolower($data[2]));
				//$arrData[] = $data;
				$user_uid = false;
				if($user_uid = $this->does_school_exist($data[2])) {
					$this->update_existing_school_details($user_uid,$data);
					$arrData[] = array(
						'data'=>$data,
						'status'=>'update entry'
					);
				} else {
					$this->createSchool($data);
					$arrData[] = array(
						'data'=>$data,
						'status'=>'new entry'
					);
				}
			}
			fclose($handle);
		}
		ini_set('auto_detect_line_endings', false);
		echo '<pre>';
		print_r($arrData);
		exit;
		echo '<pre>';
		print_r($arrData);

		//$schools = "'".implode("','",$arrData)."'";
		echo '</pre>';
		exit;
		$query = "SELECT DISTINCT `uid` FROM `users_schools` WHERE LOWER(`school`) IN (".$schools.") ORDER BY `uid` ";
		$result = database::query($query);
		if(mysql_error()!='') {
			echo mysql_error();
		} else {
			$arrIDs = array();
			echo mysql_num_rows($result);
			while($arrRow=mysql_fetch_array($result)) {
				$arrIDs[] = $arrRow['uid'];
			}
			echo '<pre>';
			print_r($arrIDs);
			echo '</pre>';
			
		}
		
	}

	public function does_school_exist($school_name) {
		$user_uid = false;
		$query ="SELECT ";
		$query.="`user_uid` ";
		$query.="FROM ";
		$query.="`users_schools` ";
		$query.="WHERE ";
		$query.="LOWER(`school`) = '".mysql_real_escape_string(addslashes(strtolower($school_name)))."' ";
		$query.="LIMIT 0,1 ";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			$user_uid = $row['user_uid'];
		}
		return $user_uid;
	}

	public function update_existing_school_details($user_uid=null,$arrData=array()) {
		if($user_uid!=null) {
			// update user table
			$query ="UPDATE ";
			$query.="`user` ";
			$query.="SET ";
			$query.="`provider_uid`='".mysql_real_escape_string('@atomwide')."',";
			$query.="`institution_uid`='".mysql_real_escape_string($arrData[0])."' ";
			$query.="WHERE ";
			$query.="`uid` = '".$user_uid."' ";
			$query.="LIMIT 0,1";
			database::query($query);

			// update users_schools table
			$query ="UPDATE ";
			$query.="`users_schools` ";
			$query.="SET ";
			$query.="`provider_uid`='".mysql_real_escape_string('@atomwide')."',";
			$query.="`institution_uid`='".mysql_real_escape_string($arrData[0])."' ";
			$query.="WHERE ";
			$query.="`user_uid` = '".$user_uid."' ";
			$query.="LIMIT 0,1";
			database::query($query);
		}
	}

	public function createSchool($arrData=array()) {
		$uid				= 0;
		$password			= $this->generatePassword();
		$registration_key	= '';
		// INSERT SCHOOL RECORD IN MAIN USER TABLE 
		$query = "INSERT INTO `user` SET ";
		$query .= "`registered_dts` = '".date('Y-m-d H:i:s')."', ";
		$query .= "`registration_ip` = '".$_SERVER['REMOTE_ADDR']."', ";
		$query .= "`email` = '".$this->sql_quote('admin'.$arrData[0])."', ";
		$query .= "`password` = '".md5($password)."', ";
		$query .= "`username_open` = '".$this->sql_quote($arrData[0])."', ";
		$query .= "`password_open` = '".$password."', ";
		$query .= "`provider_uid` = '".$this->sql_quote('@atomwide')."', ";
		$query .= "`institution_uid` = '".$this->sql_quote($arrData[0])."', ";
		$query .= "`active` = '1', ";
		$query .= "`access_allowed` = '1', ";
		$query .= "`allow_access_without_sub` = '1', ";
		$query .= "`locale` = 'en', ";
		$query .= "`user_type` = 'school' ";
		//$query;
		$res = database::query( $query );
		$uid = mysql_insert_id();

		if( is_numeric($uid) && $uid > 0 ) {
			$registration_key = md5( $uid .'-'. $_SERVER['REMOTE_ADDR'] );
			$sql = "UPDATE `user` SET ";
			$sql .="`registration_key` = '".$registration_key."' ";
			$sql .="WHERE `uid` = '".$uid."'";
			database::query($sql);

			// INSERT SCHOOL IN USERS SCHOOLS TABLE...
			$school_uid = 0;
			$query = "INSERT INTO `users_schools` SET ";
			$query .= "`user_uid` = '".$uid."', ";
			$query .= "`school` = '".$this->sql_quote($arrData[2])."', ";
			$query .= "`provider_uid` = '".$this->sql_quote('@atomwide')."', ";
			$query .= "`institution_uid` = '".$this->sql_quote($arrData[0])."' ";
			$res		= database::query( $query ) or die($query.'<br>'.mysql_error());
			$school_uid	= mysql_insert_id();
			
			// CREATE SCHOOL INVOICE OR ADD SUBSCRIPTION ENTRY

			$query = "INSERT INTO `subscriptions` SET ";
			$query .= "`user_uid` = '".$uid."', ";
			$query .= "`due_date` = '".date('Y-m-d H:i:s',strtotime('+5 year'))."', ";
			$query .= "`expires_dts` = '".date('Y-m-d H:i:s',strtotime('+5 year'))."', ";
			$query .= "`start_dts` = '".date('Y-m-d H:i:s')."', ";
			$query .= "`invoice_for` = 'school', ";
			$query .= "`invoice_number` = '".(1600+$uid)."' ";
			database::query( $query ) or die($query.'<br>'.mysql_error());
		}

		// 
	}

	public function generatePassword( ) {
		
		$alfa = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$token = "";
		for($i = 0; $i < 6; $i ++) {
			$token .= $alfa[@rand(0, 61)];
		}
		return $token;
	}

	public function sql_quote( $value ) {
		if(get_magic_quotes_gpc() ) {
			$value = stripslashes( $value );
		}
		//check if this function exists
		if( function_exists( "mysql_real_escape_string" ) ) {
			$value = mysql_real_escape_string( $value );
		} else { //for PHP version < 4.3.0 use addslashes
			$value = addslashes( $value );
		}
		return $value;
	}

	public function ch2pyi() {

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