<?php

/**
 * Registry Class to be used with Caching, if Possible
 */

class registry {
	private static $stack = array ();

	public static function is_entry($key='') {
		return array_key_exists($key, self::$stack);
	}

	public static function set_entry($key='', $value='') {
		self::$stack[$key] = $value;
		return true;
	}

	public static function get_entry($key) {
		return (self::is_entry($key)) ? self::$stack[$key] : null;
	}

	public static function unset_entry($key='') {
		if(self::is_entry($key)) {
			unset(self::$stack[$key]);
			return true;
		}
		return false;
	}
}

?>