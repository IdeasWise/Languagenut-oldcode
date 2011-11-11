<?php

class notificationevent extends generic_object {

    public function __construct($uid = 0) {
	parent::__construct($uid, 'notification_event');
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

    public function getListByname($OrderBy = 'name') {
	$response = false;

	$sql = "SELECT ";
	$sql.= "count(`uid`) ";
	$sql.= "FROM ";
	$sql.= "`notification_event` ";

	$this->setPagination($sql);

	$sql = "SELECT ";
	$sql.= " uid,name ";
	$sql.= "FROM ";
	$sql.= "`notification_event` ";
	$sql.= "ORDER BY ";
	$sql.= "`" . $OrderBy . "` ASC";
	$sql.= " LIMIT " . $this->get_limit();

	$res = database::query($sql);

	if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
	    $response = array();
	    while ($row = mysql_fetch_assoc($res)) {
		$response[$row['uid']] = array(
		    'name' => stripslashes($row['name'])		    
		);
	    }
	}

	return $response;
    }

    public function generateToken($title) {
	$token = format::to_friendly_url($title);
	$i = 0;
	while (1) {
	    $sql = "SELECT count(uid) as total FROM `notification_event` WHERE token='{$token}'";
	    $result = database::query($sql);
	    $data = mysql_fetch_assoc($result);
	    if ($data["total"] == "0") {
		return $token;
		break;
	    }
	    $token = format::to_friendly_url($title.$i);
	    $i++;
	}
    }

    public function isValidCreate($arrData=array()) {

	$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? addslashes($arrData['name']) : '';
	$description = (isset($arrData['description']) && strlen(trim($arrData['description'])) > 0) ? addslashes($arrData['description']) : '';
	$token=$this->generateToken($name);

	if ($name != '' && $description!="") {
	    $sql = "INSERT INTO `notification_event` SET
				name='{$name}',
				token='{$token}',
				description='{$description}'
				";
	    $notificationevent_uid = database::insert($sql);

	    $arrLocales = language::getPrefixes();
	    if (count($arrLocales) > 0) {
		foreach ($arrLocales as $luid => $arrData) {
		    $sql = "INSERT INTO `notification_event_translation` SET
			    notification_event_uid='{$notificationevent_uid}',
			    locale='{$arrData['prefix']}',
			    language_uid='{$luid}',
			    name='".addslashes($_POST["name_".$arrData['prefix']])."',
			    message='".addslashes($_POST["message_".$arrData['prefix']])."'
			";
		    database::insert($sql);
		}
	    }
	    return true;
	} else {
	    $arrData['name'] = $name;	    
	    $arrData['description'] = $description;
	    $arrData['message'] = 'Please complete all fields';
	}

	return $arrData;
    }

    public function isValidUpdate($arrData=array()) {

	$notificationevent_uid = $notification_event_uid = (isset($arrData['notification_event_uid']) && (int) $arrData['notification_event_uid'] > 0) ? $arrData['notification_event_uid'] : '';
	$name = (isset($arrData['name']) && strlen(trim($arrData['name'])) > 0) ? $arrData['name'] : '';
	$description = (isset($arrData['description']) && strlen(trim($arrData['description'])) > 0) ? addslashes($arrData['description']) : '';
	

	if ($notification_event_uid != '' && $name != '' && $description!='') {

	    $this->__construct($notification_event_uid);
	    $this->load();

	    $this->arrFields['name']['Value'] = $name;
	    $this->arrFields['description']['Value'] = $description;
	    
	    $this->save();

	   $arrLocales = language::getPrefixes();
	   if(count($arrLocales)>0){
		foreach ($arrLocales as $luid => $arrData) {
		    $name = addslashes($_POST['name_' . $arrData['prefix']]);
		    $message = addslashes($_POST['message_' . $arrData['prefix']]);
		    $sql = "UPDATE `notification_event_translation` SET
			    name='{$name}',
			    message='{$message}'
			    WHERE
			    notification_event_uid='{$notificationevent_uid}'
			    AND
			    language_uid='{$luid}'
			";
		    database::query($sql);
		}
	    }

	    return true;
	} else {
	    $arrData['name'] = $name;
	    $arrData['description'] = $description;
	    $arrData['message'] = 'Please complete all fields';
	}

	return $arrData;
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