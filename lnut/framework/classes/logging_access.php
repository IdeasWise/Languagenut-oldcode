<?php

class logging_access extends generic_object {

    public function __construct($uid = 0) {
        parent::__construct($uid, __CLASS__);
    }

    public function getList( $user_uid = 0 ) {
    		
    		$query	=	"SELECT COUNT(`uid`) FROM `logging_access` WHERE `user_uid` = '".$user_uid."'";
		$this->setPagination($query);
		$query	=	"SELECT `uri` as `log_uri`,  DATE_FORMAT(`time`, '%D %b %Y  %l:%i %p') as `dateNtime` FROM `logging_access`  WHERE `user_uid` = '".$user_uid."' ORDER BY `time` DESC LIMIT " . $this->get_limit();		
		return database::arrQuery($query);	
    }
	
	
	public function getLoginStates( ) {
		$Fields		= array();
		$Fields[]	= '`S`.`school`';
		$Fields[]	= '`S`.`affiliate`';
		$where = $this->SearchQueryWhere($Fields);
		if(isset($_SESSION['user']['localeRights'])) {
			$where .= "AND `S`.`language_prefix` IN (".$_SESSION['user']['localeRights'].") ";
		}
		
		$query  = "SELECT ";
		$query .= "count(DISTINCT `S`.`uid`) ";
		$query .= "FROM ";		
		$query .= "`users_schools` AS `S` ";
		$query .= "WHERE ";
		$query .= "`S`.`school` != '' ";
		$query .= $where;
		
		$this->setPagination($query, '', 20);
		
		$query  = "SELECT ";
		$query .= "`S`.`uid` AS `school_uid` ";
		$query .= ",`S`.`school` ";
		$query .= ",`S`.`affiliate` ";
		
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "`time`";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`S`.`uid` = `logging_access`.`school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") ";			
		$query .= "AS `LastLogin` ";
		
		$start_of_today = date( 'Y-m-d H:i:s', mktime(0,0,0,date('m'),date('d'),date('Y')) );
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`S`.`uid` = `logging_access`.`school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `time` >= '".$start_of_today."' ";
					
		$query .= ") ";			
		$query .= "AS `Todays` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`S`.`uid` = `logging_access`.`school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-7 day'))."' ";
					
		$query .= ") ";
		$query .= "AS `Weekly` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`S`.`uid` = `logging_access`.`school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-6 months'))."' ";
					
		$query .= ") ";
		$query .= "AS `6Months` ";

		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`S`.`uid` = `logging_access`.`school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";					
		$query .= ") ";
		$query .= "AS `AllTime` ";
		
		$query .= "FROM ";
		$query .= "`users_schools` AS `S` ";
		$query .= "WHERE ";
		$query .= "`S`.`school` != '' ";
	
		$query .= $where;
		//$query .= "GROUP BY ";
		//$query .= "`LA`.`school_uid` ";
		$query .= "ORDER BY ";
		$query .= "`S`.`school` ";
		$query .= "LIMIT " . $this->get_limit();
		//echo '<br /><br />';
		//echo $query; //exit;

		return database::arrQuery($query);	
    }
	
	public function getLoginStates_old( ) {
    	$Fields		= array();
		$Fields[]	= '`S`.`school`';
		$Fields[]	= '`S`.`affiliate`';		
		$where = $this->SearchQueryWhere($Fields);
		
    	$query  = "SELECT ";
		$query .= "count(DISTINCT `LA`.`school_uid`) ";
		$query .= "FROM ";		
		$query .= "`logging_access` AS `LA`, ";
		$query .= "`user` AS `U` ";
		$query .= "WHERE ";
		$query .= "`LA`.`user_uid` = `U`.`uid` ";
		$query .= "AND ";
		$query .= "`LA`.`school_uid` > 0 ";
		$query .= "AND ";
		$query .= "`LA`.`is_login_entry` = '1' ";
		if(isset($_SESSION['user']['localeRights'])) {
			$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}
		$query .= $where;
		echo $query; 
		$this->setPagination($query, '', 20);
		
		$query  = "SELECT ";
		$query .= "DISTINCT `LA`.`school_uid` ";
		$query .= ",`S`.`school` ";
		$query .= ",`S`.`affiliate` ";
		
		/* OLD SQL QUERY 
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "DATE_FORMAT(`time`,'%d/%m/%Y %H:%i:%s') ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`school_uid` = `school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") ";			
		$query .= "AS `LastLogin` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`school_uid` = `school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND DATE_FORMAT(`time`,'%Y-%m-%d') = '".date('Y-m-d')."' ";
					
		$query .= ") ";			
		$query .= "AS `Todays` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`school_uid` = `school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND DATE_FORMAT(`time`,'%Y-%m-%d') <= '".date('Y-m-d')."' ";
					$query .= "AND DATE_FORMAT(`time`,'%Y-%m-%d') > '".date('Y-m-d',strtotime('-7 day'))."' ";
					
		$query .= ") ";			
		$query .= "AS `Weekly` ";
		*/
		/*
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "`time`";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`school_uid` = `school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") ";			
		$query .= "AS `LastLogin` ";
		
		$start_of_today = date( 'Y-m-d H:i:s', mktime(0,0,0,date('m'),date('d'),date('Y')) );
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`school_uid` = `school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `time` >= '".$start_of_today."' ";
					
		$query .= ") ";			
		$query .= "AS `Todays` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`school_uid` = `school_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-7 day'))."' ";
					
		$query .= ") ";			
		$query .= "AS `Weekly` ";
		*/
		$query .= "FROM ";
		$query .= "`users_schools` AS `S`, ";
		$query .= "`logging_access` AS `LA` ";
		//$query .= "`user` AS `U` ";
		$query .= "WHERE ";
		$query .= "`LA`.`school_uid` = `S`.`uid` ";
	//	$query .= "AND ";
	//	$query .= "`S`.`user_uid` = `U`.`uid` ";
		$query .= "AND ";
		$query .= "`LA`.`is_login_entry` = '1' ";
		if(isset($_SESSION['user']['localeRights'])) {
			//$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}
		/*
		"
		SELECT 
		`S`.`school` ,
		`S`.`affiliate` ,
		`LA`.`school_uid` , 
			( SELECT DATE_FORMAT(`time`,'%d/%m/%Y %H:%i:%s') FROM `logging_access` WHERE `LA`.`school_uid` = `school_uid` AND `is_login_entry` = '1' ORDER BY `time` DESC LIMIT 0,1 ) AS `LastLogin` , ( SELECT count(`uid`) FROM `logging_access` WHERE `LA`.`school_uid` = `school_uid` AND `is_login_entry` = '1' AND DATE_FORMAT(`time`,'%Y-%m-%d') = '2011-04-01' ) AS `Todays` , ( SELECT count(`uid`) FROM `logging_access` WHERE `LA`.`school_uid` = `school_uid` AND `is_login_entry` = '1' AND DATE_FORMAT(`time`,'%Y-%m-%d') <= '2011-04-01' AND DATE_FORMAT(`time`,'%Y-%m-%d') > '2011-03-25' ) AS `Weekly` FROM `users_schools` AS `S`, `logging_access` AS `LA` WHERE `LA`.`school_uid` = `S`.`uid` AND `LA`.`is_login_entry` = '1' GROUP BY `LA`.`school_uid` ORDER BY `S`.`school` LIMIT 0 , 20
		
		"
		
		"
		SELECT `S`.`school` ,`S`.`affiliate` ,`LA`.`school_uid` , ( SELECT DATE_FORMAT(`time`,'%d/%m/%Y %H:%i:%s') FROM `logging_access` WHERE `LA`.`school_uid` = `school_uid` AND `is_login_entry` = '1' ORDER BY `time` DESC LIMIT 0,1 ) AS `LastLogin` , ( SELECT count(`uid`) FROM `logging_access` WHERE `LA`.`school_uid` = `school_uid` AND `is_login_entry` = '1' AND DATE_FORMAT(`time`,'%Y-%m-%d') = '2011-04-01' ) AS `Todays` , ( SELECT count(`uid`) FROM `logging_access` WHERE `LA`.`school_uid` = `school_uid` AND `is_login_entry` = '1' AND DATE_FORMAT(`time`,'%Y-%m-%d') <= '2011-04-01' AND DATE_FORMAT(`time`,'%Y-%m-%d') > '2011-03-25' ) AS `Weekly` FROM `users_schools` AS `S`, `logging_access` AS `LA` WHERE `LA`.`school_uid` = `S`.`uid` AND `LA`.`is_login_entry` = '1' GROUP BY `LA`.`school_uid` ORDER BY `S`.`school` LIMIT 0 , 20
		
		"
		*/
		$query .= $where;
		//$query .= "GROUP BY ";
		//$query .= "`LA`.`school_uid` ";
		$query .= "ORDER BY ";
		$query .= "`S`.`school` ";
		$query .= "LIMIT " . $this->get_limit();
		echo '<br /><br />';
		echo $query; //exit;
		return database::arrQuery($query);	
    }
	
	
	
	public function getSchoolUserLoginStates( $school_uid ) {
    	
		$Fields		= array();
		$Fields[]	= '`U`.`email`';				
		$where = $this->SearchQueryWhere($Fields);
		/*
    	$query  = "SELECT ";
		$query .= "count(DISTINCT `LA`.`user_uid`) ";		
		$query .= "FROM ";		
		$query .= "`logging_access` AS `LA`, ";
		$query .= "`user` AS `U` ";		
		$query .= "WHERE ";
		$query .= "`LA`.`user_uid` = `U`.`uid` ";
		$query .= "AND ";
		$query .= "`LA`.`school_uid` = '".$school_uid."' ";
		$query .= "AND ";
		$query .= "`LA`.`is_login_entry` = '1' ";
		*/
		$query  = "SELECT ";
		$query .= "count(`uid`) ";		
		$query .= "FROM ";		
		$query .= "`user` AS `U` ";		
		$query .= "WHERE ";
		$query .= "`U`.`uid` IN (";
		$query .= "SELECT DISTINCT `user_uid` FROM `logging_access` WHERE `school_uid` = '".$school_uid."' AND `is_login_entry` = '1' ";	
		$query .= " ) ";
		
		if(isset($_SESSION['user']['localeRights'])) {
			$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}
		$query .= $where;
		
		
		$this->setPagination($query, '', 20);
		/*
		$query  = "SELECT ";
		$query .= "`U`.`email` ";
		$query .= ", `LA`.`user_uid` ";
		$query .= ", REPLACE(`U`.`user_type`,',','') AS `user_type` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "`time` ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`user_uid` = `user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") ";			
		$query .= "AS `LastLogin` ";
		$start_of_today = date( 'Y-m-d H:i:s', mktime(0,0,0,date('m'),date('d'),date('Y')) );
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`user_uid` = `user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND `time` >= '".$start_of_today."' ";
					
		$query .= ") ";			
		$query .= "AS `Todays` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`user_uid` = `user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-7 day'))."' ";
					
		$query .= ") ";			
		$query .= "AS `Weekly` ";
		
		$query .= "FROM ";
		$query .= "`user` AS `U` ";
		$query .= ", `logging_access` AS `LA` ";
		$query .= "WHERE ";
		$query .= "`LA`.`user_uid` = `U`.`uid` ";
		$query .= "AND ";
		$query .= "`LA`.`is_login_entry` = '1' ";
		$query .= "AND ";
		$query .= "`LA`.`school_uid` = '".$school_uid."' ";
		$query .= $where;
		if(isset($_SESSION['user']['localeRights'])) {
			$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}
		$query .= "GROUP BY ";
		$query .= "`LA`.`user_uid` ";
		$query .= "ORDER BY ";
		$query .= "`U`.`email` ";
		$query .= "LIMIT " . $this->get_limit();
		*/
		
		
		
		$query  = "SELECT ";
		$query .= "`U`.`email` ";
		$query .= ", `U`.`uid` AS `user_uid` ";
		$query .= ", REPLACE(`U`.`user_type`,',','') AS `user_type` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "`time` ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`U`.`uid` = `logging_access`.`user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
				//	$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") ";			
		$query .= "AS `LastLogin` ";
		$start_of_today = date( 'Y-m-d H:i:s', mktime(0,0,0,date('m'),date('d'),date('Y')) );
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`U`.`uid` = `logging_access`.`user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
				//	$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND `time` >= '".$start_of_today."' ";
					
		$query .= ") ";			
		$query .= "AS `Todays` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`U`.`uid` = `logging_access`.`user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
//					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-7 day'))."' ";
					
		$query .= ") ";
		$query .= "AS `Weekly` ";

		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`U`.`uid` = `logging_access`.`user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
//					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-6 months'))."' ";
					
		$query .= ") ";
		$query .= "AS `6Months` ";

		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`U`.`uid` = `logging_access`.`user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
//					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND `time` <= '".date('Y-m-d H:i:s')."' ";
					$query .= "AND `time` > '".date('Y-m-d H:i:s',strtotime('-6 months'))."' ";
					
		$query .= ") ";
		$query .= "AS `AllTime` ";

		$query .= "FROM ";
		$query .= "`user` AS `U` ";		
		$query .= "WHERE ";
		$query .= "`U`.`uid` IN (";
		$query .= "SELECT DISTINCT `user_uid` FROM `logging_access` WHERE `school_uid` = '".$school_uid."' AND `is_login_entry` = '1' ";						 		$query .= " ) ";
		
		$query .= $where;
		if(isset($_SESSION['user']['localeRights'])) {
			$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}		
		$query .= "ORDER BY ";
		$query .= "`U`.`email` ";
		$query .= "LIMIT " . $this->get_limit();
		
		return database::arrQuery($query);	
    }
	
	
	
	public function getSchoolUserLoginStates_old( $school_uid ) {
    	
		$Fields		= array();
		$Fields[]	= '`U`.`email`';				
		$where = $this->SearchQueryWhere($Fields);
		
    	$query  = "SELECT ";
		$query .= "count(DISTINCT `LA`.`user_uid`) ";		
		$query .= "FROM ";		
		$query .= "`logging_access` AS `LA`, ";
		$query .= "`user` AS `U` ";		
		$query .= "WHERE ";
		$query .= "`LA`.`user_uid` = `U`.`uid` ";
		$query .= "AND ";
		$query .= "`LA`.`school_uid` = '".$school_uid."' ";
		$query .= "AND ";
		$query .= "`LA`.`is_login_entry` = '1' ";
		if(isset($_SESSION['user']['localeRights'])) {
			$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}
		$query .= $where;
		
		$this->setPagination($query, '', 20);
		
		$query  = "SELECT ";
		$query .= "`U`.`email` ";
		$query .= ", `LA`.`user_uid` ";
		$query .= ", REPLACE(`U`.`user_type`,',','') AS `user_type` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "DATE_FORMAT(`time`,'%d/%m/%Y %H:%i:%s') ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`user_uid` = `user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "ORDER BY `time` DESC ";
					$query .= " LIMIT 0,1 ";
					
		$query .= ") ";			
		$query .= "AS `LastLogin` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`user_uid` = `user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND DATE_FORMAT(`time`,'%Y-%m-%d') = '".date('Y-m-d')."' ";
					
		$query .= ") ";			
		$query .= "AS `Todays` ";
		
		$query .= ", ( ";
					$query .= "SELECT ";
					$query .= "count(`uid`) ";
					$query .= "FROM ";
					$query .= "`logging_access` ";
					$query .= "WHERE ";
					$query .= "`LA`.`user_uid` = `user_uid` ";
					$query .= "AND `is_login_entry` = '1' ";
					$query .= "AND `school_uid` = '".$school_uid."' ";
					$query .= "AND DATE_FORMAT(`time`,'%Y-%m-%d') <= '".date('Y-m-d')."' ";
					$query .= "AND DATE_FORMAT(`time`,'%Y-%m-%d') > '".date('Y-m-d',strtotime('-7 day'))."' ";
					
		$query .= ") ";			
		$query .= "AS `Weekly` ";
		
		$query .= "FROM ";
		$query .= "`user` AS `U` ";
		$query .= ", `logging_access` AS `LA` ";
		$query .= "WHERE ";
		$query .= "`LA`.`user_uid` = `U`.`uid` ";
		$query .= "AND ";
		$query .= "`LA`.`is_login_entry` = '1' ";
		$query .= "AND ";
		$query .= "`LA`.`school_uid` = '".$school_uid."' ";
		$query .= $where;
		if(isset($_SESSION['user']['localeRights'])) {
			$query .= "AND `U`.`locale` IN (".$_SESSION['user']['localeRights'].") ";
		}
		$query .= "GROUP BY ";
		$query .= "`LA`.`user_uid` ";
		$query .= "ORDER BY ";
		$query .= "`U`.`email` ";
		$query .= "LIMIT " . $this->get_limit();
		
		return database::arrQuery($query);	
    }
	private function SearchQueryWhere( $Fields = array() ) {
		$where = '';
		if(isset($_GET['find']) && trim($_GET['find']) != '') {
			if(strpos($_GET['find'],"/p-") !== false) {
				$_GET['find'] = substr($_GET['find'],0,strpos($_GET['find'],"/p-"));
			}
			$_GET['find'] = mysql_real_escape_string($_GET['find']);
			$where .= " AND ";
			$where .= " ( ";
				$WhereArray = array();		 
				foreach($Fields as $Field) {
					$WhereArray[] = $Field." LIKE '%".$_GET['find']."%'";
				}
				$where .= implode(" OR ", $WhereArray);
			$where .= " ) ";
		}
		return $where;
	}
    
    public function insert_delete() {

        if(isset($this->arrFields) && is_array($this->arrFields)) {
            foreach($this->arrFields as $key => $val) {
                $this->arrFields[$key]['Value'] =  mysql_real_escape_string($this->arrFields[$key]['Value']);
            }
        }

        $sql = "INSERT INTO `logging_access`
                (`sid`, `user_uid`, `uri`, `encoding`, `language`, `browser`, `remoteaddress`, `remoteresolved`, `time`, `referrerurl`, `keywords`, `searchengine`)
                VALUES
                ('{$this->arrFields['sid']['Value']}','{$this->arrFields['user_uid']['Value']}','{$this->arrFields['uri']['Value']}','{$this->arrFields['encoding']['Value']}','{$this->arrFields['language']['Value']}','{$this->arrFields['browser']['Value']}','{$this->arrFields['remoteaddress']['Value']}','{$this->arrFields['remoteresolved']['Value']}','{$this->arrFields['time']['Value']}','{$this->arrFields['referrerurl']['Value']}','{$this->arrFields['keywords']['Value']}','{$this->arrFields['searchengine']['Value']}')";

        $result = database::query($sql);
        if($result && mysql_error()=='') {
            $insert_id  =   mysql_insert_id();
        }
        return $insert_id;
    }
}
?>
