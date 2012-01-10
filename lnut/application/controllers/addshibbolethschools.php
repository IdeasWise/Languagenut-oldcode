<?php

/**
 * addshibbolethschools.php
 */

class createschools extends Controller {

	public function __construct () {
		parent::__construct();

		$this->page();
	}
	

	public function page() {
		set_time_limit(0);
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
		echo '</pre>';
		exit;
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

			// ADD Authority IN ADDRESS TABLE
			$address_id = 0;
			$query ="INSERT INTO `lib_property_address_uk` SET ";
			$query.="`county` ='".$this->sql_quote($arrData[1])."' ";
			database::query($query);
			$address_id = mysql_insert_id();

			// INSERT SCHOOL IN USERS SCHOOLS TABLE...
			$school_uid = 0;
			$query = "INSERT INTO `users_schools` SET ";
			$query .= "`user_uid` = '".$uid."', ";
			$query .= "`school` = '".$this->sql_quote($arrData[2])."', ";
			$query .= "`address_id` = '".$this->sql_quote($address_id)."', ";
			$query .= "`provider_uid` = '".$this->sql_quote('@atomwide')."', ";
			$query .= "`institution_uid` = '".$this->sql_quote($arrData[0])."' ";
			$res		= database::query( $query ) or die($query.'<br>'.mysql_error());
			$school_uid	= mysql_insert_id();
			
			// CREATE SCHOOL INVOICE OR ADD SUBSCRIPTION ENTRY FOR LGFL STANDARD

			$query = "INSERT INTO `subscriptions` SET ";
			$query .= "`user_uid` = '".$uid."', ";
			$query .= "`due_date` = '".date('Y-m-d H:i:s',strtotime('+5 year'))."', ";
			$query .= "`expires_dts` = '".date('Y-m-d H:i:s',strtotime('+5 year'))."', ";
			$query .= "`start_dts` = '".date('Y-m-d H:i:s')."', ";
			$query .= "`invoice_for` = 'school', ";
			$query .= "`package_token` = 'lgfl_standard', ";
			$query .= "`invoice_number` = '".(1600+$uid)."' ";
			database::query( $query ) or die($query.'<br>'.mysql_error());

			// CREATE SCHOOL INVOICE OR ADD SUBSCRIPTION ENTRY FOR LGFL EAL
			$query = "INSERT INTO `subscriptions` SET ";
			$query .= "`user_uid` = '".$uid."', ";
			$query .= "`due_date` = '".date('Y-m-d H:i:s',strtotime('+5 year'))."', ";
			$query .= "`expires_dts` = '".date('Y-m-d H:i:s',strtotime('+5 year'))."', ";
			$query .= "`start_dts` = '".date('Y-m-d H:i:s')."', ";
			$query .= "`invoice_for` = 'school', ";
			$query .= "`package_token` = 'lgfl_eal', ";
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

}

?>