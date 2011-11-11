<?php

/**
 * Session Class to wrap session calls
 *
 * Example: add to the session
 * <code>
 * session->set('username','service_1');
 * echo session->get('username');
 * session->set('username');
 * </code>
 *
 * @todo Write a complete wrapper for the session including proper state checks
 */

class session {

	/**
	* Static variable to indicate whether or not the session has been started
	*/
	private static $started = false;

	/**
	* Private method to start the session if not already started
	* This should not be called directly, but only when actually
	* adding data to, removing data from or updating data in the
	* session itself
	*/
	private static function start () {
		if(count($_SESSION) < 1) {
			session_start();
			self::$started = true;
		}
	}

	/**
	 * Return the data from the session, if it exists, using a key
	 * @param string $key This is the name of the key from the key/value pair used to store data
	 * @return mixed $val This is either null or the value from the key/value pair used when storing data
	 */
	public static function get ($key='') {
		if(!self::$started) {
			self::start();
		}
		$val = null;
		if(count($_SESSION) > 0 && isset($_SESSION[$key])) {
			$val = $_SESSION[$key];
		}
		return $val;
	}

	/**
	 * Store data in the session using a key/value pair OR unset a key if null is passed as the second
	 * parameter or if no second parameter is passed
	 * @param string $key This is the name of the key from the key/value pair used to store data
	 * @param mixed $val This is the value to store, or, if null, signals that the key should be unset
	 */
	public static function set ($key='', $val=null) {
		if(!self::$started) {
			self::start();
		}
		if(count($_SESSION) > 0) {
			if($val == null) {
				unset($_SESSION[$key]);
			} else {
				$_SESSION[$key]=$val;
			}
		}
	}
}

?>