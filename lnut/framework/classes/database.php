<?php

/**
 * database
 */
class database {

	private static $db = null;
	public static $error = false;
	public static $query = '';
	private static $result = null;

	public static function connect() {
		if (!self::$db) {
			self::$db = mysql_connect(config::db('server'), config::db('username'), config::db('password'));
			if (!@mysql_select_db(config::db('database'))) {
				debug::message(
						debug::$log_type['error'], debug_backtrace(), array(
					0 => 'Could not connect to database',
					1 => mysql_error()
						)
				);
			}
			if (!self::$db) {
				debug::message(
						debug::$log_type['error'], debug_backtrace(), array(
					0 => 'Could not connect to server',
					1 => mysql_error()
						)
				);
			} else {
				self::query("SET NAMES 'utf8'");
			}
		}
	}

	public function select($fields=array(), $tables=array(), $conditions=array(), $orderby=array(), $limit=array(), $caching=0) {

		if (count($fields) > 0 && count($tables) > 0) {
			$querystring = "SELECT ";
			$querystring.= implode(', ', $fields);
			$querystring.= " FROM ";
			$querystring.= implode(', ', $tables);
			if (count($conditions) > 0) {
				$querystring.= " WHERE ";
				$querystring.= implode(' AND ', $conditions);
			}
			if (count($orderby) > 0) {
				$querystring.= " ORDER BY ";
				$querystring.= implode(', ', $orderby);
			}
			if (
					$limit
					&& is_array($limit)
					&& isset($limit['start'])
					&& is_numeric($limit['start'])
					&& (int) $limit['start'] > -1
					&& isset($limit['records'])
					&& is_numeric($limit['records'])
					&& (int) $limit['records'] > 0
			) {
				$start = (int) $limit['start'];
				$records = $limit['records'];
				$querystring.= " LIMIT ";
				$querystring.= $start . ", " . $records;
			} else if (is_string($limit) && $limit == 'none') {
				// get everything
			} else {
				$start = 0;
				$records = 30;
				$querystring.= " LIMIT ";
				$querystring.= $start . ", " . $records;
			}


			$objCache = new cache();
			if ($caching > 0 && $objCache->cacheExist($querystring, "sql")) {
				return unserialize($objCache->getCacheContent($querystring, "sql"));
			} else {
				return self::query($querystring, $caching);
			}
		} else {
			debug::message(
					debug::$log_type['error'], debug_backtrace(), array(
				0 => 'Could Not Instantiate.',
				1 => 'Invalid Parameters Passed.',
				2 => 'Fields: ' . print_r($fields, true),
				3 => 'Tables: ' . print_r($tables, true),
				4 => 'Conditions: ' . print_r($conditions, true),
				5 => 'Order By: ' . print_r($orderby, true),
				6 => 'Limit: ' . print_r($limit, true)
					)
			);
		}
	}

	public static function query($query, $caching=0) {
		self::reset();
		self::connect();

		// remove cache is query is not of select
		// echo $query;
		//if ($caching == 1 && preg_match("/^select/", $query)) {
		//	$objCache = new cache();
		//	$objCache->emptyCache();
		//}
		self::$query = $query;
		self::$result = @mysql_query($query, self::$db);
		if (mysql_error()) {
			self::add_error(mysql_error());
			debug::message(
				debug::$log_type['message'], debug_backtrace(), array(
					0 => $query,
					1 => mysql_error()
				)
			);
		}

		return self::$result;
	}

	public static function arrQuery($query,$caching=0) {
		
		$objCache = new cache();
		if ($caching > 0 && $objCache->cacheExist($query, "sql")) {
			return unserialize($objCache->getCacheContent($query, "sql"));
		} else {
//			echo "no caching";
			$result = self::query($query, $caching);
			$data = array();
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				while ($row = mysql_fetch_assoc($result)) {
					$data[] = $row;
				}
			}
			if ($caching > 0) {
				$objCache->createOrReplace($query, serialize($data), "sql");
			}
			return $data;
		}
	}

	public static function arrQueryByUid($query, $keyMap=array(), $caching=0) {

		//$objCache = new cache();

		//$objCache->cacheExist("uid_" . $query, "sql");

		//if ($caching > 0 && $objCache->cacheExist("uid_" . $query, "sql") !== false) {

		//	return unserialize($objCache->getCacheContent("uid_" . $query, "sql"));
		//} else {
//			echo "no caching";
			$result = self::query($query, $caching);
			$data = array();
			if ($result && mysql_error() == '' && mysql_num_rows($result) > 0) {
				$counter = 0;
				while ($row = mysql_fetch_assoc($result)) {
					$keyUid = (isset($row["uid"])) ? $row["uid"] : $counter;
					if (!empty($keyMap)) {
						$newRow = array();
						foreach ($row as $key => $value) {
							$newkey = array_search($key, $keyMap);
							if ($newkey !== false) {
								$newRow[$newkey] = $value;
							} else {
								$newRow[$key] = $value;
							}
						}
						$data[$keyUid] = $newRow;
					} else {
						$data[$keyUid] = $row;
					}
					$counter++;
				}
			}
			//if ($caching > 0) {
			//	$objCache->createOrReplace("uid_" . $query, serialize($data), "sql");
			//}
			return $data;
		//}
	}

	public static function insert($query, $caching=0) {
		$result = self::query($query, $caching);
		if (self::$error == '') {
			return mysql_insert_id();
		} else {
			return null;
		}
	}

	protected function reset() {
		self::$result = null;
		self::$error = '';
	}

	public static function add_error($error = '') {
		self::$error.= $error . '<br />';
	}

}

?>