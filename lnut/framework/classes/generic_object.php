<?php

class generic_object extends pager {

	// private properties
	private $uid = null;
	private $table = '';
	private $valid = false;
	private $data = array();
	public $TableData = array();
	protected $arrFields = array();
	protected $arrHelpers = array();

	public function __construct($uid = 0, $table = '', $takeautoid = false) {

		if ($table != "") {
			$this->table = format::toTableName($table);
		}
		if (is_numeric($uid) && (int) $uid > 0) {
			$this->uid = $uid;
		}

		if (strlen($this->table) > 0) {

			$query = "SHOW COLUMNS FROM `{$this->table}`";
			$result = database::query($query);

			if (mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$type = $row['Type'];
					$data = '';
					$size = null;

					if (preg_match('/(.*)\((\d+)\)/', $type, $matches)) {
						$data = strtoupper($matches[1]);
						$size = $matches[2];
					} else if (preg_match('/(.*)\((\d+,\d+)\)/', $type, $matches)) {
						$data = strtoupper($matches[1]);
						$size = $matches[2];
					} else {
						$data = strtoupper($type);
					}
					if ($row['Field'] != "uid" || $takeautoid) {
						$this->arrFields[$row['Field']] = array(
							'Type' => array(
								'Size' => $size,
								'Data' => $data
							),
							'Null' => $row['Null'],
							'Key' => $row['Key'],
							'Default' => $row['Default'],
							'Extra' => $row['Extra'],
							'Value' => null
						);
					}
				}
			}

			if ($this->is_valid($this->uid, $this->table)) {
				$this->valid = true;
			}
		}
	}

	/**
	 * Fetch one row from the table using the fields requested
	 */
	public function load($fields = array(), $where = array()) {
		if (count($fields) < 1) {
			$fields = $this->arrFields;
		} else {
			foreach ($fields as $fieldName) {
				if (!array_key_exists($fieldName, $this->arrFields)) {
					return false;
				}
			}
		}

		$whereclose = '';

		if (count($where) > 0) {
			foreach ($where as $idx => $val) {
				$whereclose .=" AND `{$idx}` = '{$val}'";
			}
		} else {
			$whereclose .=" AND `uid`={$this->uid} ";
		}
		$query = "SELECT `" . implode("`, `", array_keys($fields)) . "` FROM `{$this->table}` WHERE 1=1 {$whereclose} LIMIT 1";
		$result = database::query($query);

		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$columns = array_keys($row);
			$count = count($columns);

			for ($i = $count - 1; $i >= 0; $i--) {
				//if($columns[$i] != 'uid') {
				$method = 'set_' . $columns[$i];
				$this->$method(stripslashes($row[$columns[$i]]));
				//}
			}
		}
		$this->TableData = $this->arrFields;
	}

	// default public methods of the class
	public function get_uid() {
		return $this->uid;
	}

	public function get_valid() {
		return $this->valid;
	}

	/**
	 * Requires the $table to be a single database table
	 * Requires the $set array to consist of arrays of field=>$data array pairs
	 * Example:
	 * array('name'=>array('city','string'))
	 */
	public function save($set=array(),$caching=0) {

		$response = false;

		$setdata = array();
		$query = "UPDATE `{$this->table}` SET ";

		if (count($set) < 1) {
			$set = $this->arrFields;
		}

		// this foreach loop needs to be updated to match
		// the format of the arrFields array
		foreach ($set as $key => $data) {
			$setdata[] = "`$key`=" . format::mysql_prepare(array('value' => $data['Value'], 'type' => $data['Type']['Data']));
		}
		$query.= implode(", ", $setdata) . " WHERE `uid`={$this->uid} LIMIT 1";
		database::query($query,$caching);

		if (!database::$error) {
			$response = $this->uid;
		} else {
			$response = database::$error;
		}

		return $response;
	}

	public function delete($caching=0) {

		$response = false;

		$query = "DELETE FROM `{$this->table}` WHERE `uid`={$this->uid} LIMIT 1";
		database::query($query,$caching);

		if (!database::$error) {
			$response = true;
		} else {
			$response = database::$error;
		}
	}

	public function where_delete($where,$caching=0) {
		$response = false;
		$whereclose = 'WHERE 1 = 1';

		if (count($where) > 0) {
			foreach ($where as $idx => $val) {
				$whereclose .=" AND `{$idx}` = '{$val}'";
			}

			if ($whereclose != 'WHERE 1 = 1') {

				$query = "DELETE FROM `{$this->table}` {$whereclose}  LIMIT 1";
				database::query($query,$caching);

				if (!database::$error) {
					$response = true;
				} else {
					$response = database::$error;
				}
			}
		}
	}

	public function insert($caching=0) {
		$query = "INSERT INTO `{$this->table}` (";
		$fields = array();
		$values = array();

		foreach ($this->arrFields as $key => $data) {
			$fields[] = "`{$key}`";
			$values[] = format::mysql_prepare(array('value' => $data['Value'], 'type' => $data['Type']['Data']));
		}

		if (count($fields) > 0 && count($values) > 0) {
			$query.= implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
		}
		return database::insert($query,$caching);
	}

	public function search($fields = array(), $where = array(), $OrderBy = '') {

		$queryFields = '';
		if (is_array($fields) && count($fields) > 0) {
			$queryFields = '`' . implode('`,`', $fields) . '`';
		} else {
			$queryFields = ' * ';
		}

		$queryWhere = " WHERE 1=1 ";
		if (count($where) > 0) {
			foreach ($where as $idx => $val)
				$queryWhere .= " and `" . $idx . "` = '" . $val . "' ";
		}

		$query = "SELECT " . $queryFields . " FROM `" . $this->table . "` " . $queryWhere . " " . $OrderBy;
		$result = database::query($query);
		$data = array();
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result)) {
				$data[] = $row;
			}
		}
		if (count($data) == 1)
			return $data[0];
		else
			return $data;
	}

	public function is_valid($uid = 0, $table = '') {
		/* function to check a valid record exists in the table for that UID, sanity checking mostly */
		$valid = false;

		if ($uid && is_numeric($uid) && (int) $uid > 0) {
			if (strlen($table) > 0) {
				$this->table = format::toTableName($table);
			}
			$query = "SELECT `uid` FROM `$table` WHERE `uid` = $uid LIMIT 1;";
			$result = database::query($query);

			if (!database::$error && $result && mysql_num_rows($result) > 0) {
				$valid = true;
			}
		}
		return $valid;
	}

	public function __call($method, $arguments) {
		$prefix = strtolower(substr($method, 0, 4));
		$method = substr($method, 4);

		if (!empty($prefix) && !empty($method)) {

			if (in_array($prefix, array('get_', 'set_'))) {
				if (array_key_exists($method, $this->arrFields)) {

					$fieldName = $method;

					if ('get_' == $prefix && isset($this->arrFields[$fieldName]['Value'])) {

						return $this->arrFields[$fieldName]['Value'];
					} else if ('set_' == $prefix) {

						$value = $arguments[0];

						$this->arrFields[$fieldName]['Value'] = $value;
					}
				} else {

					$helper = $method;

					if (count($this->arrHelpers) > 0) {
						if (isset($this->arrHelpers[$helper])) {
							$objHelper = new $this->arrHelpers[$helper];
							return $objHelper->run($arguments);
						}
					}
				}
			}
		}
	}

	public static function redirectTo($url) {
		if (!headers_sent($filename, $linenum)) {
			header('Location: ' . config::url($url));
			exit();
		} else {
			echo "Headers already sent in $filename on line $linenum\n";
		}
	}

	public static function redirectToDynamic($url) {
		if (@$_SESSION['user']['admin'] == 1) {
			$url = 'admin' . $url;
		} else {
			$url = 'account' . $url;
		}

		if (!headers_sent($filename, $linenum)) {
			header('Location: ' . config::url($url));
			exit();
		} else {
			echo "Headers already sent in $filename on line $linenum\n";
		}
	}

	public function getFields() {
		$response = array();

		if (count($this->arrFields) < 1) {
			$this->__construct();
		}

		foreach ($this->arrFields as $key => $val) {
			$response[$key] = $this->arrFields[$key]['Value'];
		}

		return $response;
	}

	protected function setPagination($query = "", $pageId = '', $record_limit = 10) {

		$result = database::query($query);
		$max = 0;
		if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_array($result);
			$max = $row[0];
		}
		if(isset($_GET['p']) && is_numeric($_GET['p'])) {
			$pageId = (int)$_GET['p'];
		}
		if ($pageId == '') {
			$parts = config::get('paths');
			$page = end($parts);
			if (strpos($page, "p-") !== false) {
				$page = str_replace("p-", "", $page);
				if (is_numeric($page) && $page > 0) {
					$pageId = $page;
				} else {
					$pageId = 1;
				}
			} else {
				if(trim($page)!='' && !is_numeric($page) && isset($_REQUEST['find'])) {
					$page = trim($_REQUEST['find']);
					if (strpos($page, "p-") !== false) {
						$page = strstr($page,"p-");
						$page = str_replace("p-", "", $page);
						if (is_numeric($page) && $page > 0) {
							$pageId = $page;
						} else {
							$pageId = 1;
						}
					} else {
						$pageId = 1;
					}
				} else {
					$pageId = 1;
				}
			}
		}

		if(isset($_GET) && count($_GET) && isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING'])>0) {
			$this->pager(
				$max, //see above
				$record_limit, //how many records to display at one time
				$pageId, array("php_self" => "&p=")
			);

		} else {
			$this->pager(
					$max, //see above
					$record_limit, //how many records to display at one time
					$pageId, array("php_self" => "p-")
			);
		}
		$this->set_range(10);
	}

	/**
	 * $arrFields is fields array which we need to validate before processing with database
	 * $objTable is database table object which we'll use to assign value by $objTable->set_field($val) method
	 */
	public function isValidarrFields($arrFields=array(), $objTable=false) {
		$arrMessages = array();
		foreach ($arrFields as $index => $arrInfo) {

			if ($arrInfo['checkEmpty'] && ($arrInfo['value'] == '' || $arrInfo['value'] == '0')) {
				$arrMessages[$arrInfo['errIndex']] = $arrInfo['errEmpty'];
			} else if ($arrInfo['dataType'] != false && !validation::isValid($arrInfo['dataType'], $arrInfo['value'])) {
				$arrMessages[$arrInfo['errIndex']] = $arrInfo['errdataType'];
			} else if ($arrInfo['minChar'] > 0 && $arrInfo['maxChar'] > 0 && (strlen(trim($arrInfo['value'])) < $arrInfo['minChar'] || strlen(trim($arrInfo['value'])) > $arrInfo['maxChar'])) {
				$arrMessages[$arrInfo['errIndex']] = $arrInfo['errMinMax'];
			} else if (isset($arrInfo['errdate']) && isset($arrInfo['errdateFrom']) && isset($arrInfo['errdateTo']) && is_numeric($arrInfo['errdateFrom']) && is_numeric($arrInfo['errdateTo']) && $arrInfo['errdateTo'] < $arrInfo['errdateFrom']) {
				$arrMessages[$arrInfo['errIndex']] = $arrInfo['errdate'];
			}
		}

		if (count($arrMessages) > 0) {
			$strMessage = '';
			foreach ($arrMessages as $index => $value) {
				$this->arrForm[$index] = 'label_error';
				$strMessage .= '<li>' . $value . '</li>';
			}
			$this->arrForm['message_error'] = '<p>Please correct the errors below:</p><ul>' . $strMessage . '</ul>';
		}

		/**
		 * FOLLOWING CODE WILL SAVE ALL $_POST VALUES TO $this->arrForm. THIS WILL USE TO REPOPULATE
		 * DATA ON FORM WHEN ERROR OCCURS
		 */
		foreach ($_POST as $index => $value) {
			if(!is_array($value)) {
				$this->arrForm[$index] = $value;
			}
		}

		if (count($arrMessages) == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * copy translation data
	 *
	 * $arrValues like
	 * array(
	 * 		array(
	 * 			'field' => 'field name'
	 * 			'value' => 'value name'
	 * 		)
	 * )
	 *
	 */
	protected function copyToTranslation($tableFrom, $tableTo, $where=null, $arrValues=null, $groupBy=null) {
		if ($tableFrom == $tableTo && !empty($tableFrom) && !empty($tableTo)) {

			$newArrValueString = "";
			$unsetArray = array();
			foreach ($arrValues as $value) {

				$newArrValueString .= "`".$value["field"] . "`='{$value["value"]}',";

				$unsetArray[] = mysql_real_escape_string($value["field"]);
			}
			$newArrValueString = trim($newArrValueString, ',');

			$query = "SELECT * FROM {$tableFrom}";
			$query.= " WHERE 1=1 ";
			$query.= " {$where}";
			$query.= " {$groupBy}";

			//echo ($tableFrom=="years_translations")?$query:"";
			$copyValues = database::arrQuery($query);

			foreach ($copyValues as $value) {

				$defaultValue = "";
				foreach ($value as $key => $val) {
					if (array_search($key, $unsetArray) === false && $key != 'uid') {
						$val=mysql_real_escape_string($val);
						$defaultValue.="`".$key . "`='$val',";
					}
				}
				$defaultValue = (!empty($newArrValueString)) ? $defaultValue : trim($defaultValue, ',');

				$query = "INSERT INTO {$tableTo} ";
				$query.= " SET ";
				$query.= $defaultValue . $newArrValueString;

				//echo ($tableTo=="years_translations")?$query:"";

				database::query($query,1);
			}
		}
	}

}

?>