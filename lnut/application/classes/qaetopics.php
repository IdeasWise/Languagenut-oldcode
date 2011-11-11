<?php

class qaetopics extends generic_object {

    public function __construct($uid = 0) {
	parent::__construct($uid, 'qae_topic');
    }

    public function getListByLocale($uid, $luid) {
	$response = false;

	$sql = "SELECT ";
	$sql.= " *";
	$sql.= " FROM ";
	$sql.= "`qae_topic_translation` ";
	$sql.= " WHERE ";
	$sql.= " qaetopic_uid='$uid' ";
	$sql.= " AND ";
	$sql.= " language_uid_support='$luid'";
	$sql.= " LIMIT 1";

	$res = database::query($sql);

	if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
	    $response = array();
	    while ($row = mysql_fetch_assoc($res)) {
		$response= array(
		    'uid' => stripslashes($row['uid']),
		    'title' => stripslashes($row['title']),
		    'primary_image_path' => stripslashes($row['primary_image_path']),
		    'primary_image_caption' => stripslashes($row['primary_image_caption']),
		    'secondary_image_path' => stripslashes($row['secondary_image_path']),
		    'secondary_image_caption' => stripslashes($row['secondary_image_caption']),
		    'introduction' => stripslashes($row['introduction'])
		);
	    }
	}

	return $response;
    }

    public function getListByTitle($OrderBy = 'title') {
	$response = false;

	$sql = "SELECT ";
	$sql.= "count(`uid`) ";
	$sql.= "FROM ";
	$sql.= "`qae_topic` ";
	
	$this->setPagination($sql);
	
	$sql = "SELECT ";
	$sql.= "`uid`, ";
	$sql.= "`title` ";
	$sql.= "FROM ";
	$sql.= "`qae_topic` ";
	$sql.= "ORDER BY ";
	$sql.= "`" . $OrderBy . "` ASC";
	$sql.= " LIMIT ". $this->get_limit();

	$res = database::query($sql);

	if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
	    $response = array();
	    while ($row = mysql_fetch_assoc($res)) {
		$response[$row['uid']] = array(
		    'title' => stripslashes($row['title']),
		);
	    }
	}

	return $response;
    }

    public function isValidCreate($arrData=array()) {

	$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';

	if ($title != '') {
	    $sql = "INSERT INTO `qae_topic` SET
				title='{$title}'";
	    $topic_uid = database::insert($sql);

	    $validExtension = array('jpg', 'jpeg', 'gif', 'png');
	    $imgClass = new image_resize();
	    $arrLocales = language::getPrefixes();
	    if (count($arrLocales) > 0) {

		foreach ($arrLocales as $luid => $arrData) {
		    $primaryImageName = "";
		    $secondaryImageName = "";
		    $imgPath = config::get('site') . '/images/qae/' . $arrData['prefix'] . "/";
		    if (!file_exists($imgPath)) {
			@mkdir($imgPath);
		    }
		    if (isset($_FILES['primary_image_path_' . $arrData['prefix']]) && !empty($_FILES['primary_image_path_' . $arrData['prefix']]['name'])) {
			$ext = strtolower(array_pop(explode(".", $_FILES['primary_image_path_' . $arrData['prefix']]['name'])));

			if (array_search($ext, $validExtension) !== false) {
			    $image_name = '';
			    $thumb_name = '';
			    $suffix = '';
			    $parts = explode('.', $_FILES['primary_image_path_' . $arrData['prefix']]['name']);
			    $suffix = $parts[sizeof($parts) - 1];
			    $primaryImageName = $image_name = time() . '.' . $suffix;
			    $thumb_name = 'thumb_' . $image_name;
			    $imgClass->resize_image($imgPath . $thumb_name, 'primary_image_path_' . $arrData['prefix'], '196', '148');
			    @move_uploaded_file($_FILES['primary_image_path_' . $arrData['prefix']]['tmp_name'], $imgPath . $image_name);
			} else {
			    if (is_array($topicTranslation)) {
				foreach ($topicTranslation as $topic) {
				    @unlink($imgPath . $topic["primary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["primary_image_path"]);
				    @unlink($imgPath . $topic["secondary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["secondary_image_path"]);
				}
			    }
			    $sql = "DELETE FROM `qae_topic` WHERE uid='{$topic_uid}'";
			    database::query($sql);

			    $arrMsgData['message'] = 'Please upload valid image';
			    return $arrMsgData;
			}
		    }
		    if (isset($_FILES['secondary_image_path_' . $arrData['prefix']]) && !empty($_FILES['secondary_image_path_' . $arrData['prefix']]['name'])) {
			$ext = strtolower(array_pop(explode(".", $_FILES['secondary_image_path_' . $arrData['prefix']]['name'])));
			if (array_search($ext, $validExtension) !== false) {
			    $image_name = '';
			    $thumb_name = '';
			    $suffix = '';
			    $parts = explode('.', $_FILES['secondary_image_path_' . $arrData['prefix']]['name']);
			    $suffix = $parts[sizeof($parts) - 1];
			    $secondaryImageName = $image_name = '2'.time() . '.' . $suffix;
			    $thumb_name = 'thumb_' . $image_name;
			    $imgClass->resize_image($imgPath . $thumb_name, 'secondary_image_path_' . $arrData['prefix'], '196', '148');
			    @move_uploaded_file($_FILES['secondary_image_path_' . $arrData['prefix']]['tmp_name'], $imgPath . $image_name);
			} else {
			    if (is_array($topicTranslation)) {
				foreach ($topicTranslation as $topic) {
				    @unlink($imgPath . $topic["primary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["primary_image_path"]);
				    @unlink($imgPath . $topic["secondary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["secondary_image_path"]);
				}
			    }
			    $sql = "DELETE FROM `qae_topic` WHERE uid='{$topic_uid}'";
			    database::query($sql);
			    $arrMsgData['message'] = 'Please upload valid image';
			    return $arrMsgData;
			}
		    }
		    $title = addslashes($_POST['title_' . $arrData['prefix']]);
		    $primaryImageName = addslashes($primaryImageName);
		    $secondaryImageName = addslashes($secondaryImageName);
		    $primary_image_caption = addslashes($_POST['primary_image_caption_' . $arrData['prefix']]);
		    $secondary_image_caption = addslashes($_POST['secondary_image_caption_' . $arrData['prefix']]);
		    $introduction = addslashes($_POST['introduction_' . $arrData['prefix']]);
		    $topicTranslation[$arrData['prefix']] = array(
			'title' => $title,
			'primary_image_path' => $primaryImageName,
			'primary_image_caption' => $primary_image_caption,
			'secondary_image_path' => $secondaryImageName,
			'secondary_image_caption' => $secondary_image_caption,
			'introduction' => $introduction
		    );
		}
		foreach ($arrLocales as $luid => $arrData) {
		     $sql = "INSERT INTO `qae_topic_translation` SET
			    qaetopic_uid='{$topic_uid}',
			    locale='{$arrData['prefix']}',
			    language_uid_support='{$luid}',
			    title='{$topicTranslation[$arrData['prefix']]['title']}',
			    primary_image_path='{$topicTranslation[$arrData['prefix']]['primary_image_path']}',
			    primary_image_caption='{$topicTranslation[$arrData['prefix']]['primary_image_caption']}',
			    secondary_image_path='{$topicTranslation[$arrData['prefix']]['secondary_image_path']}',
			    secondary_image_caption='{$topicTranslation[$arrData['prefix']]['secondary_image_caption']}',
			    introduction='{$topicTranslation[$arrData['prefix']]['introduction']}'
			";
		    database::insert($sql);
		}
	    }



	    return true;
	} else {
	    $arrData['title'] = $title;
	    $arrData['message'] = 'Please complete all fields';
	}

	return $arrData;
    }

    public function isValidUpdate($arrData=array()) {

	$topic_uid = $qaetopic_uid = (isset($arrData['qaetopic_uid']) && (int) $arrData['qaetopic_uid'] > 0) ? $arrData['qaetopic_uid'] : '';
	$title = (isset($arrData['title']) && strlen(trim($arrData['title'])) > 0) ? $arrData['title'] : '';

	if ($qaetopic_uid != '' && $title != '') {

	    $this->__construct($qaetopic_uid);
	    $this->load();

	    $this->arrFields['title']['Value'] = $title;
	    $this->save();

	    $validExtension = array('jpg', 'jpeg', 'gif', 'png');
	    $imgClass = new image_resize();
	    $arrLocales = language::getPrefixes();
	    if (count($arrLocales) > 0) {

		foreach ($arrLocales as $luid => $arrData) {
		    $primaryImageName = "";
		    $secondaryImageName = "";
		    $imgPath = config::get('site') . '/images/qae/' . $arrData['prefix'] . "/";
		    if (!file_exists($imgPath)) {
			@mkdir($imgPath);
		    }
		    if (isset($_FILES['primary_image_path_' . $arrData['prefix']]) && !empty($_FILES['primary_image_path_' . $arrData['prefix']]['name'])) {
			$ext = strtolower(array_pop(explode(".", $_FILES['primary_image_path_' . $arrData['prefix']]['name'])));

			if (array_search($ext, $validExtension) !== false) {
			    $image_name = '';
			    $thumb_name = '';
			    $suffix = '';
			    $parts = explode('.', $_FILES['primary_image_path_' . $arrData['prefix']]['name']);
			    $suffix = $parts[sizeof($parts) - 1];
			    $primaryImageName = $image_name = time() . '.' . $suffix;
			    $thumb_name = 'thumb_' . $image_name;
			    $imgClass->resize_image($imgPath . $thumb_name, 'primary_image_path_' . $arrData['prefix'], '196', '148');
			    @move_uploaded_file($_FILES['primary_image_path_' . $arrData['prefix']]['tmp_name'], $imgPath . $image_name);
			} else {
			    if (is_array($topicTranslation)) {
				foreach ($topicTranslation as $topic) {
				    @unlink($imgPath . $topic["primary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["primary_image_path"]);
				    @unlink($imgPath . $topic["secondary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["secondary_image_path"]);
				}
			    }
			    $sql = "DELETE FROM `qae_topic` WHERE uid='{$topic_uid}'";
			    database::query($sql);

			    $arrMsgData['message'] = 'Please upload valid image';
			    return $arrMsgData;
			}
		    }
		    if (isset($_FILES['secondary_image_path_' . $arrData['prefix']]) && !empty($_FILES['secondary_image_path_' . $arrData['prefix']]['name'])) {
			$ext = strtolower(array_pop(explode(".", $_FILES['secondary_image_path_' . $arrData['prefix']]['name'])));
			if (array_search($ext, $validExtension) !== false) {
			    $image_name = '';
			    $thumb_name = '';
			    $suffix = '';
			    $parts = explode('.', $_FILES['secondary_image_path_' . $arrData['prefix']]['name']);
			    $suffix = $parts[sizeof($parts) - 1];
			    $secondaryImageName = $image_name = '2'.time() . '.' . $suffix;
			    $thumb_name = 'thumb_' . $image_name;
			    $imgClass->resize_image($imgPath . $thumb_name, 'secondary_image_path_' . $arrData['prefix'], '196', '148');
			    @move_uploaded_file($_FILES['secondary_image_path_' . $arrData['prefix']]['tmp_name'], $imgPath . $image_name);
			} else {
			    if (is_array($topicTranslation)) {
				foreach ($topicTranslation as $topic) {
				    @unlink($imgPath . $topic["primary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["primary_image_path"]);
				    @unlink($imgPath . $topic["secondary_image_path"]);
				    @unlink($imgPath . 'thumb_' . $topic["secondary_image_path"]);
				}
			    }
			    $sql = "DELETE FROM `qae_topic` WHERE uid='{$topic_uid}'";
			    database::query($sql);
			    $arrMsgData['message'] = 'Please upload valid image';
			    return $arrMsgData;
			}
		    }
		    $title = addslashes($_POST['title_' . $arrData['prefix']]);
		    $primaryImageName = addslashes($primaryImageName);
		    $secondaryImageName = addslashes($secondaryImageName);
		    $primary_image_caption = addslashes($_POST['primary_image_caption_' . $arrData['prefix']]);
		    $secondary_image_caption = addslashes($_POST['secondary_image_caption_' . $arrData['prefix']]);
		    $introduction = addslashes($_POST['introduction_' . $arrData['prefix']]);
		    $topicTranslation[$arrData['prefix']] = array(
			'title' => $title,
			'primary_image_path' => $primaryImageName,
			'primary_image_caption' => $primary_image_caption,
			'secondary_image_path' => $secondaryImageName,
			'secondary_image_caption' => $secondary_image_caption,
			'introduction' => $introduction,
			'old_primary_image_path' => $_POST['old_primary_image_path_' . $arrData['prefix']],
			'old_secondary_image_path' => $_POST['old_secondary_image_path_' . $arrData['prefix']]
		    );
		}
		foreach ($arrLocales as $luid => $arrData) {
		    $imgPath = config::get('site') . '/images/qae/' . $arrData['prefix'] . "/";
		    // echo $imgPath . $topicTranslation[$arrData['prefix']]["old_primary_image_path"];
		    // echo "<br>". $imgPath . 'thumb_' . $topicTranslation[$arrData['prefix']]["old_primary_image_path"];
		    $primaryImageSql="";
		    $secondaryImageSql="";

		    if (!empty($topicTranslation[$arrData['prefix']]["primary_image_path"])) {
			@unlink($imgPath . $topicTranslation[$arrData['prefix']]["old_primary_image_path"]);
			@unlink($imgPath . 'thumb_' . $topicTranslation[$arrData['prefix']]["old_primary_image_path"]);
			$primaryImageSql="primary_image_path='{$topicTranslation[$arrData['prefix']]['primary_image_path']}',";
		    }
		    if (!empty($topicTranslation[$arrData['prefix']]["secondary_image_path"])) {
			@unlink($imgPath . $topicTranslation[$arrData['prefix']]["old_secondary_image_path"]);
			@unlink($imgPath . 'thumb_' . $topicTranslation[$arrData['prefix']]["old_secondary_image_path"]);
			$secondaryImageSql="secondary_image_path='{$topicTranslation[$arrData['prefix']]['secondary_image_path']}',";
		    }
		     $sql = "UPDATE `qae_topic_translation` SET
			    title='{$topicTranslation[$arrData['prefix']]['title']}',
			    {$primaryImageSql}
			    primary_image_caption='{$topicTranslation[$arrData['prefix']]['primary_image_caption']}',
			    {$secondaryImageSql}
			    secondary_image_caption='{$topicTranslation[$arrData['prefix']]['secondary_image_caption']}',
			    introduction='{$topicTranslation[$arrData['prefix']]['introduction']}'
			    WHERE
			    qaetopic_uid='{$topic_uid}'
			    AND
			    language_uid_support='{$luid}'
			";
		    database::query($sql);
		}
	    }

	    return true;
	} else {
	    $arrData['title'] = $title;
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

    public function getTranslationByUid($uid) {
	$response = false;

	$sql = "SELECT * FROM `qae_topic_translation`
		WHERE
		qaetopic_uid='{$uid}'";
	$res = database::query($sql);

	if ($res && mysql_error() == '' && mysql_num_rows($res) > 0) {
	    $response = array();
	    while ($row = mysql_fetch_assoc($res)) {
		$response[$row['uid']] = array(
		    'locale' => stripslashes($row['locale']),
		    'language_uid_support' => stripslashes($row['language_uid_support']),
		    'title' => stripslashes($row['title']),
		    'primary_image_path' => stripslashes($row['primary_image_path']),
		    'primary_image_caption' => stripslashes($row['primary_image_caption']),
		    'secondary_image_path' => stripslashes($row['secondary_image_path']),
		    'secondary_image_caption' => stripslashes($row['secondary_image_caption']),
		    'introduction' => stripslashes($row['introduction'])
		);
	    }
	}

	return $response;
    }

}

?>