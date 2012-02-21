<?php

/**
 * school_report.php
 */

class school_report extends Controller {

	public function __construct () {
		parent::__construct();
		if (user::isLoggedIn()) {
			$objUser = new user($_SESSION['user']['uid']);
			$objUser->load();

			if ($objUser->isAdmin()) {
				$this->generate_report();
			} else {
				$objUser->logout();
				output::redirect(config::url('login/'));
			}
		} else {
			output::redirect(config::url('login/'));
		}
		
	}

	protected function generate_report () {
		set_time_limit(0);
		@ini_set('memory_limit', '256M');

		$query ="SELECT ";
		$query.="`U`.`uid`, ";
		$query.="`U`.`locale`, ";
		$query.="`U`.`registered_dts`, ";
		$query.="`U`.`email`, ";
		$query.="0 AS `payment_type`, ";
		// payment_type
		// last_login
		/*
		$query .= " ( ";
					$query .= "SELECT ";
					$query .= "`time`";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`S`.`uid` = `logging_access`.`school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") AS `last_login`, ";
		*/
		$query.="`U`.`last_seen`, ";
		$query.= "( ";
			$query.="SELECT ";
			$query.="count(`logging_access`.`uid`) ";
			$query.="FROM ";
			$query.="`logging_access` ";
			$query.="WHERE ";
			$query.="`S`.`uid` = `logging_access`.`school_uid` ";
			$query.="AND ";
			$query.="`is_login_entry` = '1' ";
		$query.=") AS `AllTime`, ";
		$query.="`U`.`active`, ";
		$query.="`U`.`access_allowed`, ";
		
		$query.="`SB`.`date_paid`, ";
		$query.="`SB`.`due_date`, ";
		$query.="`SB`.`start_dts`, ";
		$query.="`SB`.`expires_dts`, ";
		$query.="`SB`.`verified`, ";
		$query.="`SB`.`verified_dts`, ";
		$query.="`SB`.`invoice_number`, ";

		$query.="`S`.`name`, ";
		$query.="`S`.`school`, ";

		$query.="`A`.`street_name_1` AS `address`, ";
		$query.="`A`.`postcode`, ";
		$query.="`A`.`name` AS `contact`, ";

		$query.="`S`.`phone_number`, ";
		$query.="`S`.`affiliate`, ";
		$query.="`S`.`tracking_code` ";

		$query.="FROM ";
		$query.="`user` AS `U`, ";
		$query.="`subscriptions` AS `SB`, ";
		$query.="`users_schools` AS `S`, ";
		$query.="`lib_property_address_uk` AS `A` ";
		$query.="WHERE ";
		$query.="`U`.`uid`=`SB`.`user_uid` ";
		$query.="AND ";
		$query.="`U`.`uid`=`S`.`user_uid` ";
		$query.="AND ";
		$query.="`A`.`uid`=`S`.`address_id` ";
		$query.="GROUP BY `U`.`uid` ";
		$query.="ORDER BY `U`.`uid` ";
		//$query.="LIMIT 0,10 ";
		//echo $query; exit;
		$result = database::query($query);
		$filename = 'school_report.csv';
		if($result && mysql_error()=='' && mysql_num_rows($result)) {
			header( 'Content-Type: text/csv; charset=utf-8');
			header( 'Content-Disposition: attachment;filename='.$filename);
			$fp = fopen('php://output', 'w');
			$arrTitles = array(
				'uid',
				'locale',
				'registered_dts',
				'email',
				'payment_type',
				'last_login',
				'total_usage',
				'active',
				'access_allowed',
				'date_paid',
				'due_date',
				'start_dts',
				'expires_dts',
				'verified',
				'verified_dts',
				'invoice_number',
				'name',
				'school',
				'address',
				'postcode',
				'contact',
				'phone_number',
				'affiliate',
				'tracking_code'
			);
			fputcsv($fp, $arrTitles);
			while($arrRow = mysql_fetch_assoc($result)) {
				if($arrRow['last_seen'] > 0) {
					$arrRow['last_seen'] = date('Y-m-d H:i:s',$arrRow['last_seen']);
				} else {
					$arrRow['last_seen'] = 'never';
				}
				fputcsv($fp, $arrRow);
			}
			fclose($fp);
		}
	}

}

?>