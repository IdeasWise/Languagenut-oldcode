<?php

class notification extends generic_object {

    public function __construct($uid = 0) {
	parent::__construct($uid, 'notification');
    }

    public function getListByLocale($uid, $luid) {
	$response = false;

	$sql = "SELECT ";
	$sql.= " *";
	$sql.= " FROM ";
	$sql.= "`notification_event_translation` ";
	$sql.= " WHERE ";
	$sql.= " notification_event_uid='$uid' ";
	$sql.= " AND ";
	$sql.= " language_uid='$luid'";
	$sql.= " LIMIT 1";

	$res = database::query($sql);

	if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
	    $response = array();
	    while ($row = mysql_fetch_assoc($res)) {
		$response = array(
		    'uid' => stripslashes($row['uid']),
		    'name' => stripslashes($row['name']),	    
		    'message' => stripslashes($row['message'])
		);
	    }
	}

	return $response;
    }

    public function getList($OrderBy = 'event_type_token') {
	$response = false;

	$sql = "SELECT ";
	$sql.= "count(n.uid) ";
	$sql.= "FROM ";
	$sql.= "`notification` n ";
	$sql.= " INNER JOIN";
	$sql.= " `user` fu";
	$sql.= " ON";
	$sql.= " n.from_uid=fu.uid";
	$sql.= " INNER JOIN";
	$sql.= " `user` tu";
	$sql.= " ON";
	$sql.= " n.to_uid=tu.uid";

	$this->setPagination($sql);

	$sql = "SELECT ";
	$sql.= " fu.username_open as from_username";
	$sql.= " ,tu.username_open as to_username";
	$sql.= " ,fu.user_type as from_usertype";
	$sql.= " ,tu.user_type as to_usertype";
	$sql.= " ,n.uid ";
	$sql.= " ,n.message ";
	$sql.= " ,n.notification_created ";
	$sql.= " FROM ";
	$sql.= "`notification` n ";
	$sql.= " INNER JOIN";
	$sql.= " `user` fu";
	$sql.= " ON";
	$sql.= " n.from_uid=fu.uid";
	$sql.= " INNER JOIN";
	$sql.= " `user` tu";
	$sql.= " ON";
	$sql.= " n.to_uid=tu.uid";
	$sql.= " ORDER BY ";
	$sql.= "`" . $OrderBy . "` ASC";
	$sql.= " LIMIT " . $this->get_limit();

	$res = database::query($sql);

	if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
	    $response = array();
	    while ($row = mysql_fetch_assoc($res)) {
		$response[$row['uid']] = array(
		    'from_username' => stripslashes($row['from_username']),
		    'to_username' => stripslashes($row['to_username']),
		    'from_usertype' => stripslashes($row['from_usertype']),
		    'to_usertype' => stripslashes($row['to_usertype']),
		    'message' => stripslashes((strlen($row['message'])>25)?substr($row['message'], 0,25)."...":$row['message']),
		    'notification_created' => stripslashes($row['notification_created'])
		    
		);
	    }
	}

	return $response;
    }
    
    public function getFields() {
	$response = array();

	foreach ($this->arrFields as $key => $val) {
	    $response[$key] = $this->arrFields[$key]['Value'];
	}

	return $response;
    }
    
}

?>